<?php
require_once 'init.php';

// SEO meta etiketlerini oluştur
$metaTags = $seo->generateMetaTags('home', [
    'title' => $siteSettings['site_title'] ?? APP_NAME,
    'description' => $siteSettings['site_description'] ?? '',
    'keywords' => $siteSettings['site_keywords'] ?? ''
]);

// Öne çıkan haberleri al
$featuredNews = $cache->cacheQuery(
    'featured_news',
    "SELECT h.*, k.ad as kategori_adi, u.ad_soyad as yazar_adi 
     FROM haberler h 
     LEFT JOIN kategoriler k ON h.kategori_id = k.id 
     LEFT JOIN kullanicilar u ON h.yazar_id = u.id 
     WHERE h.durum = 'yayinda' AND h.one_cikar = 1 
     ORDER BY h.created_at DESC 
     LIMIT 5"
);

// Son haberleri al
$latestNews = $cache->cacheLatestNews(10);

// Popüler haberleri al
$popularNews = $cache->cachePopularNews(10);

// Kategorileri al
$categories = $cache->cacheQuery(
    'categories',
    "SELECT * FROM kategoriler WHERE aktif = 1 ORDER BY sira"
);

// Sayfa başlığı
$pageTitle = $siteSettings['site_title'] ?? APP_NAME;
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
                    <img src="<?php echo $siteSettings['site_logo'] ?? 'assets/images/logo.png'; ?>" alt="<?php echo $pageTitle; ?>">
                </a>
            </div>
            <nav>
                <ul>
                    <li><a href="<?php echo APP_URL; ?>" class="<?php echo isActivePage('index.php') ? 'active' : ''; ?>">Ana Sayfa</a></li>
                    <?php foreach ($categories as $cat): ?>
                    <li>
                        <a href="<?php echo APP_URL; ?>/kategori/<?php echo $cat['slug']; ?>" 
                           class="<?php echo isActivePage('kategori.php') && $_GET['slug'] === $cat['slug'] ? 'active' : ''; ?>">
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
            <!-- Featured News -->
            <section class="featured-news">
                <h2>Öne Çıkan Haberler</h2>
                <div class="news-grid">
                    <?php foreach ($featuredNews as $news): ?>
                    <article class="news-card featured">
                        <div class="news-image">
                            <img src="<?php echo $news['resim']; ?>" alt="<?php echo $news['baslik']; ?>">
                        </div>
                        <div class="news-content">
                            <span class="category"><?php echo $news['kategori_adi']; ?></span>
                            <h3><a href="<?php echo APP_URL; ?>/haber/<?php echo $news['slug']; ?>"><?php echo $news['baslik']; ?></a></h3>
                            <p><?php echo truncate($news['ozet'], 150); ?></p>
                            <div class="news-meta">
                                <span><i class="fas fa-user"></i> <?php echo $news['yazar_adi']; ?></span>
                                <span><i class="fas fa-clock"></i> <?php echo formatDate($news['created_at']); ?></span>
                                <span><i class="fas fa-eye"></i> <?php echo $news['goruntulenme']; ?></span>
                            </div>
                        </div>
                    </article>
                    <?php endforeach; ?>
                </div>
            </section>

            <!-- Latest News -->
            <section class="latest-news">
                <h2>Son Haberler</h2>
                <div class="news-grid">
                    <?php foreach ($latestNews as $news): ?>
                    <article class="news-card">
                        <div class="news-image">
                            <img src="<?php echo $news['resim']; ?>" alt="<?php echo $news['baslik']; ?>">
                        </div>
                        <div class="news-content">
                            <span class="category"><?php echo $news['kategori_adi']; ?></span>
                            <h3><a href="<?php echo APP_URL; ?>/haber/<?php echo $news['slug']; ?>"><?php echo $news['baslik']; ?></a></h3>
                            <p><?php echo truncate($news['ozet'], 100); ?></p>
                            <div class="news-meta">
                                <span><i class="fas fa-user"></i> <?php echo $news['yazar_adi']; ?></span>
                                <span><i class="fas fa-clock"></i> <?php echo formatDate($news['created_at']); ?></span>
                            </div>
                        </div>
                    </article>
                    <?php endforeach; ?>
                </div>
            </section>

            <!-- Popular News -->
            <section class="popular-news">
                <h2>Popüler Haberler</h2>
                <div class="news-list">
                    <?php foreach ($popularNews as $news): ?>
                    <article class="news-item">
                        <div class="news-image">
                            <img src="<?php echo $news['resim']; ?>" alt="<?php echo $news['baslik']; ?>">
                        </div>
                        <div class="news-content">
                            <h3><a href="<?php echo APP_URL; ?>/haber/<?php echo $news['slug']; ?>"><?php echo $news['baslik']; ?></a></h3>
                            <div class="news-meta">
                                <span><i class="fas fa-eye"></i> <?php echo $news['goruntulenme']; ?></span>
                                <span><i class="fas fa-clock"></i> <?php echo formatDate($news['created_at']); ?></span>
                            </div>
                        </div>
                    </article>
                    <?php endforeach; ?>
                </div>
            </section>
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
                <p>&copy; <?php echo date('Y'); ?> <?php echo $pageTitle; ?>. Tüm hakları saklıdır.</p>
            </div>
        </div>
    </footer>

    <script src="assets/js/main.js"></script>
</body>
</html> 