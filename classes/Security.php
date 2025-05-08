<?php
class Security {
    private static $instance = null;
    private $db;
    private $logTable = "guvenlik_loglari";
    private $backupTable = "yedekleme_loglari";
    private $maxLoginAttempts = 5;
    private $lockoutTime = 1800; // 30 dakika
    private $sessionTimeout = 3600; // 1 saat

    private function __construct() {
        $this->db = Database::getInstance();
        $this->initSession();
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function initSession() {
        if (session_status() === PHP_SESSION_NONE) {
            session_set_cookie_params([
                'lifetime' => $this->sessionTimeout,
                'path' => '/',
                'secure' => true,
                'httponly' => true,
                'samesite' => 'Strict'
            ]);
            
            session_start();
        }
    }

    public function login($username, $password) {
        // Giriş denemelerini kontrol et
        if ($this->isIPBlocked()) {
            throw new Exception('IP adresiniz geçici olarak engellendi. Lütfen daha sonra tekrar deneyin.');
        }

        // Kullanıcıyı kontrol et
        $sql = "SELECT * FROM kullanicilar WHERE kullanici_adi = ?";
        $stmt = $this->db->query($sql, [$username]);
        $user = $stmt->fetch();

        if (!$user || !password_verify($password, $user['sifre'])) {
            $this->logFailedLogin($username);
            throw new Exception('Geçersiz kullanıcı adı veya şifre.');
        }

        // Kullanıcı aktif mi kontrol et
        if (!$user['aktif']) {
            throw new Exception('Hesabınız aktif değil.');
        }

        // İki faktörlü doğrulama kontrolü
        if ($user['iki_faktorlu']) {
            $_SESSION['temp_user_id'] = $user['id'];
            return ['require_2fa' => true];
        }

        // Giriş başarılı
        $this->logSuccessfulLogin($user['id']);
        $this->createSession($user);

        return ['success' => true];
    }

    public function verifyTwoFactor($code) {
        if (!isset($_SESSION['temp_user_id'])) {
            throw new Exception('Geçersiz oturum.');
        }

        $sql = "SELECT * FROM kullanicilar WHERE id = ? AND iki_faktorlu_kod = ?";
        $stmt = $this->db->query($sql, [$_SESSION['temp_user_id'], $code]);
        $user = $stmt->fetch();

        if (!$user) {
            throw new Exception('Geçersiz doğrulama kodu.');
        }

        // Giriş başarılı
        $this->logSuccessfulLogin($user['id']);
        $this->createSession($user);

        return ['success' => true];
    }

    public function logout() {
        if (isset($_SESSION['user_id'])) {
            $this->logLogout($_SESSION['user_id']);
        }

        session_destroy();
        session_start();
        session_regenerate_id(true);
    }

    public function checkSession() {
        if (!isset($_SESSION['user_id']) || !isset($_SESSION['last_activity'])) {
            return false;
        }

        if (time() - $_SESSION['last_activity'] > $this->sessionTimeout) {
            $this->logout();
            return false;
        }

        $_SESSION['last_activity'] = time();
        return true;
    }

    public function createBackup() {
        $backupDir = 'backups/';
        if (!file_exists($backupDir)) {
            mkdir($backupDir, 0755, true);
        }

        $filename = $backupDir . 'backup_' . date('Y-m-d_H-i-s') . '.sql';
        
        // Veritabanı yedeği al
        $command = sprintf(
            'mysqldump -h %s -u %s -p%s %s > %s',
            DB_HOST,
            DB_USER,
            DB_PASS,
            DB_NAME,
            $filename
        );
        
        exec($command, $output, $returnVar);

        if ($returnVar !== 0) {
            throw new Exception('Veritabanı yedeği alınamadı.');
        }

        // Yedekleme kaydı oluştur
        $data = [
            'dosya_adi' => basename($filename),
            'dosya_boyutu' => filesize($filename),
            'durum' => 'basarili',
            'tarih' => date('Y-m-d H:i:s')
        ];

        $this->db->insert($this->backupTable, $data);

        return $filename;
    }

    public function restoreBackup($filename) {
        $backupFile = 'backups/' . $filename;
        
        if (!file_exists($backupFile)) {
            throw new Exception('Yedek dosyası bulunamadı.');
        }

        // Veritabanını geri yükle
        $command = sprintf(
            'mysql -h %s -u %s -p%s %s < %s',
            DB_HOST,
            DB_USER,
            DB_PASS,
            DB_NAME,
            $backupFile
        );
        
        exec($command, $output, $returnVar);

        if ($returnVar !== 0) {
            throw new Exception('Veritabanı geri yüklenemedi.');
        }

        // Geri yükleme kaydı oluştur
        $data = [
            'dosya_adi' => $filename,
            'islem_tipi' => 'geri_yukleme',
            'durum' => 'basarili',
            'tarih' => date('Y-m-d H:i:s')
        ];

        $this->db->insert($this->backupTable, $data);

        return true;
    }

    public function getBackupList() {
        $sql = "SELECT * FROM {$this->backupTable} ORDER BY tarih DESC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }

    public function deleteBackup($filename) {
        $backupFile = 'backups/' . $filename;
        
        if (file_exists($backupFile)) {
            unlink($backupFile);
        }

        $sql = "DELETE FROM {$this->backupTable} WHERE dosya_adi = ?";
        return $this->db->query($sql, [$filename]);
    }

    public function logSecurityAction($userId, $action, $details = null) {
        $data = [
            'kullanici_id' => $userId,
            'islem' => $action,
            'detaylar' => $details,
            'ip_adresi' => $_SERVER['REMOTE_ADDR'],
            'user_agent' => $_SERVER['HTTP_USER_AGENT'],
            'tarih' => date('Y-m-d H:i:s')
        ];

        return $this->db->insert($this->logTable, $data);
    }

    public function getSecurityLogs($filters = []) {
        $sql = "SELECT * FROM {$this->logTable} WHERE 1=1";
        $params = [];

        if (isset($filters['kullanici_id'])) {
            $sql .= " AND kullanici_id = ?";
            $params[] = $filters['kullanici_id'];
        }

        if (isset($filters['islem'])) {
            $sql .= " AND islem = ?";
            $params[] = $filters['islem'];
        }

        if (isset($filters['start_date'])) {
            $sql .= " AND tarih >= ?";
            $params[] = $filters['start_date'];
        }

        if (isset($filters['end_date'])) {
            $sql .= " AND tarih <= ?";
            $params[] = $filters['end_date'];
        }

        $sql .= " ORDER BY tarih DESC";

        if (isset($filters['limit'])) {
            $sql .= " LIMIT ?";
            $params[] = $filters['limit'];
        }

        $stmt = $this->db->query($sql, $params);
        return $stmt->fetchAll();
    }

    private function isIPBlocked() {
        $ip = $_SERVER['REMOTE_ADDR'];
        
        $sql = "SELECT COUNT(*) as count FROM {$this->logTable} 
                WHERE ip_adresi = ? 
                AND islem = 'failed_login' 
                AND tarih > DATE_SUB(NOW(), INTERVAL ? SECOND)";
        
        $stmt = $this->db->query($sql, [$ip, $this->lockoutTime]);
        $result = $stmt->fetch();
        
        return $result['count'] >= $this->maxLoginAttempts;
    }

    private function logFailedLogin($username) {
        $data = [
            'kullanici_id' => null,
            'islem' => 'failed_login',
            'detaylar' => "Failed login attempt for username: {$username}",
            'ip_adresi' => $_SERVER['REMOTE_ADDR'],
            'user_agent' => $_SERVER['HTTP_USER_AGENT'],
            'tarih' => date('Y-m-d H:i:s')
        ];

        $this->db->insert($this->logTable, $data);
    }

    private function logSuccessfulLogin($userId) {
        $data = [
            'kullanici_id' => $userId,
            'islem' => 'successful_login',
            'detaylar' => 'User logged in successfully',
            'ip_adresi' => $_SERVER['REMOTE_ADDR'],
            'user_agent' => $_SERVER['HTTP_USER_AGENT'],
            'tarih' => date('Y-m-d H:i:s')
        ];

        $this->db->insert($this->logTable, $data);
    }

    private function logLogout($userId) {
        $data = [
            'kullanici_id' => $userId,
            'islem' => 'logout',
            'detaylar' => 'User logged out',
            'ip_adresi' => $_SERVER['REMOTE_ADDR'],
            'user_agent' => $_SERVER['HTTP_USER_AGENT'],
            'tarih' => date('Y-m-d H:i:s')
        ];

        $this->db->insert($this->logTable, $data);
    }

    private function createSession($user) {
        session_regenerate_id(true);
        
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['kullanici_adi'];
        $_SESSION['role'] = $user['rol'];
        $_SESSION['last_activity'] = time();
        
        // Son giriş tarihini güncelle
        $sql = "UPDATE kullanicilar SET son_giris = NOW() WHERE id = ?";
        $this->db->query($sql, [$user['id']]);
    }

    public function generateCSRFToken() {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        
        return $_SESSION['csrf_token'];
    }

    public function validateCSRFToken($token) {
        if (!isset($_SESSION['csrf_token']) || $token !== $_SESSION['csrf_token']) {
            $this->logSecurityEvent('csrf_validation_failed', [
                'ip' => $_SERVER['REMOTE_ADDR'],
                'user_agent' => $_SERVER['HTTP_USER_AGENT']
            ]);
            throw new Exception('CSRF token doğrulaması başarısız.');
        }
        
        return true;
    }

    public function sanitizeInput($input) {
        if (is_array($input)) {
            foreach ($input as $key => $value) {
                $input[$key] = $this->sanitizeInput($value);
            }
        } else {
            $input = htmlspecialchars($input, ENT_QUOTES, 'UTF-8');
        }
        
        return $input;
    }

    public function validatePassword($password) {
        // En az 8 karakter
        if (strlen($password) < 8) {
            return false;
        }

        // En az bir büyük harf
        if (!preg_match('/[A-Z]/', $password)) {
            return false;
        }

        // En az bir küçük harf
        if (!preg_match('/[a-z]/', $password)) {
            return false;
        }

        // En az bir rakam
        if (!preg_match('/[0-9]/', $password)) {
            return false;
        }

        // En az bir özel karakter
        if (!preg_match('/[!@#$%^&*()\-_=+{};:,<.>]/', $password)) {
            return false;
        }

        return true;
    }

    public function generatePassword() {
        $length = 12;
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()\-_=+{};:,<.>';
        
        do {
            $password = '';
            for ($i = 0; $i < $length; $i++) {
                $password .= $chars[random_int(0, strlen($chars) - 1)];
            }
        } while (!$this->validatePassword($password));
        
        return $password;
    }

    public function sanitize($data) {
        if (is_array($data)) {
            foreach ($data as $key => $value) {
                $data[$key] = $this->sanitize($value);
            }
        } else {
            $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
        }
        return $data;
    }

    public function logSecurityEvent($event, $data = []) {
        $query = "INSERT INTO guvenlik_loglari (kullanici_id, ip_adresi, islem, detay) 
                 VALUES (:kullanici_id, :ip_adresi, :islem, :detay)";
        
        $this->db->query($query)
            ->bind(':kullanici_id', $_SESSION['user_id'] ?? null)
            ->bind(':ip_adresi', $_SERVER['REMOTE_ADDR'])
            ->bind(':islem', $event)
            ->bind(':detay', json_encode($data))
            ->execute();
    }

    public function hashPassword($password) {
        return password_hash($password, PASSWORD_DEFAULT, ['cost' => 12]);
    }

    public function verifyPassword($password, $hash) {
        return password_verify($password, $hash);
    }

    public function generateToken($length = 32) {
        return bin2hex(random_bytes($length));
    }

    public function checkIP($ip) {
        $query = "SELECT COUNT(*) as count FROM guvenlik_loglari 
                 WHERE ip_adresi = :ip 
                 AND islem = 'failed_login' 
                 AND created_at > DATE_SUB(NOW(), INTERVAL 1 HOUR)";
        
        $result = $this->db->query($query)
            ->bind(':ip', $ip)
            ->single();
        
        return $result['count'] < 5;
    }

    public function validateFileUpload($file, $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'], $maxSize = 5242880) {
        if (!isset($file['error']) || is_array($file['error'])) {
            throw new Exception('Geçersiz dosya parametresi.');
        }

        switch ($file['error']) {
            case UPLOAD_ERR_OK:
                break;
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                throw new Exception('Dosya boyutu çok büyük.');
            case UPLOAD_ERR_PARTIAL:
                throw new Exception('Dosya tam yüklenemedi.');
            case UPLOAD_ERR_NO_FILE:
                throw new Exception('Dosya yüklenmedi.');
            case UPLOAD_ERR_NO_TMP_DIR:
                throw new Exception('Geçici klasör bulunamadı.');
            case UPLOAD_ERR_CANT_WRITE:
                throw new Exception('Dosya yazılamadı.');
            case UPLOAD_ERR_EXTENSION:
                throw new Exception('Dosya yükleme uzantısı durduruldu.');
            default:
                throw new Exception('Bilinmeyen bir hata oluştu.');
        }

        if ($file['size'] > $maxSize) {
            throw new Exception('Dosya boyutu izin verilenden büyük.');
        }

        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->file($file['tmp_name']);

        if (!in_array($mimeType, $allowedTypes)) {
            throw new Exception('Geçersiz dosya türü.');
        }

        return true;
    }

    public function generateSafeFileName($fileName) {
        $info = pathinfo($fileName);
        $name = $info['filename'];
        $ext = $info['extension'] ?? '';
        
        $name = preg_replace('/[^a-zA-Z0-9]/', '_', $name);
        $name = strtolower($name);
        
        return $name . '_' . uniqid() . ($ext ? '.' . $ext : '');
    }
} 