<?php
/**
 * Genel yardımcı fonksiyonlar
 */

// Güvenlik fonksiyonları
function sanitize($data) {
    if (is_array($data)) {
        return array_map('sanitize', $data);
    }
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

function generateToken($length = 32) {
    return bin2hex(random_bytes($length));
}

function validateToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

function checkAuth() {
    if (!isset($_SESSION['user_id'])) {
        header('Location: ' . APP_URL . '/giris.php');
        exit;
    }
}

function checkAdmin() {
    if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
        header('Location: ' . APP_URL . '/404.php');
        exit;
    }
}

// URL ve yönlendirme fonksiyonları
function redirect($url) {
    header("Location: $url");
    exit;
}

function getCurrentUrl() {
    return (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
}

function getBaseUrl() {
    return APP_URL;
}

// Dosya işlemleri
function uploadFile($file, $targetDir, $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'], $maxSize = 5242880) {
    if (!isset($file['error']) || is_array($file['error'])) {
        throw new Exception('Geçersiz dosya parametresi.');
    }

    if ($file['error'] !== UPLOAD_ERR_OK) {
        throw new Exception('Dosya yükleme hatası: ' . $file['error']);
    }

    if ($file['size'] > $maxSize) {
        throw new Exception('Dosya boyutu çok büyük.');
    }

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mimeType = $finfo->file($file['tmp_name']);

    if (!in_array($mimeType, $allowedTypes)) {
        throw new Exception('Geçersiz dosya türü.');
    }

    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $fileName = uniqid() . '.' . $extension;
    $targetPath = $targetDir . '/' . $fileName;

    if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
        throw new Exception('Dosya yüklenemedi.');
    }

    return $fileName;
}

function deleteFile($filePath) {
    if (file_exists($filePath)) {
        return unlink($filePath);
    }
    return false;
}

// Tarih ve zaman fonksiyonları
function formatDate($date, $format = 'd.m.Y H:i') {
    return date($format, strtotime($date));
}

function timeAgo($datetime) {
    $time = strtotime($datetime);
    $now = time();
    $diff = $now - $time;

    if ($diff < 60) {
        return 'Az önce';
    } elseif ($diff < 3600) {
        return floor($diff / 60) . ' dakika önce';
    } elseif ($diff < 86400) {
        return floor($diff / 3600) . ' saat önce';
    } elseif ($diff < 604800) {
        return floor($diff / 86400) . ' gün önce';
    } else {
        return date('d.m.Y', $time);
    }
}

// Metin işleme fonksiyonları
function slugify($text) {
    $text = strtolower($text);
    $text = str_replace(['ı', 'ğ', 'ü', 'ş', 'ö', 'ç'], ['i', 'g', 'u', 's', 'o', 'c'], $text);
    $text = preg_replace('/[^a-z0-9\s-]/', '', $text);
    $text = preg_replace('/[\s-]+/', '-', $text);
    return trim($text, '-');
}

function truncate($text, $length = 100, $append = '...') {
    if (strlen($text) > $length) {
        $text = substr($text, 0, $length);
        $text = substr($text, 0, strrpos($text, ' '));
        $text .= $append;
    }
    return $text;
}

function highlight($text, $words) {
    if (!is_array($words)) {
        $words = [$words];
    }
    foreach ($words as $word) {
        $text = preg_replace('/(' . preg_quote($word, '/') . ')/i', '<mark>$1</mark>', $text);
    }
    return $text;
}

// Veritabanı yardımcı fonksiyonları
function getDb() {
    return Database::getInstance();
}

function query($sql, $params = []) {
    $db = getDb();
    $stmt = $db->query($sql);
    foreach ($params as $key => $value) {
        $stmt->bind($key, $value);
    }
    return $stmt->execute();
}

function fetch($sql, $params = []) {
    $db = getDb();
    $stmt = $db->query($sql);
    foreach ($params as $key => $value) {
        $stmt->bind($key, $value);
    }
    return $stmt->single();
}

function fetchAll($sql, $params = []) {
    $db = getDb();
    $stmt = $db->query($sql);
    foreach ($params as $key => $value) {
        $stmt->bind($key, $value);
    }
    return $stmt->resultSet();
}

// Cache fonksiyonları
function setCache($key, $value, $ttl = 3600) {
    if (!CACHE_ENABLED) return false;
    
    $cacheFile = CACHE_DIR . '/' . CACHE_PREFIX . md5($key);
    $data = [
        'value' => $value,
        'expires' => time() + $ttl
    ];
    
    return file_put_contents($cacheFile, serialize($data));
}

