<?php
require_once 'init.php';

// Oturumu sonlandır
$security->logout();

// Ana sayfaya yönlendir
redirect(APP_URL); 