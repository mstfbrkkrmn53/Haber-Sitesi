<?php
require_once '../db.php';
require_once '../functions.php';

if (!giris_kontrol() || !rol_kontrol(['admin', 'editör'])) {
    header('Location: login.php');
    exit;
}

// Kategori ekle
$mesaj = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ad'], $_POST['csrf_token'])) {
    if (!csrf_token_kontrol($_POST['csrf_token'])) {
        $mesaj = 'Güvenlik hatası!';
    } else {
        $ad = $conn->real_escape_string($_POST['ad']);
        $conn->query("INSERT INTO kategoriler (ad) VALUES ('$ad')");
        $mesaj = 'Kategori eklendi!';
    }
}

// Kategori sil
if (isset($_GET['sil'])) {
    $id = intval($_GET['sil']);
    $conn->query("DELETE FROM kategoriler WHERE id = $id");
    header('Location: kategoriler.php');
    exit;
}

// Kategoriler
$kategoriler = $conn->query("SELECT * FROM kategoriler ORDER BY ad ASC");
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Kategoriler - Kontrol Paneli</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="../assets/bootstrap.min.css" rel="stylesheet">
    <link href="style.css" rel="stylesheet">
</head>
<body>
<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <div class="col-md-3 col-lg-2 sidebar">
            <h4 class="text-center mb-4">Kontrol Paneli</h4>
            <nav class="nav flex-column">
                <a class="nav-link" href="panel.php">
                    <i class="fas fa-home"></i> Ana Sayfa
                </a>
                <a class="nav-link" href="haberler.php">
                    <i class="fas fa-newspaper"></i> Haberler
                </a>
                <a class="nav-link active" href="kategoriler.php">
                    <i class="fas fa-folder"></i> Kategoriler
                </a>
                <a class="nav-link" href="yorumlar.php">
                    <i class="fas fa-comments"></i> Yorumlar
                </a>
                <a class="nav-link" href="kullanicilar.php">
                    <i class="fas fa-users"></i> Kullanıcılar
                </a>
                <a class="nav-link" href="sifre_degistir.php">
                    <i class="fas fa-key"></i> Şifre Değiştir
                </a>
                <a class="nav-link" href="logout.php">
                    <i class="fas fa-sign-out-alt"></i> Çıkış Yap
                </a>
            </nav>
        </div>

        <!-- Ana İçerik -->
        <div class="col-md-9 col-lg-10 p-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2><i class="fas fa-folder"></i> Kategoriler</h2>
            </div>

            <?php if($mesaj): ?>
                <div class="alert alert-info alert-dismissible fade show" role="alert">
                    <i class="fas fa-info-circle"></i> <?php echo $mesaj; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Kapat"></button>
                </div>
            <?php endif; ?>

            <div class="row">
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0"><i class="fas fa-plus"></i> Yeni Kategori Ekle</h5>
                        </div>
                        <div class="card-body">
                            <form method="post">
                                <input type="hidden" name="csrf_token" value="<?php echo csrf_token_uret(); ?>">
                                <div class="mb-3">
                                    <label class="form-label">Kategori Adı</label>
                                    <input type="text" name="ad" class="form-control" required>
                                </div>
                                <button type="submit" class="btn btn-success">
                                    <i class="fas fa-plus"></i> Ekle
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0"><i class="fas fa-list"></i> Kategori Listesi</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Ad</th>
                                            <th>İşlemler</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    <?php while($kat = $kategoriler->fetch_assoc()): ?>
                                        <tr>
                                            <td><?php echo $kat['id']; ?></td>
                                            <td><?php echo e($kat['ad']); ?></td>
                                            <td>
                                                <a href="?sil=<?php echo $kat['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Bu kategoriyi silmek istediğinizden emin misiniz?')" title="Sil">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Tema Değiştirme Butonu -->
<button class="theme-toggle" onclick="toggleTheme()">🌓</button>

<!-- Scripts -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/js/all.min.js"></script>
<script src="../assets/bootstrap.bundle.min.js"></script>
<script>
    function toggleTheme() {
        const html = document.documentElement;
        const currentTheme = html.getAttribute('data-theme');
        const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
        html.setAttribute('data-theme', newTheme);
        localStorage.setItem('theme', newTheme);
    }

    // Kaydedilmiş tema tercihini kontrol et
    const savedTheme = localStorage.getItem('theme');
    if (savedTheme) {
        document.documentElement.setAttribute('data-theme', savedTheme);
    }
</script>
</body>
</html> 