function getCache($key) {
    if (!CACHE_ENABLED) return false;
    
    $cacheFile = CACHE_DIR . '/' . CACHE_PREFIX . md5($key);
    if (!file_exists($cacheFile)) return false;
    
    $data = unserialize(file_get_contents($cacheFile));
    if ($data['expires'] < time()) {
        unlink($cacheFile);
        return false;
    }
    
    return $data['value'];
}

function deleteCache($key) {
    if (!CACHE_ENABLED) return false;
    
    $cacheFile = CACHE_DIR . '/' . CACHE_PREFIX . md5($key);
    if (file_exists($cacheFile)) {
        return unlink($cacheFile);
    }
    return false;
}

function clearCache() {
    if (!CACHE_ENABLED) return false;
    
    $files = glob(CACHE_DIR . '/' . CACHE_PREFIX . '*');
    foreach ($files as $file) {
        unlink($file);
    }
    return true;
}

// Mail fonksiyonları
function sendMail($to, $subject, $body, $attachments = []) {
    $mail = new PHPMailer\PHPMailer\PHPMailer(true);
    
    try {
        $mail->isSMTP();
        $mail->Host = MAIL_HOST;
        $mail->SMTPAuth = true;
        $mail->Username = MAIL_USERNAME;
        $mail->Password = MAIL_PASSWORD;
        $mail->SMTPSecure = MAIL_ENCRYPTION;
        $mail->Port = MAIL_PORT;
        $mail->CharSet = 'UTF-8';
        
        $mail->setFrom(MAIL_FROM_ADDRESS, MAIL_FROM_NAME);
        $mail->addAddress($to);
        
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $body;
        
        foreach ($attachments as $attachment) {
            $mail->addAttachment($attachment);
        }
        
        return $mail->send();
    } catch (Exception $e) {
        error_log('Mail gönderme hatası: ' . $e->getMessage());
        return false;
    }
}

// Bildirim fonksiyonları
function addNotification($userId, $title, $message, $type = 'info') {
    $db = getDb();
    $query = "INSERT INTO bildirimler (kullanici_id, baslik, mesaj, tip) VALUES (:user_id, :title, :message, :type)";
    
    return $db->query($query)
        ->bind(':user_id', $userId)
        ->bind(':title', $title)
        ->bind(':message', $message)
        ->bind(':type', $type)
        ->execute();
}

function getNotifications($userId, $limit = 10) {
    $db = getDb();
    $query = "SELECT * FROM bildirimler WHERE kullanici_id = :user_id ORDER BY created_at DESC LIMIT :limit";
    
    return $db->query($query)
        ->bind(':user_id', $userId)
        ->bind(':limit', $limit)
        ->resultSet();
}

function markNotificationAsRead($notificationId) {
    $db = getDb();
    $query = "UPDATE bildirimler SET okundu = 1 WHERE id = :id";
    
    return $db->query($query)
        ->bind(':id', $notificationId)
        ->execute();
}

// İstatistik fonksiyonları
function logVisit($page) {
    $db = getDb();
    $query = "INSERT INTO ziyaretci_loglari (ip_adresi, user_agent, sayfa, referrer) 
              VALUES (:ip, :user_agent, :page, :referrer)";
    
    return $db->query($query)
        ->bind(':ip', $_SERVER['REMOTE_ADDR'])
        ->bind(':user_agent', $_SERVER['HTTP_USER_AGENT'])
        ->bind(':page', $page)
        ->bind(':referrer', $_SERVER['HTTP_REFERER'] ?? '')
        ->execute();
}

function getStats($period = 'day') {
    $db = getDb();
    $date = date('Y-m-d');
    
    switch ($period) {
        case 'week':
            $date = date('Y-m-d', strtotime('-7 days'));
            break;
        case 'month':
            $date = date('Y-m-d', strtotime('-30 days'));
            break;
    }
    
    $query = "SELECT COUNT(*) as total FROM ziyaretci_loglari WHERE DATE(ziyaret_tarihi) >= :date";
    return $db->query($query)
        ->bind(':date', $date)
        ->single()['total'];
}

// API fonksiyonları
function validateApiKey($key) {
    return $key === API_KEY;
}

