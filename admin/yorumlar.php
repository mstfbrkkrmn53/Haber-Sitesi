<?php
require_once '../db.php';
require_once '../functions.php';

if (!giris_kontrol() || !rol_kontrol(['admin', 'editör'])) {
    header('Location: login.php');
    exit;
}

// Yorum sil
if (isset($_GET['sil'])) {
    $id = intval($_GET['sil']);
    $conn->query("DELETE FROM yorumlar WHERE id = $id");
    header('Location: yorumlar.php');
    exit;
}

// Yorumları getir
$yorumlar = $conn->query("
    SELECT y.*, h.baslik as haber_baslik, k.kullanici_adi 
    FROM yorumlar y 
    LEFT JOIN haberler h ON y.haber_id = h.id 
    LEFT JOIN kullanicilar k ON y.kullanici_id = k.id 
    ORDER BY y.tarih DESC
");
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Yorumlar - Kontrol Paneli</title>
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
                <a class="nav-link" href="kategoriler.php">
                    <i class="fas fa-folder"></i> Kategoriler
                </a>
                <a class="nav-link active" href="yorumlar.php">
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
                <h2><i class="fas fa-comments"></i> Yorumlar</h2>
            </div>

            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Haber</th>
                                    <th>Kullanıcı</th>
                                    <th>Yorum</th>
                                    <th>Tarih</th>
                                    <th>İşlemler</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php while($yorum = $yorumlar->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo $yorum['id']; ?></td>
                                    <td>
                                        <a href="../haber.php?id=<?php echo $yorum['haber_id']; ?>" target="_blank">
                                            <?php echo e($yorum['haber_baslik']); ?>
                                        </a>
                                    </td>
                                    <td><?php echo e($yorum['kullanici_adi']); ?></td>
                                    <td><?php echo e($yorum['yorum']); ?></td>
                                    <td><?php echo $yorum['tarih']; ?></td>
                                    <td>
                                        <div class="btn-group">
                                            <a href="?sil=<?php echo $yorum['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Bu yorumu silmek istediğinizden emin misiniz?')" title="Sil">
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