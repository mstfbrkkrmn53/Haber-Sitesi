<?php
// Yapılandırma dosyasını yükle
require_once 'config.php';

// Veritabanı bağlantısı
try {
    $db = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
} catch (PDOException $e) {
    die("Veritabanı bağlantı hatası: " . $e->getMessage());
}

// Sınıfları yükle
require_once 'classes/Database.php';
require_once 'classes/User.php';
require_once 'classes/News.php';
require_once 'classes/Category.php';
require_once 'classes/Comment.php';
require_once 'classes/Media.php';
require_once 'classes/Settings.php';
require_once 'classes/Statistics.php';
require_once 'classes/Notification.php';
require_once 'classes/Security.php';
require_once 'classes/SEO.php';
require_once 'classes/Cache.php';

// Sınıf örneklerini oluştur
$database = new Database($db);
$user = new User($db);
$news = new News($db);
$category = new Category($db);
$comment = new Comment($db);
$media = new Media($db);
$settings = new Settings($db);
$statistics = new Statistics($db);
$notification = new Notification($db);
$security = new Security($db);
$seo = new SEO($db);
$cache = new Cache($db);

// Oturum başlat
session_start();

// Güvenlik kontrolü
$security->checkSession();

// Ziyaretçi istatistiğini kaydet
$statistics->logVisit([
    'ip' => $_SERVER['REMOTE_ADDR'],
    'user_agent' => $_SERVER['HTTP_USER_AGENT'],
    'referer' => $_SERVER['HTTP_REFERER'] ?? null,
    'url' => $_SERVER['REQUEST_URI']
]);

// Site ayarlarını yükle
$siteSettings = $settings->getAll();

// Hata işleyici
function errorHandler($errno, $errstr, $errfile, $errline) {
    $error = date('Y-m-d H:i:s') . " - Hata: [$errno] $errstr - $errfile:$errline\n";
    error_log($error, 3, LOG_DIR . '/error.log');
    
    if (ini_get('display_errors')) {
        echo "<div style='color: red; padding: 10px; border: 1px solid red; margin: 10px;'>";
        echo "<strong>Hata:</strong> $errstr<br>";
        echo "<strong>Dosya:</strong> $errfile:$errline";
        echo "</div>";
    }
    
    return true;
}

// Özel hata işleyiciyi ayarla
set_error_handler('errorHandler');

// Ölümcül hataları yakala
function fatalErrorHandler() {
    $error = error_get_last();
    if ($error !== null && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        errorHandler($error['type'], $error['message'], $error['file'], $error['line']);
    }
}

register_shutdown_function('fatalErrorHandler');

// Yardımcı fonksiyonlar
function redirect($url) {
    header("Location: $url");
    exit;
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function requireLogin() {
    if (!isLoggedIn()) {
        redirect(APP_URL . '/login.php');
    }
}

function requireAdmin() {
    requireLogin();
    if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
        redirect(APP_URL . '/403.php');
    }
}

function sanitize($input) {
    return htmlspecialchars($input, ENT_QUOTES, 'UTF-8');
}

function formatDate($date) {
    return date('d.m.Y H:i', strtotime($date));
}

function getFileExtension($filename) {
    return strtolower(pathinfo($filename, PATHINFO_EXTENSION));
}

function generateSlug($text) {
    $text = strtolower($text);
    $text = preg_replace('/[^a-z0-9\s-]/', '', $text);
    $text = preg_replace('/[\s-]+/', ' ', $text);
    $text = preg_replace('/\s/', '-', $text);
    return $text;
}

function truncate($text, $length = 100) {
    if (strlen($text) <= $length) {
        return $text;
    }
    return substr($text, 0, $length) . '...';
}

function getCurrentPage() {
    return basename($_SERVER['PHP_SELF']);
}

function isActivePage($page) {
    return getCurrentPage() === $page;
}

function getBaseUrl() {
    return APP_URL;
}

function getUploadUrl() {
    return APP_URL . '/uploads';
}

function getCacheUrl() {
    return APP_URL . '/cache';
}

function getLogUrl() {
    return APP_URL . '/logs';
}

// CSRF token oluştur
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

function getCsrfToken() {
    return $_SESSION['csrf_token'];
}

function validateCsrfToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// XSS koruması için çıktı tamponlama
ob_start(); 