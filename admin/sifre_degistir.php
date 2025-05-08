<?php
require_once '../db.php';
require_once '../functions.php';

if (!giris_kontrol()) {
    header('Location: login.php');
    exit;
}

$mesaj = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['eski'], $_POST['yeni'], $_POST['yeni2'], $_POST['csrf_token'])) {
    if (!csrf_token_kontrol($_POST['csrf_token'])) {
        $mesaj = 'Güvenlik hatası!';
    } else {
        $id = $_SESSION['kullanici_id'];
        $eski = $_POST['eski'];
        $yeni = $_POST['yeni'];
        $yeni2 = $_POST['yeni2'];
        $user = $conn->query("SELECT * FROM kullanicilar WHERE id = $id")->fetch_assoc();
        if (!password_verify($eski, $user['sifre'])) {
            $mesaj = 'Eski şifre yanlış!';
        } elseif ($yeni !== $yeni2) {
            $mesaj = 'Yeni şifreler eşleşmiyor!';
        } else {
            $hash = password_hash($yeni, PASSWORD_BCRYPT);
            $conn->query("UPDATE kullanicilar SET sifre = '$hash' WHERE id = $id");
            $mesaj = 'Şifreniz başarıyla değiştirildi!';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Şifre Değiştir - Kontrol Paneli</title>
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
                <a class="nav-link" href="yorumlar.php">
                    <i class="fas fa-comments"></i> Yorumlar
                </a>
                <a class="nav-link" href="kullanicilar.php">
                    <i class="fas fa-users"></i> Kullanıcılar
                </a>
                <a class="nav-link active" href="sifre_degistir.php">
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
                <h2><i class="fas fa-key"></i> Şifre Değiştir</h2>
            </div>

            <?php if(isset($mesaj)): ?>
                <div class="alert alert-<?php echo $mesaj_tipi; ?> alert-dismissible fade show" role="alert">
                    <i class="fas fa-<?php echo $mesaj_tipi == 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i>
                    <?php echo $mesaj; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Kapat"></button>
                </div>
            <?php endif; ?>

            <div class="card">
                <div class="card-body">
                    <form method="post">
                        <div class="mb-3">
                            <label for="eski_sifre" class="form-label">
                                <i class="fas fa-lock"></i> Mevcut Şifre
                            </label>
                            <input type="password" class="form-control" id="eski_sifre" name="eski_sifre" required>
                        </div>
                        <div class="mb-3">
                            <label for="yeni_sifre" class="form-label">
                                <i class="fas fa-key"></i> Yeni Şifre
                            </label>
                            <input type="password" class="form-control" id="yeni_sifre" name="yeni_sifre" required>
                        </div>
                        <div class="mb-3">
                            <label for="yeni_sifre_tekrar" class="form-label">
                                <i class="fas fa-key"></i> Yeni Şifre (Tekrar)
                            </label>
                            <input type="password" class="form-control" id="yeni_sifre_tekrar" name="yeni_sifre_tekrar" required>
                        </div>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Şifreyi Güncelle
                        </button>
                    </form>
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