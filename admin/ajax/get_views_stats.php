<?php
require_once '../../config/db.php';
require_once '../../classes/Admin.php';

// Oturum kontrolü
session_start();
if (!isset($_SESSION['admin_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

// Periyot parametresini al
$period = $_GET['period'] ?? 'week';

// Geçerli periyotları kontrol et
if (!in_array($period, ['week', 'month', 'year'])) {
    $period = 'week';
}

// Admin sınıfını kullanarak istatistikleri al
$admin = new Admin($db);
$data = $admin->getViewsChartData($period);

// JSON olarak döndür
header('Content-Type: application/json');
echo json_encode($data); 