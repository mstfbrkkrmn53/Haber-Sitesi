<?php
require_once 'init.php';

$error = '';
$success = '';

// Form gönderildi mi kontrol et
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF kontrolü
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
        $error = 'Güvenlik doğrulaması başarısız. Lütfen sayfayı yenileyip tekrar deneyin.';
    } else {
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $subject = trim($_POST['subject'] ?? '');
        $message = trim($_POST['message'] ?? '');

        // Validasyon
        if (empty($name)) {
            $error = 'Adınızı girin.';
        } elseif (empty($email)) {
            $error = 'E-posta adresinizi girin.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Geçerli bir e-posta adresi girin.';
        } elseif (empty($subject)) {
            $error = 'Konu girin.';
        } elseif (empty($message)) {
            $error = 'Mesajınızı girin.';
        } else {
            // E-posta gönder
            $to = $siteSettings['contact_email'] ?? '';
            $headers = "From: $email\r\n";
            $headers .= "Reply-To: $email\r\n";
            $headers .= "Content-Type: text/html; charset=UTF-8\r\n";

            $emailBody = "
                <h2>İletişim Formu Mesajı</h2>
                <p><strong>Gönderen:</strong> $name</p>
                <p><strong>E-posta:</strong> $email</p>
                <p><strong>Konu:</strong> $subject</p>
                <p><strong>Mesaj:</strong></p>
                <p>" . nl2br(htmlspecialchars($message)) . "</p>
            ";

            if (mail($to, "İletişim Formu: $subject", $emailBody, $headers)) {
                $success = 'Mesajınız başarıyla gönderildi. En kısa sürede size dönüş yapacağız.';
                // Formu temizle
                $name = $email = $subject = $message = '';
            } else {
                $error = 'Mesajınız gönderilemedi. Lütfen daha sonra tekrar deneyin.';
            }
        }
    }
}

// SEO meta etiketlerini oluştur
$metaTags = $seo->generateMetaTags('contact', [
    'title' => 'İletişim',
    'description' => 'Bizimle iletişime geçin',
    'robots' => 'index, follow'
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
            <div class="user-menu">
                <?php if (isLoggedIn()): ?>
                    <a href="<?php echo APP_URL; ?>/profil.php" class="btn">Profilim</a>
                    <a href="<?php echo APP_URL; ?>/logout.php" class="btn btn-outline">Çıkış</a>
                <?php else: ?>
                    <a href="<?php echo APP_URL; ?>/login.php" class="btn">Giriş</a>
                    <a href="<?php echo APP_URL; ?>/register.php" class="btn btn-outline">Kayıt Ol</a>
                <?php endif; ?>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main>
        <div class="container">
            <div class="page-content">
                <h1>İletişim</h1>

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

                <div class="contact-container">
                    <div class="contact-info">
                        <h2>İletişim Bilgileri</h2>
                        <div class="info-item">
                            <i class="fas fa-map-marker-alt"></i>
                            <div>
                                <h3>Adres</h3>
                                <p><?php echo $siteSettings['contact_address'] ?? ''; ?></p>
                            </div>
                        </div>
                        <div class="info-item">
                            <i class="fas fa-phone"></i>
                            <div>
                                <h3>Telefon</h3>
                                <p><?php echo $siteSettings['contact_phone'] ?? ''; ?></p>
                            </div>
                        </div>
                        <div class="info-item">
                            <i class="fas fa-envelope"></i>
                            <div>
                                <h3>E-posta</h3>
                                <p><?php echo $siteSettings['contact_email'] ?? ''; ?></p>
                            </div>
                        </div>
                        <div class="info-item">
                            <i class="fas fa-clock"></i>
                            <div>
                                <h3>Çalışma Saatleri</h3>
                                <p>Pazartesi - Cuma: 09:00 - 18:00</p>
                                <p>Cumartesi: 10:00 - 14:00</p>
                                <p>Pazar: Kapalı</p>
                            </div>
                        </div>
                        <div class="social-links">
                            <h3>Sosyal Medya</h3>
                            <div class="social-icons">
                                <?php if (!empty($siteSettings['social_facebook'])): ?>
                                <a href="<?php echo $siteSettings['social_facebook']; ?>" target="_blank" title="Facebook">
                                    <i class="fab fa-facebook"></i>
                                </a>
                                <?php endif; ?>
                                <?php if (!empty($siteSettings['social_twitter'])): ?>
                                <a href="<?php echo $siteSettings['social_twitter']; ?>" target="_blank" title="Twitter">
                                    <i class="fab fa-twitter"></i>
                                </a>
                                <?php endif; ?>
                                <?php if (!empty($siteSettings['social_instagram'])): ?>
                                <a href="<?php echo $siteSettings['social_instagram']; ?>" target="_blank" title="Instagram">
                                    <i class="fab fa-instagram"></i>
                                </a>
                                <?php endif; ?>
                                <?php if (!empty($siteSettings['social_youtube'])): ?>
                                <a href="<?php echo $siteSettings['social_youtube']; ?>" target="_blank" title="YouTube">
                                    <i class="fab fa-youtube"></i>
                                </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <div class="contact-form">
                        <h2>Bize Ulaşın</h2>
                        <form method="POST" action="">
                            <input type="hidden" name="csrf_token" value="<?php echo getCsrfToken(); ?>">
                            
                            <div class="form-group">
                                <label for="name">Adınız Soyadınız *</label>
                                <div class="input-group">
                                    <i class="fas fa-user"></i>
                                    <input type="text" id="name" name="name" required 
                                           value="<?php echo htmlspecialchars($name ?? ''); ?>">
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="email">E-posta Adresiniz *</label>
                                <div class="input-group">
                                    <i class="fas fa-envelope"></i>
                                    <input type="email" id="email" name="email" required 
                                           value="<?php echo htmlspecialchars($email ?? ''); ?>">
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="subject">Konu *</label>
                                <div class="input-group">
                                    <i class="fas fa-tag"></i>
                                    <input type="text" id="subject" name="subject" required 
                                           value="<?php echo htmlspecialchars($subject ?? ''); ?>">
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="message">Mesajınız *</label>
                                <div class="input-group">
                                    <i class="fas fa-comment"></i>
                                    <textarea id="message" name="message" rows="5" required><?php echo htmlspecialchars($message ?? ''); ?></textarea>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-block">
                                <i class="fas fa-paper-plane"></i> Gönder
                            </button>
                        </form>
                    </div>
                </div>

                <div class="map-container">
                    <h2>Konum</h2>
                    <div class="map">
                        <!-- Google Maps iframe'i buraya eklenecek -->
                        <iframe 
                            src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d..." 
                            width="100%" 
                            height="450" 
                            style="border:0;" 
                            allowfullscreen="" 
                            loading="lazy">
                        </iframe>
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