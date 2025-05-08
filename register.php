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
    $username = $_POST['username'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $password_confirm = $_POST['password_confirm'] ?? '';
    $name = $_POST['name'] ?? '';
    $surname = $_POST['surname'] ?? '';
    $accept_terms = isset($_POST['accept_terms']);

    // Form doğrulama
    if (empty($username) || empty($email) || empty($password) || empty($password_confirm) || empty($name) || empty($surname)) {
        $error = 'Tüm alanları doldurun.';
    } elseif ($password !== $password_confirm) {
        $error = 'Şifreler eşleşmiyor.';
    } elseif (!$accept_terms) {
        $error = 'Kullanım koşullarını kabul etmelisiniz.';
    } else {
        // Kullanıcı kaydını dene
        $result = $user->register([
            'username' => $username,
            'email' => $email,
            'password' => $password,
            'name' => $name,
            'surname' => $surname
        ]);

        if ($result['success']) {
            $success = 'Kayıt başarılı! Giriş yapabilirsiniz.';
            // E-posta doğrulama gerekiyorsa
            if ($result['requires_verification']) {
                $success .= ' Lütfen e-posta adresinizi doğrulayın.';
            }
        } else {
            $error = $result['message'];
        }
    }
}

// SEO meta etiketlerini oluştur
$metaTags = $seo->generateMetaTags('register', [
    'title' => 'Kayıt Ol',
    'description' => 'Yeni hesap oluşturun',
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
                    <h1>Kayıt Ol</h1>

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
                            <label for="username">Kullanıcı Adı</label>
                            <div class="input-group">
                                <i class="fas fa-user"></i>
                                <input type="text" id="username" name="username" required 
                                       value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>">
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="email">E-posta</label>
                            <div class="input-group">
                                <i class="fas fa-envelope"></i>
                                <input type="email" id="email" name="email" required 
                                       value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="name">Ad</label>
                                <div class="input-group">
                                    <i class="fas fa-user"></i>
                                    <input type="text" id="name" name="name" required 
                                           value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>">
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="surname">Soyad</label>
                                <div class="input-group">
                                    <i class="fas fa-user"></i>
                                    <input type="text" id="surname" name="surname" required 
                                           value="<?php echo htmlspecialchars($_POST['surname'] ?? ''); ?>">
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="password">Şifre</label>
                            <div class="input-group">
                                <i class="fas fa-lock"></i>
                                <input type="password" id="password" name="password" required>
                                <button type="button" class="toggle-password" onclick="togglePassword('password')">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                            <small class="form-text">En az 8 karakter, bir büyük harf, bir küçük harf ve bir rakam içermelidir.</small>
                        </div>

                        <div class="form-group">
                            <label for="password_confirm">Şifre Tekrar</label>
                            <div class="input-group">
                                <i class="fas fa-lock"></i>
                                <input type="password" id="password_confirm" name="password_confirm" required>
                                <button type="button" class="toggle-password" onclick="togglePassword('password_confirm')">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="checkbox-label">
                                <input type="checkbox" name="accept_terms" required 
                                       <?php echo isset($_POST['accept_terms']) ? 'checked' : ''; ?>>
                                <a href="<?php echo APP_URL; ?>/terms.php" target="_blank">Kullanım Koşulları</a>'nı kabul ediyorum
                            </label>
                        </div>

                        <button type="submit" class="btn btn-block">Kayıt Ol</button>
                    </form>

                    <div class="auth-links">
                        <span>Zaten hesabınız var mı?</span>
                        <a href="<?php echo APP_URL; ?>/login.php">Giriş Yap</a>
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
    function togglePassword(inputId) {
        const passwordInput = document.getElementById(inputId);
        const toggleButton = passwordInput.parentNode.querySelector('.toggle-password i');
        
        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            toggleButton.classList.remove('fa-eye');
            toggleButton.classList.add('fa-eye-slash');
        } else {
            passwordInput.type = 'password';
            toggleButton.classList.remove('fa-eye-slash');
            toggleButton.classList.add('fa-eye');
        }
    }
    </script>
</body>
</html> 