function rateLimit($ip) {
    $db = getDb();
    $query = "SELECT * FROM rate_limits WHERE ip_address = :ip";
    $result = $db->query($query)
        ->bind(':ip', $ip)
        ->single();
    
    if (!$result) {
        $db->query("INSERT INTO rate_limits (ip_address, requests, last_request) VALUES (:ip, 1, :time)")
            ->bind(':ip', $ip)
            ->bind(':time', time())
            ->execute();
        return true;
    }
    
    if (time() - $result['last_request'] > 3600) {
        $db->query("UPDATE rate_limits SET requests = 1, last_request = :time WHERE ip_address = :ip")
            ->bind(':time', time())
            ->bind(':ip', $ip)
            ->execute();
        return true;
    }
    
    if ($result['requests'] >= API_RATE_LIMIT) {
        return false;
    }
    
    $db->query("UPDATE rate_limits SET requests = requests + 1, last_request = :time WHERE ip_address = :ip")
        ->bind(':time', time())
        ->bind(':ip', $ip)
        ->execute();
    
    return true;
}

// Debug fonksiyonları
function dd($data) {
    echo '<pre>';
    var_dump($data);
    echo '</pre>';
    die();
}

function logError($message, $context = []) {
    $logFile = LOG_DIR . '/error.log';
    $timestamp = date('Y-m-d H:i:s');
    $contextStr = !empty($context) ? json_encode($context) : '';
    $logMessage = "[$timestamp] $message $contextStr\n";
    
    if (!is_dir(dirname($logFile))) {
        mkdir(dirname($logFile), 0777, true);
    }
    
    file_put_contents($logFile, $logMessage, FILE_APPEND);
}

// Yardımcı fonksiyonlar
function isActive($path) {
    return strpos($_SERVER['REQUEST_URI'], $path) !== false ? 'active' : '';
}

function getSetting($key) {
    global $site_settings;
    return $site_settings[$key] ?? null;
}

function formatNumber($number) {
    return number_format($number, 0, ',', '.');
}

function generatePassword($length = 12) {
    $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()';
    $password = '';
    
    for ($i = 0; $i < $length; $i++) {
        $password .= $chars[random_int(0, strlen($chars) - 1)];
    }
    
    return $password;
}

function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

function validatePhone($phone) {
    return preg_match('/^[0-9]{10,11}$/', $phone);
}

function getFileExtension($filename) {
    return strtolower(pathinfo($filename, PATHINFO_EXTENSION));
}

function isImage($filename) {
    $ext = getFileExtension($filename);
    return in_array($ext, ['jpg', 'jpeg', 'png', 'gif']);
}

function resizeImage($source, $destination, $width, $height) {
    list($srcWidth, $srcHeight) = getimagesize($source);
    $ratio = $srcWidth / $srcHeight;
    
    if ($width / $height > $ratio) {
        $width = $height * $ratio;
    } else {
        $height = $width / $ratio;
    }
    
    $image = imagecreatetruecolor($width, $height);
    $sourceImage = imagecreatefromjpeg($source);
    
    imagecopyresampled($image, $sourceImage, 0, 0, 0, 0, $width, $height, $srcWidth, $srcHeight);
    imagejpeg($image, $destination, 90);
    
    imagedestroy($image);
    imagedestroy($sourceImage);
}

function getClientIP() {
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        return $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        return $_SERVER['HTTP_X_FORWARDED_FOR'];
    }
    return $_SERVER['REMOTE_ADDR'];
}

function getBrowser() {
    $userAgent = $_SERVER['HTTP_USER_AGENT'];
    $browser = "Bilinmeyen Tarayıcı";
    
    if (preg_match('/MSIE/i', $userAgent)) {
        $browser = "Internet Explorer";
    } elseif (preg_match('/Firefox/i', $userAgent)) {
        $browser = "Firefox";
    } elseif (preg_match('/Chrome/i', $userAgent)) {
        $browser = "Chrome";
    } elseif (preg_match('/Safari/i', $userAgent)) {
        $browser = "Safari";
    } elseif (preg_match('/Opera/i', $userAgent)) {
        $browser = "Opera";
    }
    
    return $browser;
}

function getOS() {
    $userAgent = $_SERVER['HTTP_USER_AGENT'];
    $os = "Bilinmeyen İşletim Sistemi";
    
    if (preg_match('/windows|win32/i', $userAgent)) {
        $os = "Windows";
    } elseif (preg_match('/macintosh|mac os x/i', $userAgent)) {
        $os = "Mac";
    } elseif (preg_match('/linux/i', $userAgent)) {
        $os = "Linux";
    } elseif (preg_match('/ubuntu/i', $userAgent)) {
        $os = "Ubuntu";
    } elseif (preg_match('/android/i', $userAgent)) {
        $os = "Android";
    } elseif (preg_match('/webos/i', $userAgent)) {
        $os = "Mobile";
    }
    
    return $os;
} 