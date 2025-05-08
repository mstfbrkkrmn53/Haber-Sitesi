<?php
require_once 'init.php';

// Kullanıcı giriş yapmamışsa giriş sayfasına yönlendir
if (!isLoggedIn()) {
    redirect(APP_URL . '/login.php');
}

// 2FA gerekiyor mu kontrol et
if (!isset($_SESSION['requires_2fa']) || !$_SESSION['requires_2fa']) {
    redirect(APP_URL);
}

$error = '';
$success = '';

// Form gönderildi mi kontrol et
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $code = $_POST['code'] ?? '';

    if (empty($code)) {
        $error = 'Doğrulama kodunu girin.';
    } else {
        // 2FA kodunu doğrula
        $result = $security->verify2FA($code);

        if ($result['success']) {
            // 2FA başarılı, oturumu tamamla
            $_SESSION['requires_2fa'] = false;
            redirect(APP_URL);
        } else {
            $error = $result['message'];
        }
    }
}

// Yeni kod gönder
if (isset($_POST['resend_code'])) {
    $result = $security->send2FACode();

    if ($result['success']) {
        $success = 'Yeni doğrulama kodu gönderildi.';
    } else {
        $error = $result['message'];
    }
}

// SEO meta etiketlerini oluştur
$metaTags = $seo->generateMetaTags('2fa', [
    'title' => 'İki Faktörlü Doğrulama',
    'description' => 'Hesabınızı güvence altına alın',
    'robots' => 'noindex, follow'
]);
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php echo $metaTags; ?>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <!-- Header -->
    <header>
        <div class="container">
            <div class="logo">
                <a href="<?php echo APP_URL; ?>">
                    <img src="<?php echo $siteSettings['site_logo'] ?? 'assets/images/logo.png'; ?>" alt="<?php echo $siteSettings['site_title']; ?>">
                </a>
            </div>
            <nav>
                <ul>
                    <li><a href="<?php echo APP_URL; ?>">Ana Sayfa</a></li>
                    <?php foreach ($categories as $cat): ?>
                    <li>
                        <a href="<?php echo APP_URL; ?>/kategori/<?php echo $cat['slug']; ?>">
                            <?php echo $cat['ad']; ?>
                        </a>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </nav>
        </div>
    </header>

    <!-- Main Content -->
    <main>
        <div class="container">
            <div class="auth-container">
                <div class="auth-box">
                    <h1>İki Faktörlü Doğrulama</h1>

                    <?php if ($error): ?>
                    <div class="alert alert-error">
                        <i class="fas fa-exclamation-circle"></i>
                        <?php echo $error; ?>
                    </div>
                    <?php endif; ?>

                    <?php if ($success): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i>
                        <?php echo $success; ?>
                    </div>
                    <?php endif; ?>

                    <div class="twofa-info">
                        <p>
                            <i class="fas fa-shield-alt"></i>
                            Hesabınızı güvence altına almak için iki faktörlü doğrulama kullanıyoruz.
                        </p>
                        <p>
                            <i class="fas fa-mobile-alt"></i>
                            Telefonunuza gönderilen doğrulama kodunu girin.
                        </p>
                    </div>

                    <form class="auth-form" method="POST" action="">
                        <input type="hidden" name="csrf_token" value="<?php echo getCsrfToken(); ?>">
                        
                        <div class="form-group">
                            <label for="code">Doğrulama Kodu</label>
                            <div class="input-group">
                                <i class="fas fa-key"></i>
                                <input type="text" id="code" name="code" required 
                                       pattern="[0-9]{6}" maxlength="6" 
                                       placeholder="6 haneli kod"
                                       value="<?php echo htmlspecialchars($_POST['code'] ?? ''); ?>">
                            </div>
                        </div>

                        <button type="submit" class="btn btn-block">Doğrula</button>
                    </form>

                    <form method="POST" action="" class="resend-form">
                        <input type="hidden" name="csrf_token" value="<?php echo getCsrfToken(); ?>">
                        <button type="submit" name="resend_code" class="btn btn-outline btn-block">
                            <i class="fas fa-redo"></i> Yeni Kod Gönder
                        </button>
                    </form>

                    <div class="auth-links">
                        <a href="<?php echo APP_URL; ?>/logout.php">Çıkış Yap</a>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer>
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3>Hakkımızda</h3>
                    <p><?php echo $siteSettings['site_description'] ?? ''; ?></p>
                </div>
                <div class="footer-section">
                    <h3>Kategoriler</h3>
                    <ul>
                        <?php foreach ($categories as $cat): ?>
                        <li><a href="<?php echo APP_URL; ?>/kategori/<?php echo $cat['slug']; ?>"><?php echo $cat['ad']; ?></a></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <div class="footer-section">
                    <h3>İletişim</h3>
                    <ul>
                        <li><i class="fas fa-envelope"></i> <?php echo $siteSettings['contact_email'] ?? ''; ?></li>
                        <li><i class="fas fa-phone"></i> <?php echo $siteSettings['contact_phone'] ?? ''; ?></li>
                        <li><i class="fas fa-map-marker-alt"></i> <?php echo $siteSettings['contact_address'] ?? ''; ?></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h3>Sosyal Medya</h3>
                    <div class="social-links">
                        <?php if (!empty($siteSettings['social_facebook'])): ?>
                        <a href="<?php echo $siteSettings['social_facebook']; ?>" target="_blank"><i class="fab fa-facebook"></i></a>
                        <?php endif; ?>
                        <?php if (!empty($siteSettings['social_twitter'])): ?>
                        <a href="<?php echo $siteSettings['social_twitter']; ?>" target="_blank"><i class="fab fa-twitter"></i></a>
                        <?php endif; ?>
                        <?php if (!empty($siteSettings['social_instagram'])): ?>
                        <a href="<?php echo $siteSettings['social_instagram']; ?>" target="_blank"><i class="fab fa-instagram"></i></a>
                        <?php endif; ?>
                        <?php if (!empty($siteSettings['social_youtube'])): ?>
                        <a href="<?php echo $siteSettings['social_youtube']; ?>" target="_blank"><i class="fab fa-youtube"></i></a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; <?php echo date('Y'); ?> <?php echo $siteSettings['site_title']; ?>. Tüm hakları saklıdır.</p>
            </div>
        </div>
    </footer>

    <script src="assets/js/main.js"></script>
    <script>
    // Sadece rakam girişine izin ver
    document.getElementById('code').addEventListener('input', function(e) {
        this.value = this.value.replace(/[^0-9]/g, '');
    });
    </script>
</body>
</html> 