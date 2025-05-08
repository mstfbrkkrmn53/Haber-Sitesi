<?php
require_once '../config/db.php';
require_once '../classes/Admin.php';

session_start();

// Admin sınıfını kullanarak çıkış yap
$admin = new Admin($db);
$admin->logout();

// Giriş sayfasına yönlendir
header('Location: giris.php');
exit; 