<?php
class Admin {
    private $db;
    
    public function __construct($db) {
        $this->db = $db;
    }
    
    // İstatistikleri getir
    public function getStats() {
        $stats = [];
        
        // Toplam haber sayısı
        $query = "SELECT COUNT(*) as total FROM news";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $stats['total_news'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
        
        // Geçen aya göre haber artışı
        $query = "SELECT 
            (COUNT(CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL 1 MONTH) THEN 1 END) * 100.0 / 
            COUNT(CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL 2 MONTH) AND created_at < DATE_SUB(NOW(), INTERVAL 1 MONTH) THEN 1 END)) as increase
            FROM news";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $stats['news_increase'] = round($stmt->fetch(PDO::FETCH_ASSOC)['increase'] ?? 0, 1);
        
        // Toplam kullanıcı sayısı
        $query = "SELECT COUNT(*) as total FROM users";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $stats['total_users'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
        
        // Geçen aya göre kullanıcı artışı
        $query = "SELECT 
            (COUNT(CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL 1 MONTH) THEN 1 END) * 100.0 / 
            COUNT(CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL 2 MONTH) AND created_at < DATE_SUB(NOW(), INTERVAL 1 MONTH) THEN 1 END)) as increase
            FROM users";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $stats['users_increase'] = round($stmt->fetch(PDO::FETCH_ASSOC)['increase'] ?? 0, 1);
        
        // Toplam yorum sayısı
        $query = "SELECT COUNT(*) as total FROM comments";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $stats['total_comments'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
        
        // Geçen aya göre yorum artışı
        $query = "SELECT 
            (COUNT(CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL 1 MONTH) THEN 1 END) * 100.0 / 
            COUNT(CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL 2 MONTH) AND created_at < DATE_SUB(NOW(), INTERVAL 1 MONTH) THEN 1 END)) as increase
            FROM comments";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $stats['comments_increase'] = round($stmt->fetch(PDO::FETCH_ASSOC)['increase'] ?? 0, 1);
        
        // Toplam görüntülenme
        $query = "SELECT SUM(views) as total FROM news";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $stats['total_views'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
        
        // Geçen aya göre görüntülenme artışı
        $query = "SELECT 
            (SUM(CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL 1 MONTH) THEN views END) * 100.0 / 
            SUM(CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL 2 MONTH) AND created_at < DATE_SUB(NOW(), INTERVAL 1 MONTH) THEN views END)) as increase
            FROM news";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $stats['views_increase'] = round($stmt->fetch(PDO::FETCH_ASSOC)['increase'] ?? 0, 1);
        
        // Son haberler
        $query = "SELECT n.*, c.name as category_name 
            FROM news n 
            LEFT JOIN categories c ON n.category_id = c.id 
            ORDER BY n.created_at DESC 
            LIMIT 5";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $stats['recent_news'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Son yorumlar
        $query = "SELECT c.*, u.username 
            FROM comments c 
            LEFT JOIN users u ON c.user_id = u.id 
            ORDER BY c.created_at DESC 
            LIMIT 5";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $stats['recent_comments'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Görüntülenme grafiği verileri
        $stats['views_chart'] = $this->getViewsChartData();
        
        // Kategori dağılımı grafiği verileri
        $stats['category_chart'] = $this->getCategoryChartData();
        
        return $stats;
    }
    
    // Görüntülenme grafiği verilerini getir
    private function getViewsChartData($period = 'week') {
        $data = [
            'labels' => [],
            'data' => []
        ];
        
        switch ($period) {
            case 'week':
                $query = "SELECT DATE(created_at) as date, SUM(views) as total 
                    FROM news 
                    WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
                    GROUP BY DATE(created_at)
                    ORDER BY date";
                break;
                
            case 'month':
                $query = "SELECT DATE(created_at) as date, SUM(views) as total 
                    FROM news 
                    WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                    GROUP BY DATE(created_at)
                    ORDER BY date";
                break;
                
            case 'year':
                $query = "SELECT MONTH(created_at) as month, SUM(views) as total 
                    FROM news 
                    WHERE created_at >= DATE_SUB(NOW(), INTERVAL 1 YEAR)
                    GROUP BY MONTH(created_at)
                    ORDER BY month";
                break;
        }
        
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($results as $row) {
            if ($period === 'year') {
                $data['labels'][] = date('F', mktime(0, 0, 0, $row['month'], 1));
            } else {
                $data['labels'][] = date('d.m.Y', strtotime($row['date']));
            }
            $data['data'][] = $row['total'];
        }
        
        return $data;
    }
    
    // Kategori dağılımı grafiği verilerini getir
    private function getCategoryChartData() {
        $data = [
            'labels' => [],
            'data' => []
        ];
        
        $query = "SELECT c.name, COUNT(n.id) as total 
            FROM categories c 
            LEFT JOIN news n ON c.id = n.category_id 
            GROUP BY c.id 
            ORDER BY total DESC 
            LIMIT 5";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($results as $row) {
            $data['labels'][] = $row['name'];
            $data['data'][] = $row['total'];
        }
        
        return $data;
    }
    
    // Giriş kontrolü
    public function login($username, $password) {
        $query = "SELECT * FROM users WHERE username = :username AND role = 'admin' LIMIT 1";
        $stmt = $this->db->prepare($query);
        $stmt->execute(['username' => $username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['admin_id'] = $user['id'];
            $_SESSION['admin_username'] = $user['username'];
            $_SESSION['admin_name'] = $user['name'];
            $_SESSION['admin_avatar'] = $user['avatar'];
            return true;
        }
        
        return false;
    }
    
    // Çıkış
    public function logout() {
        session_destroy();
        return true;
    }
    
    // Haber ekle
    public function addNews($data) {
        $query = "INSERT INTO news (title, content, category_id, image, status, created_at) 
            VALUES (:title, :content, :category_id, :image, :status, NOW())";
        
        $stmt = $this->db->prepare($query);
        return $stmt->execute([
            'title' => $data['title'],
            'content' => $data['content'],
            'category_id' => $data['category_id'],
            'image' => $data['image'],
            'status' => $data['status']
        ]);
    }
    
    // Haber güncelle
    public function updateNews($id, $data) {
        $query = "UPDATE news SET 
            title = :title,
            content = :content,
            category_id = :category_id,
            image = :image,
            status = :status,
            updated_at = NOW()
            WHERE id = :id";
        
        $stmt = $this->db->prepare($query);
        return $stmt->execute([
            'id' => $id,
            'title' => $data['title'],
            'content' => $data['content'],
            'category_id' => $data['category_id'],
            'image' => $data['image'],
            'status' => $data['status']
        ]);
    }
    
    // Haber sil
    public function deleteNews($id) {
        $query = "DELETE FROM news WHERE id = :id";
        $stmt = $this->db->prepare($query);
        return $stmt->execute(['id' => $id]);
    }
    
    // Kategori ekle
    public function addCategory($name) {
        $query = "INSERT INTO categories (name) VALUES (:name)";
        $stmt = $this->db->prepare($query);
        return $stmt->execute(['name' => $name]);
    }
    
    // Kategori güncelle
    public function updateCategory($id, $name) {
        $query = "UPDATE categories SET name = :name WHERE id = :id";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([
            'id' => $id,
            'name' => $name
        ]);
    }
    
    // Kategori sil
    public function deleteCategory($id) {
        $query = "DELETE FROM categories WHERE id = :id";
        $stmt = $this->db->prepare($query);
        return $stmt->execute(['id' => $id]);
    }
    
    // Yorum onayla/reddet
    public function updateCommentStatus($id, $status) {
        $query = "UPDATE comments SET status = :status WHERE id = :id";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([
            'id' => $id,
            'status' => $status
        ]);
    }
    
    // Yorum sil
    public function deleteComment($id) {
        $query = "DELETE FROM comments WHERE id = :id";
        $stmt = $this->db->prepare($query);
        return $stmt->execute(['id' => $id]);
    }
    
    // Kullanıcı ekle
    public function addUser($data) {
        $query = "INSERT INTO users (username, password, name, email, role, created_at) 
            VALUES (:username, :password, :name, :email, :role, NOW())";
        
        $stmt = $this->db->prepare($query);
        return $stmt->execute([
            'username' => $data['username'],
            'password' => password_hash($data['password'], PASSWORD_DEFAULT),
            'name' => $data['name'],
            'email' => $data['email'],
            'role' => $data['role']
        ]);
    }
    
    // Kullanıcı güncelle
    public function updateUser($id, $data) {
        $query = "UPDATE users SET 
            username = :username,
            name = :name,
            email = :email,
            role = :role
            WHERE id = :id";
        
        $params = [
            'id' => $id,
            'username' => $data['username'],
            'name' => $data['name'],
            'email' => $data['email'],
            'role' => $data['role']
        ];
        
        if (!empty($data['password'])) {
            $query = str_replace('role = :role', 'role = :role, password = :password', $query);
            $params['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        }
        
        $stmt = $this->db->prepare($query);
        return $stmt->execute($params);
    }
    
    // Kullanıcı sil
    public function deleteUser($id) {
        $query = "DELETE FROM users WHERE id = :id";
        $stmt = $this->db->prepare($query);
        return $stmt->execute(['id' => $id]);
    }
    
    // Ayarları güncelle
    public function updateSettings($data) {
        $query = "UPDATE settings SET 
            site_title = :site_title,
            site_description = :site_description,
            site_keywords = :site_keywords,
            site_logo = :site_logo,
            site_favicon = :site_favicon,
            contact_email = :contact_email,
            contact_phone = :contact_phone,
            contact_address = :contact_address,
            facebook_url = :facebook_url,
            twitter_url = :twitter_url,
            instagram_url = :instagram_url,
            youtube_url = :youtube_url
            WHERE id = 1";
        
        $stmt = $this->db->prepare($query);
        return $stmt->execute([
            'site_title' => $data['site_title'],
            'site_description' => $data['site_description'],
            'site_keywords' => $data['site_keywords'],
            'site_logo' => $data['site_logo'],
            'site_favicon' => $data['site_favicon'],
            'contact_email' => $data['contact_email'],
            'contact_phone' => $data['contact_phone'],
            'contact_address' => $data['contact_address'],
            'facebook_url' => $data['facebook_url'],
            'twitter_url' => $data['twitter_url'],
            'instagram_url' => $data['instagram_url'],
            'youtube_url' => $data['youtube_url']
        ]);
    }

    // CSRF token oluştur
    public function generateCSRFToken() {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    // CSRF token doğrula
    public function validateCSRFToken($token) {
        if (!isset($_SESSION['csrf_token']) || $token !== $_SESSION['csrf_token']) {
            throw new Exception('CSRF token doğrulaması başarısız.');
        }
        return true;
    }

    // Dosya yükleme güvenliği
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

    // Hata logla
    public function logError($message, $context = []) {
        $logFile = __DIR__ . '/../logs/error.log';
        $timestamp = date('Y-m-d H:i:s');
        $contextStr = !empty($context) ? json_encode($context) : '';
        $logMessage = "[$timestamp] $message $contextStr\n";
        
        if (!is_dir(dirname($logFile))) {
            mkdir(dirname($logFile), 0777, true);
        }
        
        file_put_contents($logFile, $logMessage, FILE_APPEND);
    }

    // API rate limiting
    public function checkRateLimit($ip, $limit = 60, $period = 60) {
        $key = "rate_limit:$ip";
        $current = time();
        
        $query = "INSERT INTO rate_limits (ip_address, requests, last_request) 
                 VALUES (:ip, 1, :current) 
                 ON DUPLICATE KEY UPDATE 
                 requests = IF(last_request < :period, 1, requests + 1),
                 last_request = :current";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute([
            'ip' => $ip,
            'current' => $current,
            'period' => $current - $period
        ]);
        
        $query = "SELECT requests FROM rate_limits 
                 WHERE ip_address = :ip 
                 AND last_request > :period";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute([
            'ip' => $ip,
            'period' => $current - $period
        ]);
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $result && $result['requests'] <= $limit;
    }
} 