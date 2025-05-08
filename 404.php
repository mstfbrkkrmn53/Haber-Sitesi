<?php
require_once 'init.php';

// SEO meta etiketlerini oluştur
$metaTags = $seo->generateMetaTags('404', [
    'title' => 'Sayfa Bulunamadı',
    'description' => 'Aradığınız sayfa bulunamadı',
    'robots' => 'noindex, nofollow'
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
            <div class="error-page">
                <h1>404</h1>
                <p>Aradığınız sayfa bulunamadı.</p>
                <div class="error-actions">
                    <a href="<?php echo APP_URL; ?>" class="btn btn-primary">
                        <i class="fas fa-home"></i> Ana Sayfaya Dön
                    </a>
                    <a href="<?php echo APP_URL; ?>/contact.php" class="btn btn-outline">
                        <i class="fas fa-envelope"></i> İletişime Geç
                    </a>
                </div>
                <div class="error-suggestions">
                    <h2>Belki bunlar ilginizi çekebilir:</h2>
                    <div class="news-grid">
                        <?php
                        // Son eklenen haberleri getir
                        $latestNews = $db->query("
                            SELECT n.*, c.ad as kategori_adi, c.slug as kategori_slug 
                            FROM haberler n 
                            LEFT JOIN kategoriler c ON n.kategori_id = c.id 
                            WHERE n.durum = 1 
                            ORDER BY n.eklenme_tarihi DESC 
                            LIMIT 3
                        ")->fetchAll(PDO::FETCH_ASSOC);

                        foreach ($latestNews as $news):
                        ?>
                        <div class="news-card">
                            <div class="news-image">
                                <img src="<?php echo $news['resim'] ? 'uploads/' . $news['resim'] : 'assets/images/default.jpg'; ?>" alt="<?php echo $news['baslik']; ?>">
                                <span class="news-category">
                                    <a href="<?php echo APP_URL; ?>/kategori/<?php echo $news['kategori_slug']; ?>">
                                        <?php echo $news['kategori_adi']; ?>
                                    </a>
                                </span>
                            </div>
                            <div class="news-content">
                                <h3 class="news-title">
                                    <a href="<?php echo APP_URL; ?>/haber/<?php echo $news['slug']; ?>">
                                        <?php echo $news['baslik']; ?>
                                    </a>
                                </h3>
                                <p class="news-excerpt">
                                    <?php echo mb_substr(strip_tags($news['icerik']), 0, 100) . '...'; ?>
                                </p>
                                <div class="news-meta">
                                    <span>
                                        <i class="fas fa-calendar"></i>
                                        <?php echo date('d.m.Y', strtotime($news['eklenme_tarihi'])); ?>
                                    </span>
                                    <span>
                                        <i class="fas fa-eye"></i>
                                        <?php echo $news['goruntulenme']; ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
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