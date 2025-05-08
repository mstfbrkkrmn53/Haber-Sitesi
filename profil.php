<?php
require_once 'init.php';

// Kullanıcı giriş yapmamışsa giriş sayfasına yönlendir
if (!isLoggedIn()) {
    redirect(APP_URL . '/login.php');
}

$error = '';
$success = '';

// Kullanıcı bilgilerini getir
$userData = $user->getById($_SESSION['user_id']);

// Form gönderildi mi kontrol et
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $surname = $_POST['surname'] ?? '';
    $email = $_POST['email'] ?? '';
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $new_password_confirm = $_POST['new_password_confirm'] ?? '';

    // Profil güncelleme
    if (isset($_POST['update_profile'])) {
        $result = $user->updateProfile($_SESSION['user_id'], [
            'name' => $name,
            'surname' => $surname,
            'email' => $email
        ]);

        if ($result['success']) {
            $success = 'Profil bilgileriniz güncellendi.';
            $userData = $user->getById($_SESSION['user_id']); // Güncel bilgileri al
        } else {
            $error = $result['message'];
        }
    }

    // Şifre değiştirme
    if (isset($_POST['change_password'])) {
        if (empty($current_password)) {
            $error = 'Mevcut şifrenizi girin.';
        } elseif (empty($new_password)) {
            $error = 'Yeni şifrenizi girin.';
        } elseif ($new_password !== $new_password_confirm) {
            $error = 'Yeni şifreler eşleşmiyor.';
        } else {
            $result = $user->changePassword($_SESSION['user_id'], $current_password, $new_password);
            
            if ($result['success']) {
                $success = 'Şifreniz başarıyla değiştirildi.';
            } else {
                $error = $result['message'];
            }
        }
    }
}

// Kullanıcının yorumlarını getir
$comments = $comment->getByUserId($_SESSION['user_id']);

// SEO meta etiketlerini oluştur
$metaTags = $seo->generateMetaTags('profile', [
    'title' => 'Profilim',
    'description' => 'Profil bilgilerinizi görüntüleyin ve düzenleyin',
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
            <div class="user-menu">
                <a href="<?php echo APP_URL; ?>/profil.php" class="btn active">Profilim</a>
                <a href="<?php echo APP_URL; ?>/logout.php" class="btn btn-outline">Çıkış</a>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main>
        <div class="container">
            <div class="profile-container">
                <div class="profile-sidebar">
                    <div class="profile-info">
                        <div class="profile-avatar">
                            <img src="<?php echo $userData['avatar'] ?? 'assets/images/default-avatar.png'; ?>" 
                                 alt="<?php echo $userData['ad_soyad']; ?>">
                        </div>
                        <h2><?php echo $userData['ad_soyad']; ?></h2>
                        <p class="username">@<?php echo $userData['username']; ?></p>
                        <p class="join-date">
                            <i class="fas fa-calendar-alt"></i>
                            <?php echo formatDate($userData['created_at']); ?> tarihinde katıldı
                        </p>
                    </div>
                    <nav class="profile-nav">
                        <a href="#profile" class="active">
                            <i class="fas fa-user"></i> Profil Bilgileri
                        </a>
                        <a href="#password">
                            <i class="fas fa-lock"></i> Şifre Değiştir
                        </a>
                        <a href="#comments">
                            <i class="fas fa-comments"></i> Yorumlarım
                        </a>
                    </nav>
                </div>

                <div class="profile-content">
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

                    <!-- Profil Bilgileri -->
                    <section id="profile" class="profile-section">
                        <h2>Profil Bilgileri</h2>
                        <form method="POST" action="" class="profile-form">
                            <input type="hidden" name="csrf_token" value="<?php echo getCsrfToken(); ?>">
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="name">Ad</label>
                                    <input type="text" id="name" name="name" required 
                                           value="<?php echo htmlspecialchars($userData['name']); ?>">
                                </div>

                                <div class="form-group">
                                    <label for="surname">Soyad</label>
                                    <input type="text" id="surname" name="surname" required 
                                           value="<?php echo htmlspecialchars($userData['surname']); ?>">
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="email">E-posta</label>
                                <input type="email" id="email" name="email" required 
                                       value="<?php echo htmlspecialchars($userData['email']); ?>">
                            </div>

                            <button type="submit" name="update_profile" class="btn">Bilgileri Güncelle</button>
                        </form>
                    </section>

                    <!-- Şifre Değiştir -->
                    <section id="password" class="profile-section">
                        <h2>Şifre Değiştir</h2>
                        <form method="POST" action="" class="profile-form">
                            <input type="hidden" name="csrf_token" value="<?php echo getCsrfToken(); ?>">
                            
                            <div class="form-group">
                                <label for="current_password">Mevcut Şifre</label>
                                <div class="input-group">
                                    <input type="password" id="current_password" name="current_password" required>
                                    <button type="button" class="toggle-password" onclick="togglePassword('current_password')">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="new_password">Yeni Şifre</label>
                                <div class="input-group">
                                    <input type="password" id="new_password" name="new_password" required>
                                    <button type="button" class="toggle-password" onclick="togglePassword('new_password')">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                                <small class="form-text">En az 8 karakter, bir büyük harf, bir küçük harf ve bir rakam içermelidir.</small>
                            </div>

                            <div class="form-group">
                                <label for="new_password_confirm">Yeni Şifre Tekrar</label>
                                <div class="input-group">
                                    <input type="password" id="new_password_confirm" name="new_password_confirm" required>
                                    <button type="button" class="toggle-password" onclick="togglePassword('new_password_confirm')">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                            </div>

                            <button type="submit" name="change_password" class="btn">Şifreyi Değiştir</button>
                        </form>
                    </section>

                    <!-- Yorumlar -->
                    <section id="comments" class="profile-section">
                        <h2>Yorumlarım</h2>
                        <?php if (empty($comments)): ?>
                        <p class="no-comments">Henüz yorum yapmamışsınız.</p>
                        <?php else: ?>
                        <div class="comments-list">
                            <?php foreach ($comments as $comment): ?>
                            <div class="comment">
                                <div class="comment-header">
                                    <div class="comment-meta">
                                        <a href="<?php echo APP_URL; ?>/haber/<?php echo $comment['haber_slug']; ?>" class="news-title">
                                            <?php echo $comment['haber_baslik']; ?>
                                        </a>
                                        <span class="comment-date">
                                            <i class="fas fa-clock"></i>
                                            <?php echo formatDate($comment['created_at']); ?>
                                        </span>
                                    </div>
                                    <div class="comment-actions">
                                        <button class="btn-edit" onclick="editComment(<?php echo $comment['id']; ?>)">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn-delete" onclick="deleteComment(<?php echo $comment['id']; ?>)">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="comment-content">
                                    <?php echo nl2br($comment['icerik']); ?>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>
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
    <script>
    // Şifre göster/gizle
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

    // Profil navigasyonu
    document.querySelectorAll('.profile-nav a').forEach(link => {
        link.addEventListener('click', (e) => {
            e.preventDefault();
            const targetId = link.getAttribute('href').substring(1);
            
            // Aktif linki güncelle
            document.querySelectorAll('.profile-nav a').forEach(l => l.classList.remove('active'));
            link.classList.add('active');
            
            // İlgili bölümü göster
            document.querySelectorAll('.profile-section').forEach(section => {
                section.style.display = section.id === targetId ? 'block' : 'none';
            });
        });
    });
    </script>
</body>
</html> 