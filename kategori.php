<?php
require_once 'init.php';

// Kategori slug'ını al
$slug = $_GET['slug'] ?? '';

if (empty($slug)) {
    redirect(APP_URL);
}

// Kategoriyi getir
$category = $category->getBySlug($slug);

if (!$category) {
    redirect(APP_URL . '/404.php');
}

// Sayfalama
$page = isset($_GET['sayfa']) ? (int)$_GET['sayfa'] : 1;
$perPage = 12;
$offset = ($page - 1) * $perPage;

// Kategoriye ait haberleri getir
$news = $news->getByCategory($category['id'], $perPage, $offset);
$totalNews = $news->getTotalByCategory($category['id']);
$totalPages = ceil($totalNews / $perPage);

// SEO meta etiketlerini oluştur
$metaTags = $seo->generateMetaTags('category', [
    'title' => $category['ad'],
    'description' => $category['aciklama'],
    'keywords' => $category['anahtar_kelimeler']
]);

// Yapılandırılmış veriyi oluştur
$structuredData = $seo->generateStructuredData('category', [
    'name' => $category['ad'],
    'description' => $category['aciklama'],
    'url' => APP_URL . '/kategori/' . $category['slug']
]);
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php echo $metaTags; ?>
    <script type="application/ld+json"><?php echo json_encode($structuredData); ?></script>
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
                        <a href="<?php echo APP_URL; ?>/kategori/<?php echo $cat['slug']; ?>" 
                           class="<?php echo $cat['id'] === $category['id'] ? 'active' : ''; ?>">
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
            <section class="category-header">
                <h1><?php echo $category['ad']; ?></h1>
                <?php if (!empty($category['aciklama'])): ?>
                <p class="category-description"><?php echo $category['aciklama']; ?></p>
                <?php endif; ?>
            </section>

            <section class="category-news">
                <div class="news-grid">
                    <?php foreach ($news as $item): ?>
                    <article class="news-card">
                        <div class="news-image">
                            <img src="<?php echo $item['resim']; ?>" alt="<?php echo $item['baslik']; ?>">
                        </div>
                        <div class="news-content">
                            <span class="category"><?php echo $category['ad']; ?></span>
                            <h3><a href="<?php echo APP_URL; ?>/haber/<?php echo $item['slug']; ?>"><?php echo $item['baslik']; ?></a></h3>
                            <p><?php echo truncate($item['ozet'], 100); ?></p>
                            <div class="news-meta">
                                <span><i class="fas fa-user"></i> <?php echo $item['yazar_adi']; ?></span>
                                <span><i class="fas fa-clock"></i> <?php echo formatDate($item['created_at']); ?></span>
                                <span><i class="fas fa-eye"></i> <?php echo $item['goruntulenme']; ?></span>
                            </div>
                        </div>
                    </article>
                    <?php endforeach; ?>
                </div>

                <?php if ($totalPages > 1): ?>
                <div class="pagination">
                    <?php if ($page > 1): ?>
                    <a href="?sayfa=<?php echo $page - 1; ?>" class="btn btn-outline">
                        <i class="fas fa-chevron-left"></i> Önceki
                    </a>
                    <?php endif; ?>

                    <?php
                    $start = max(1, $page - 2);
                    $end = min($totalPages, $page + 2);

                    for ($i = $start; $i <= $end; $i++):
                    ?>
                    <a href="?sayfa=<?php echo $i; ?>" 
                       class="btn <?php echo $i === $page ? '' : 'btn-outline'; ?>">
                        <?php echo $i; ?>
                    </a>
                    <?php endfor; ?>

                    <?php if ($page < $totalPages): ?>
                    <a href="?sayfa=<?php echo $page + 1; ?>" class="btn btn-outline">
                        Sonraki <i class="fas fa-chevron-right"></i>
                    </a>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
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
                <p>&copy; <?php echo date('Y'); ?> <?php echo $siteSettings['site_title']; ?>. Tüm hakları saklıdır.</p>
            </div>
        </div>
    </footer>

    <script src="assets/js/main.js"></script>
</body>
</html> 