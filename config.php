<?php
// Hata raporlama
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/logs/php_errors.log');

// Oturum başlat
session_start();

// Veritabanı yapılandırması
define('DB_HOST', 'localhost');
define('DB_NAME', 'haber_db');
define('DB_USER', 'root');
define('DB_PASS', '');

// Uygulama yapılandırması
define('APP_NAME', 'Haber Sitesi');
define('APP_URL', 'http://localhost/haber');
define('APP_ROOT', dirname(__FILE__));
define('APP_VERSION', '1.0.0');
define('APP_ENV', 'development');
define('APP_DEBUG', true);

// Dizin yapılandırması
define('UPLOAD_DIR', APP_ROOT . '/uploads');
define('CACHE_DIR', APP_ROOT . '/cache');
define('LOG_DIR', APP_ROOT . '/logs');

// Veritabanı bağlantısı
require_once 'classes/Database.php';
$db = Database::getInstance();

// Güvenlik sınıfı
require_once 'classes/Security.php';
$security = Security::getInstance();

// Hata yönetimi
require_once 'classes/ErrorHandler.php';
$errorHandler = ErrorHandler::getInstance();

// Admin sınıfı
require_once 'classes/Admin.php';
$admin = new Admin();

// Kullanıcı sınıfı
require_once 'classes/User.php';
$user = new User();

// SEO sınıfı
require_once 'classes/SEO.php';
$seo = new SEO();

// Bildirim sınıfı
require_once 'classes/Notification.php';
$notification = new Notification();

// Site ayarları
$site_settings = $db->query("SELECT * FROM site_ayarlari")->fetch(PDO::FETCH_ASSOC);

// Zaman dilimi
date_default_timezone_set('Europe/Istanbul');

// Karakter seti
header('Content-Type: text/html; charset=utf-8');

// Güvenlik başlıkları
header('X-Frame-Options: SAMEORIGIN');
header('X-XSS-Protection: 1; mode=block');
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: strict-origin-when-cross-origin');
header('Content-Security-Policy: default-src \'self\'; script-src \'self\' \'unsafe-inline\' \'unsafe-eval\' https://cdn.jsdelivr.net; style-src \'self\' \'unsafe-inline\' https://cdn.jsdelivr.net; img-src \'self\' data: https:; font-src \'self\' https://cdn.jsdelivr.net;');

// Oturum yapılandırması
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 0); // HTTPS yoksa 0 olmalı
ini_set('session.cookie_samesite', 'Strict');
ini_set('session.gc_maxlifetime', 3600);

// Güvenlik yapılandırması
define('HASH_COST', 12);
define('TOKEN_EXPIRY', 3600);
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOGIN_TIMEOUT', 1800);

// Cache yapılandırması
define('CACHE_ENABLED', true);
define('CACHE_PREFIX', 'cache_');
define('CACHE_EXPIRY', 3600);

// Mail yapılandırması
define('MAIL_HOST', 'smtp.gmail.com');
define('MAIL_PORT', 587);
define('MAIL_USERNAME', 'your-email@gmail.com');
define('MAIL_PASSWORD', 'your-password');
define('MAIL_ENCRYPTION', 'tls');
define('MAIL_FROM_ADDRESS', 'noreply@habersitesi.com');
define('MAIL_FROM_NAME', 'Haber Sitesi');

// API yapılandırması
define('API_KEY', 'your-api-key');
define('API_SECRET', 'your-api-secret');
define('API_RATE_LIMIT', 100);

// Dosya yükleme limitleri
define('MAX_FILE_SIZE', 10485760); // 10MB
define('ALLOWED_FILE_TYPES', [
    'image/jpeg',
    'image/png',
    'image/gif',
    'video/mp4',
    'application/pdf'
]);

// Dizinleri oluştur
$directories = [UPLOAD_DIR, CACHE_DIR, LOG_DIR];
foreach ($directories as $dir) {
    if (!file_exists($dir)) {
        mkdir($dir, 0755, true);
    }
}

// Güvenlik kontrolü
if (APP_ENV === 'production') {
    // Üretim ortamında hata gösterimini kapat
    error_reporting(0);
    ini_set('display_errors', 0);
    
    // Güvenlik başlıklarını güçlendir
    header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
    
    // Session güvenliğini artır
    ini_set('session.cookie_secure', 1);
}

// Hata yönetimini başlat
$errorHandler->init();
?> 