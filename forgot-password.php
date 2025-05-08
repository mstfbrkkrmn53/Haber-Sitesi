<?php
require_once 'init.php';

// Kullanıcı zaten giriş yapmışsa ana sayfaya yönlendir
if (isLoggedIn()) {
    redirect(APP_URL);
}

$error = '';
$success = '';

// Form gönderildi mi kontrol et
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';

    if (empty($email)) {
        $error = 'E-posta adresinizi girin.';
    } else {
        // Şifre sıfırlama e-postası gönder
        $result = $user->sendPasswordResetEmail($email);

        if ($result['success']) {
            $success = 'Şifre sıfırlama bağlantısı e-posta adresinize gönderildi.';
        } else {
            $error = $result['message'];
        }
    }
}

// SEO meta etiketlerini oluştur
$metaTags = $seo->generateMetaTags('forgot-password', [
    'title' => 'Şifremi Unuttum',
    'description' => 'Şifrenizi sıfırlayın',
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
                    <h1>Şifremi Unuttum</h1>

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

                    <form class="auth-form" method="POST" action="">
                        <input type="hidden" name="csrf_token" value="<?php echo getCsrfToken(); ?>">
                        
                        <div class="form-group">
                            <label for="email">E-posta</label>
                            <div class="input-group">
                                <i class="fas fa-envelope"></i>
                                <input type="email" id="email" name="email" required 
                                       value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                            </div>
                            <small class="form-text">
                                Kayıtlı e-posta adresinizi girin. Size şifre sıfırlama bağlantısı göndereceğiz.
                            </small>
                        </div>

                        <button type="submit" class="btn btn-block">Şifre Sıfırlama Bağlantısı Gönder</button>
                    </form>

                    <div class="auth-links">
                        <a href="<?php echo APP_URL; ?>/login.php">Giriş Yap</a>
                        <span class="separator">|</span>
                        <a href="<?php echo APP_URL; ?>/register.php">Hesap Oluştur</a>
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
</body>
</html> 