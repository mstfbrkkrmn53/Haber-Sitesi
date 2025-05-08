<?php
require_once 'init.php';

// SEO meta etiketlerini oluştur
$metaTags = $seo->generateMetaTags('privacy', [
    'title' => 'Gizlilik Politikası',
    'description' => 'Kişisel verilerinizin nasıl korunduğu ve işlendiği hakkında bilgi',
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
                <h1>Gizlilik Politikası</h1>

                <div class="privacy-content">
                    <section>
                        <h2>1. Giriş</h2>
                        <p>
                            <?php echo $siteSettings['site_title']; ?> olarak, kişisel verilerinizin güvenliği ve gizliliği bizim için önemlidir. 
                            Bu gizlilik politikası, kişisel verilerinizin nasıl toplandığını, kullanıldığını ve korunduğunu açıklar.
                        </p>
                        <p>
                            Bu politikayı kabul ederek, kişisel verilerinizin bu politikada belirtilen şekilde işlenmesine 
                            onay vermiş olursunuz.
                        </p>
                    </section>

                    <section>
                        <h2>2. Toplanan Veriler</h2>
                        <p>
                            Sitemizi kullanırken aşağıdaki kişisel verilerinizi toplayabiliriz:
                        </p>
                        <ul>
                            <li>Ad ve soyad</li>
                            <li>E-posta adresi</li>
                            <li>Telefon numarası</li>
                            <li>IP adresi</li>
                            <li>Tarayıcı bilgileri</li>
                            <li>Kullanım istatistikleri</li>
                        </ul>
                    </section>

                    <section>
                        <h2>3. Verilerin Kullanımı</h2>
                        <p>
                            Topladığımız verileri aşağıdaki amaçlar için kullanırız:
                        </p>
                        <ul>
                            <li>Hizmetlerimizi sunmak ve geliştirmek</li>
                            <li>Hesabınızı yönetmek</li>
                            <li>İletişim kurmak</li>
                            <li>Güvenliği sağlamak</li>
                            <li>Yasal yükümlülükleri yerine getirmek</li>
                        </ul>
                    </section>

                    <section>
                        <h2>4. Veri Güvenliği</h2>
                        <p>
                            Kişisel verilerinizin güvenliği için:
                        </p>
                        <ul>
                            <li>SSL şifreleme kullanıyoruz</li>
                            <li>Düzenli güvenlik güncellemeleri yapıyoruz</li>
                            <li>Erişim kontrolü uyguluyoruz</li>
                            <li>Veri yedekleme sistemleri kullanıyoruz</li>
                        </ul>
                    </section>

                    <section>
                        <h2>5. Çerezler</h2>
                        <p>
                            Sitemizde çerezler kullanılmaktadır. Çerezler:
                        </p>
                        <ul>
                            <li>Oturum yönetimi</li>
                            <li>Tercihlerinizi hatırlama</li>
                            <li>Site performansını ölçme</li>
                            <li>Kişiselleştirilmiş içerik sunma</li>
                        </ul>
                        <p>
                            için kullanılır. Tarayıcı ayarlarınızdan çerezleri kontrol edebilirsiniz.
                        </p>
                    </section>

                    <section>
                        <h2>6. Üçüncü Taraflar</h2>
                        <p>
                            Kişisel verileriniz, aşağıdaki durumlar dışında üçüncü taraflarla paylaşılmaz:
                        </p>
                        <ul>
                            <li>Yasal zorunluluk durumunda</li>
                            <li>Hizmet sağlayıcılarımızla (veri işleme amaçlı)</li>
                            <li>İzniniz olduğunda</li>
                        </ul>
                    </section>

                    <section>
                        <h2>7. Veri Saklama</h2>
                        <p>
                            Kişisel verileriniz:
                        </p>
                        <ul>
                            <li>Hesabınız aktif olduğu sürece saklanır</li>
                            <li>Hesabınızı sildiğinizde veya devre dışı bıraktığınızda silinir</li>
                            <li>Yasal yükümlülükler gerektiğinde daha uzun süre saklanabilir</li>
                        </ul>
                    </section>

                    <section>
                        <h2>8. Haklarınız</h2>
                        <p>
                            Kişisel verilerinizle ilgili olarak:
                        </p>
                        <ul>
                            <li>Verilerinize erişim talep edebilirsiniz</li>
                            <li>Düzeltme talep edebilirsiniz</li>
                            <li>Silme talep edebilirsiniz</li>
                            <li>İşlemeyi kısıtlama talep edebilirsiniz</li>
                            <li>Veri taşınabilirliği talep edebilirsiniz</li>
                            <li>İtiraz etme hakkınız vardır</li>
                        </ul>
                    </section>

                    <section>
                        <h2>9. Değişiklikler</h2>
                        <p>
                            Bu gizlilik politikası, önceden haber vermeksizin değiştirilebilir. Değişiklikler, 
                            sitede yayınlandığı anda yürürlüğe girer.
                        </p>
                    </section>

                    <section>
                        <h2>10. İletişim</h2>
                        <p>
                            Gizlilik politikamız hakkında sorularınız için:
                        </p>
                        <ul>
                            <li>E-posta: <?php echo $siteSettings['contact_email'] ?? 'info@example.com'; ?></li>
                            <li>Telefon: <?php echo $siteSettings['contact_phone'] ?? '+90 (212) 123 45 67'; ?></li>
                            <li>Adres: <?php echo $siteSettings['contact_address'] ?? 'Örnek Mahallesi, Örnek Sokak No:1, İstanbul'; ?></li>
                        </ul>
                    </section>
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