<?php
require_once 'init.php';

// SEO meta etiketlerini oluştur
$metaTags = $seo->generateMetaTags('terms', [
    'title' => 'Kullanıcı Sözleşmesi',
    'description' => 'Kullanıcı sözleşmesi ve gizlilik politikası',
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
                <h1>Kullanıcı Sözleşmesi</h1>

                <div class="terms-content">
                    <section>
                        <h2>1. Genel Hükümler</h2>
                        <p>
                            Bu kullanıcı sözleşmesi, <?php echo $siteSettings['site_title']; ?> web sitesini kullanımınızı düzenleyen 
                            şartları ve koşulları içermektedir. Siteyi kullanarak bu sözleşmeyi kabul etmiş sayılırsınız.
                        </p>
                        <p>
                            Sözleşme, site kullanımına ilişkin tüm hak ve yükümlülükleri düzenler. Sitenin kullanımı, 
                            bu sözleşmenin kabul edildiği anlamına gelir.
                        </p>
                    </section>

                    <section>
                        <h2>2. Üyelik</h2>
                        <p>
                            Sitemize üye olmak için:
                        </p>
                        <ul>
                            <li>18 yaşından büyük olmanız</li>
                            <li>Geçerli bir e-posta adresine sahip olmanız</li>
                            <li>Doğru ve güncel bilgiler vermeniz</li>
                            <li>Hesap güvenliğinizi sağlamanız</li>
                        </ul>
                        <p>
                            Üyeliğiniz, sözleşmeyi ihlal etmeniz durumunda sonlandırılabilir.
                        </p>
                    </section>

                    <section>
                        <h2>3. Kullanıcı Hakları ve Yükümlülükleri</h2>
                        <p>
                            Kullanıcılar olarak:
                        </p>
                        <ul>
                            <li>Site içeriğini kişisel kullanım için görüntüleyebilirsiniz</li>
                            <li>Yorum yapabilir ve içerik paylaşabilirsiniz</li>
                            <li>Diğer kullanıcılara saygılı davranmalısınız</li>
                            <li>Yasadışı içerik paylaşmamalısınız</li>
                            <li>Spam ve zararlı içerik paylaşmamalısınız</li>
                        </ul>
                    </section>

                    <section>
                        <h2>4. Gizlilik</h2>
                        <p>
                            Kişisel verileriniz, gizlilik politikamız kapsamında korunmaktadır. Verileriniz:
                        </p>
                        <ul>
                            <li>Güvenli bir şekilde saklanır</li>
                            <li>Üçüncü taraflarla paylaşılmaz</li>
                            <li>Yasal zorunluluklar dışında kullanılmaz</li>
                        </ul>
                    </section>

                    <section>
                        <h2>5. Fikri Mülkiyet</h2>
                        <p>
                            Sitedeki tüm içerikler (yazılar, görseller, logolar vb.) <?php echo $siteSettings['site_title']; ?>'nin 
                            fikri mülkiyetidir. İçeriklerin:
                        </p>
                        <ul>
                            <li>Kopyalanması</li>
                            <li>Dağıtılması</li>
                            <li>Değiştirilmesi</li>
                            <li>Ticari amaçla kullanılması</li>
                        </ul>
                        <p>
                            yasaktır ve yasal işlemlere tabi tutulabilir.
                        </p>
                    </section>

                    <section>
                        <h2>6. Sorumluluk Reddi</h2>
                        <p>
                            <?php echo $siteSettings['site_title']; ?>, site içeriğinin:
                        </p>
                        <ul>
                            <li>Doğruluğunu</li>
                            <li>Güncelliğini</li>
                            <li>Kesinliğini</li>
                            <li>Uygunluğunu</li>
                        </ul>
                        <p>
                            garanti etmez. Site kullanımından doğacak zararlardan sorumlu değildir.
                        </p>
                    </section>

                    <section>
                        <h2>7. Değişiklikler</h2>
                        <p>
                            Bu sözleşme, önceden haber vermeksizin değiştirilebilir. Değişiklikler, 
                            sitede yayınlandığı anda yürürlüğe girer.
                        </p>
                    </section>

                    <section>
                        <h2>8. İletişim</h2>
                        <p>
                            Sözleşme ile ilgili sorularınız için:
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