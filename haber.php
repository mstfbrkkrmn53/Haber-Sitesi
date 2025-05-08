<?php
require_once 'init.php';

// Haber slug'ını al
$slug = $_GET['slug'] ?? '';

if (empty($slug)) {
    redirect(APP_URL);
}

// Haberi getir
$news = $news->getBySlug($slug);

if (!$news) {
    redirect(APP_URL . '/404.php');
}

// Görüntülenme sayısını artır
$news->incrementViewCount($news['id']);

// İlgili haberleri getir
$relatedNews = $news->getRelatedNews($news['id'], $news['kategori_id'], 3);

// Yorumları getir
$comments = $comment->getByNewsId($news['id']);

// SEO meta etiketlerini oluştur
$metaTags = $seo->generateMetaTags('news', [
    'title' => $news['baslik'],
    'description' => $news['ozet'],
    'keywords' => $news['etiketler'],
    'image' => $news['resim'],
    'published_time' => $news['created_at'],
    'modified_time' => $news['updated_at']
]);

// Yapılandırılmış veriyi oluştur
$structuredData = $seo->generateStructuredData('news', [
    'title' => $news['baslik'],
    'description' => $news['ozet'],
    'image' => $news['resim'],
    'datePublished' => $news['created_at'],
    'dateModified' => $news['updated_at'],
    'author' => $news['yazar_adi'],
    'publisher' => [
        'name' => $siteSettings['site_title'],
        'logo' => $siteSettings['site_logo']
    ]
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
                           class="<?php echo $cat['id'] === $news['kategori_id'] ? 'active' : ''; ?>">
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
            <article class="news-detail">
                <header class="news-header">
                    <h1><?php echo $news['baslik']; ?></h1>
                    <div class="news-meta">
                        <span><i class="fas fa-user"></i> <?php echo $news['yazar_adi']; ?></span>
                        <span><i class="fas fa-folder"></i> <?php echo $news['kategori_adi']; ?></span>
                        <span><i class="fas fa-clock"></i> <?php echo formatDate($news['created_at']); ?></span>
                        <span><i class="fas fa-eye"></i> <?php echo $news['goruntulenme']; ?> görüntülenme</span>
                    </div>
                </header>

                <?php if ($news['resim']): ?>
                <div class="news-image">
                    <img src="<?php echo $news['resim']; ?>" alt="<?php echo $news['baslik']; ?>">
                </div>
                <?php endif; ?>

                <div class="news-content">
                    <?php echo $news['icerik']; ?>
                </div>

                <?php if (!empty($news['etiketler'])): ?>
                <div class="news-tags">
                    <h3>Etiketler</h3>
                    <div class="tags">
                        <?php foreach (explode(',', $news['etiketler']) as $tag): ?>
                        <a href="<?php echo APP_URL; ?>/etiket/<?php echo generateSlug($tag); ?>" class="tag">
                            <?php echo trim($tag); ?>
                        </a>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

                <div class="news-share">
                    <h3>Paylaş</h3>
                    <div class="share-buttons">
                        <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode(APP_URL . '/haber/' . $news['slug']); ?>" 
                           target="_blank" class="share-button facebook">
                            <i class="fab fa-facebook"></i>
                        </a>
                        <a href="https://twitter.com/intent/tweet?url=<?php echo urlencode(APP_URL . '/haber/' . $news['slug']); ?>&text=<?php echo urlencode($news['baslik']); ?>" 
                           target="_blank" class="share-button twitter">
                            <i class="fab fa-twitter"></i>
                        </a>
                        <a href="https://wa.me/?text=<?php echo urlencode($news['baslik'] . ' ' . APP_URL . '/haber/' . $news['slug']); ?>" 
                           target="_blank" class="share-button whatsapp">
                            <i class="fab fa-whatsapp"></i>
                        </a>
                        <a href="https://t.me/share/url?url=<?php echo urlencode(APP_URL . '/haber/' . $news['slug']); ?>&text=<?php echo urlencode($news['baslik']); ?>" 
                           target="_blank" class="share-button telegram">
                            <i class="fab fa-telegram"></i>
                        </a>
                    </div>
                </div>
            </article>

            <!-- İlgili Haberler -->
            <?php if (!empty($relatedNews)): ?>
            <section class="related-news">
                <h2>İlgili Haberler</h2>
                <div class="news-grid">
                    <?php foreach ($relatedNews as $related): ?>
                    <article class="news-card">
                        <div class="news-image">
                            <img src="<?php echo $related['resim']; ?>" alt="<?php echo $related['baslik']; ?>">
                        </div>
                        <div class="news-content">
                            <span class="category"><?php echo $related['kategori_adi']; ?></span>
                            <h3><a href="<?php echo APP_URL; ?>/haber/<?php echo $related['slug']; ?>"><?php echo $related['baslik']; ?></a></h3>
                            <p><?php echo truncate($related['ozet'], 100); ?></p>
                            <div class="news-meta">
                                <span><i class="fas fa-clock"></i> <?php echo formatDate($related['created_at']); ?></span>
                            </div>
                        </div>
                    </article>
                    <?php endforeach; ?>
                </div>
            </section>
            <?php endif; ?>

            <!-- Yorumlar -->
            <section class="comments">
                <h2>Yorumlar (<?php echo count($comments); ?>)</h2>
                
                <?php if (isLoggedIn()): ?>
                <form class="comment-form" action="<?php echo APP_URL; ?>/api/comments/add.php" method="POST">
                    <input type="hidden" name="news_id" value="<?php echo $news['id']; ?>">
                    <input type="hidden" name="csrf_token" value="<?php echo getCsrfToken(); ?>">
                    <div class="form-group">
                        <textarea name="content" required placeholder="Yorumunuzu yazın..."></textarea>
                    </div>
                    <button type="submit" class="btn">Yorum Yap</button>
                </form>
                <?php else: ?>
                <div class="login-to-comment">
                    <p>Yorum yapmak için <a href="<?php echo APP_URL; ?>/login.php">giriş yapın</a> veya <a href="<?php echo APP_URL; ?>/register.php">kayıt olun</a>.</p>
                </div>
                <?php endif; ?>

                <div class="comments-list">
                    <?php if (empty($comments)): ?>
                    <p class="no-comments">Henüz yorum yapılmamış. İlk yorumu siz yapın!</p>
                    <?php else: ?>
                    <?php foreach ($comments as $comment): ?>
                    <div class="comment">
                        <div class="comment-header">
                            <div class="comment-author">
                                <img src="<?php echo $comment['avatar'] ?? 'assets/images/default-avatar.png'; ?>" alt="<?php echo $comment['ad_soyad']; ?>">
                                <div class="comment-meta">
                                    <h4><?php echo $comment['ad_soyad']; ?></h4>
                                    <span><?php echo formatDate($comment['created_at']); ?></span>
                                </div>
                            </div>
                            <?php if (isLoggedIn() && ($_SESSION['user_id'] === $comment['user_id'] || $_SESSION['user_role'] === 'admin')): ?>
                            <div class="comment-actions">
                                <button class="btn-edit" onclick="editComment(<?php echo $comment['id']; ?>)">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn-delete" onclick="deleteComment(<?php echo $comment['id']; ?>)">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                            <?php endif; ?>
                        </div>
                        <div class="comment-content">
                            <?php echo nl2br($comment['icerik']); ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    <?php endif; ?>
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
                <p>&copy; <?php echo date('Y'); ?> <?php echo $siteSettings['site_title']; ?>. Tüm hakları saklıdır.</p>
            </div>
        </div>
    </footer>

    <script src="assets/js/main.js"></script>
    <script>
    // Yorum düzenleme
    function editComment(commentId) {
        const comment = document.querySelector(`.comment[data-id="${commentId}"]`);
        const content = comment.querySelector('.comment-content').textContent;
        const form = document.createElement('form');
        form.className = 'comment-edit-form';
        form.innerHTML = `
            <textarea required>${content}</textarea>
            <div class="form-actions">
                <button type="submit" class="btn">Kaydet</button>
                <button type="button" class="btn btn-outline" onclick="cancelEdit(${commentId})">İptal</button>
            </div>
        `;
        form.onsubmit = async (e) => {
            e.preventDefault();
            const newContent = form.querySelector('textarea').value;
            try {
                const response = await fetch(`${APP_URL}/api/comments/edit.php`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        comment_id: commentId,
                        content: newContent,
                        csrf_token: '<?php echo getCsrfToken(); ?>'
                    })
                });
                const data = await response.json();
                if (data.success) {
                    comment.querySelector('.comment-content').textContent = newContent;
                    showNotification('Yorum güncellendi', 'success');
                } else {
                    showNotification(data.message, 'error');
                }
            } catch (error) {
                showNotification('Bir hata oluştu', 'error');
            }
        };
        comment.querySelector('.comment-content').replaceWith(form);
    }

    // Yorum silme
    async function deleteComment(commentId) {
        if (!confirm('Bu yorumu silmek istediğinizden emin misiniz?')) {
            return;
        }
        try {
            const response = await fetch(`${APP_URL}/api/comments/delete.php`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    comment_id: commentId,
                    csrf_token: '<?php echo getCsrfToken(); ?>'
                })
            });
            const data = await response.json();
            if (data.success) {
                const comment = document.querySelector(`.comment[data-id="${commentId}"]`);
                comment.remove();
                showNotification('Yorum silindi', 'success');
            } else {
                showNotification(data.message, 'error');
            }
        } catch (error) {
            showNotification('Bir hata oluştu', 'error');
        }
    }

    // Düzenlemeyi iptal et
    function cancelEdit(commentId) {
        const comment = document.querySelector(`.comment[data-id="${commentId}"]`);
        const form = comment.querySelector('.comment-edit-form');
        const content = form.querySelector('textarea').value;
        const contentDiv = document.createElement('div');
        contentDiv.className = 'comment-content';
        contentDiv.textContent = content;
        form.replaceWith(contentDiv);
    }
    </script>
</body>
</html> 