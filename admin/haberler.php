<?php
require_once '../db.php';
require_once '../functions.php';

if (!giris_kontrol() || !rol_kontrol(['admin', 'editör'])) {
    header('Location: login.php');
    exit;
}

// Haber sil
if (isset($_GET['sil'])) {
    $id = intval($_GET['sil']);
    $conn->query("DELETE FROM haberler WHERE id = $id");
    header('Location: haberler.php');
    exit;
}

// Haberler
$haberler = $conn->query("SELECT h.*, k.ad as kategori_ad, u.kullanici as yazar FROM haberler h JOIN kategoriler k ON h.kategori_id = k.id JOIN kullanicilar u ON h.yazar_id = u.id ORDER BY h.tarih DESC");
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Haberler - Kontrol Paneli</title>
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
                <a class="nav-link active" href="haberler.php">
                    <i class="fas fa-newspaper"></i> Haberler
                </a>
                <a class="nav-link" href="kategoriler.php">
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
                <h2><i class="fas fa-newspaper"></i> Haberler</h2>
                <a href="haber_ekle.php" class="btn btn-success">
                    <i class="fas fa-plus"></i> Yeni Haber Ekle
                </a>
            </div>

            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Başlık</th>
                                    <th>Kategori</th>
                                    <th>Yazar</th>
                                    <th>Tarih</th>
                                    <th>İşlemler</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php while($haber = $haberler->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo $haber['id']; ?></td>
                                    <td><?php echo e($haber['baslik']); ?></td>
                                    <td><?php echo e($haber['kategori_ad']); ?></td>
                                    <td><?php echo e($haber['yazar']); ?></td>
                                    <td><?php echo $haber['tarih']; ?></td>
                                    <td>
                                        <div class="btn-group">
                                            <a href="haber_duzenle.php?id=<?php echo $haber['id']; ?>" class="btn btn-sm btn-warning" title="Düzenle">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="?sil=<?php echo $haber['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Bu haberi silmek istediğinizden emin misiniz?')" title="Sil">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </div>
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