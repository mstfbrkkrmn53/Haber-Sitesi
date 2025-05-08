<?php
require_once 'init.php';

// SEO meta etiketlerini oluştur
$metaTags = $seo->generateMetaTags('about', [
    'title' => 'Hakkımızda',
    'description' => 'Şirketimiz ve ekibimiz hakkında bilgi',
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
                <h1>Hakkımızda</h1>

                <div class="about-content">
                    <section class="company-intro">
                        <div class="intro-text">
                            <h2>Biz Kimiz?</h2>
                            <p>
                                <?php echo $siteSettings['site_description'] ?? ''; ?>
                            </p>
                            <p>
                                2020 yılında kurulan şirketimiz, müşterilerimize en kaliteli hizmeti sunmak için 
                                çalışmaktadır. Deneyimli ekibimiz ve modern altyapımız ile sektörde öncü 
                                konumdayız.
                            </p>
                        </div>
                        <div class="intro-image">
                            <img src="assets/images/about/company.jpg" alt="Şirketimiz">
                        </div>
                    </section>

                    <section class="mission-vision">
                        <div class="mission">
                            <h2>Misyonumuz</h2>
                            <p>
                                Müşterilerimize en kaliteli hizmeti sunmak, sektörde öncü olmak ve 
                                sürdürülebilir büyüme sağlamak.
                            </p>
                        </div>
                        <div class="vision">
                            <h2>Vizyonumuz</h2>
                            <p>
                                Sektörde lider konuma gelmek, yenilikçi çözümler üretmek ve 
                                müşteri memnuniyetini en üst düzeyde tutmak.
                            </p>
                        </div>
                    </section>

                    <section class="values">
                        <h2>Değerlerimiz</h2>
                        <div class="values-grid">
                            <div class="value-item">
                                <i class="fas fa-handshake"></i>
                                <h3>Güvenilirlik</h3>
                                <p>Müşterilerimize karşı her zaman dürüst ve şeffaf davranırız.</p>
                            </div>
                            <div class="value-item">
                                <i class="fas fa-lightbulb"></i>
                                <h3>Yenilikçilik</h3>
                                <p>Sürekli gelişim ve yenilik için çalışırız.</p>
                            </div>
                            <div class="value-item">
                                <i class="fas fa-users"></i>
                                <h3>Müşteri Odaklılık</h3>
                                <p>Müşterilerimizin ihtiyaçlarını en iyi şekilde karşılarız.</p>
                            </div>
                            <div class="value-item">
                                <i class="fas fa-chart-line"></i>
                                <h3>Kalite</h3>
                                <p>En yüksek kalite standartlarında hizmet sunarız.</p>
                            </div>
                        </div>
                    </section>

                    <section class="team">
                        <h2>Ekibimiz</h2>
                        <div class="team-grid">
                            <div class="team-member">
                                <img src="assets/images/team/member1.jpg" alt="Ahmet Yılmaz">
                                <h3>Ahmet Yılmaz</h3>
                                <p class="position">Genel Müdür</p>
                                <p class="bio">
                                    15 yıllık sektör deneyimi ile şirketimizin liderliğini yapmaktadır.
                                </p>
                                <div class="social-links">
                                    <a href="#" target="_blank"><i class="fab fa-linkedin"></i></a>
                                    <a href="#" target="_blank"><i class="fab fa-twitter"></i></a>
                                </div>
                            </div>
                            <div class="team-member">
                                <img src="assets/images/team/member2.jpg" alt="Ayşe Demir">
                                <h3>Ayşe Demir</h3>
                                <p class="position">Operasyon Müdürü</p>
                                <p class="bio">
                                    Operasyonlarımızın sorunsuz yürümesi için çalışmaktadır.
                                </p>
                                <div class="social-links">
                                    <a href="#" target="_blank"><i class="fab fa-linkedin"></i></a>
                                    <a href="#" target="_blank"><i class="fab fa-twitter"></i></a>
                                </div>
                            </div>
                            <div class="team-member">
                                <img src="assets/images/team/member3.jpg" alt="Mehmet Kaya">
                                <h3>Mehmet Kaya</h3>
                                <p class="position">Teknik Direktör</p>
                                <p class="bio">
                                    Teknik altyapımızın geliştirilmesinden sorumludur.
                                </p>
                                <div class="social-links">
                                    <a href="#" target="_blank"><i class="fab fa-linkedin"></i></a>
                                    <a href="#" target="_blank"><i class="fab fa-twitter"></i></a>
                                </div>
                            </div>
                            <div class="team-member">
                                <img src="assets/images/team/member4.jpg" alt="Zeynep Şahin">
                                <h3>Zeynep Şahin</h3>
                                <p class="position">Müşteri İlişkileri Müdürü</p>
                                <p class="bio">
                                    Müşteri memnuniyetini en üst düzeyde tutmak için çalışmaktadır.
                                </p>
                                <div class="social-links">
                                    <a href="#" target="_blank"><i class="fab fa-linkedin"></i></a>
                                    <a href="#" target="_blank"><i class="fab fa-twitter"></i></a>
                                </div>
                            </div>
                        </div>
                    </section>

                    <section class="achievements">
                        <h2>Başarılarımız</h2>
                        <div class="achievements-grid">
                            <div class="achievement-item">
                                <i class="fas fa-trophy"></i>
                                <h3>2023 Yılın En İyi Şirketi</h3>
                                <p>Sektörümüzde en prestijli ödülü kazandık.</p>
                            </div>
                            <div class="achievement-item">
                                <i class="fas fa-users"></i>
                                <h3>10.000+ Müşteri</h3>
                                <p>Mutlu müşteri sayımız her geçen gün artıyor.</p>
                            </div>
                            <div class="achievement-item">
                                <i class="fas fa-award"></i>
                                <h3>ISO 9001 Sertifikası</h3>
                                <p>Kalite standartlarımız uluslararası düzeyde.</p>
                            </div>
                            <div class="achievement-item">
                                <i class="fas fa-star"></i>
                                <h3>%98 Müşteri Memnuniyeti</h3>
                                <p>Müşterilerimizin memnuniyeti bizim için en önemli başarı.</p>
                            </div>
                        </div>
                    </section>

                    <section class="cta">
                        <h2>Bizimle Çalışmak İster misiniz?</h2>
                        <p>
                            Dinamik ekibimize katılmak ve kariyerinizde yeni bir sayfa açmak için 
                            bizimle iletişime geçin.
                        </p>
                        <a href="<?php echo APP_URL; ?>/contact.php" class="btn btn-large">
                            <i class="fas fa-envelope"></i> İletişime Geçin
                        </a>
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