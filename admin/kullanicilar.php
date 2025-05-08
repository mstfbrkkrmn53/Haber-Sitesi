<?php
require_once '../db.php';
require_once '../functions.php';

if (!giris_kontrol() || !rol_kontrol(['admin'])) {
    header('Location: login.php');
    exit;
}

// Kullanıcı sil
if (isset($_GET['sil'])) {
    $id = intval($_GET['sil']);
    if ($id != $_SESSION['kullanici_id']) { // Kendi hesabını silemesin
        $conn->query("DELETE FROM kullanicilar WHERE id = $id");
    }
    header('Location: kullanicilar.php');
    exit;
}

// Rol değiştir
if (isset($_GET['rol']) && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $rol_id = intval($_GET['rol']);
    $conn->query("UPDATE kullanicilar SET rol_id = $rol_id WHERE id = $id");
    header('Location: kullanicilar.php');
    exit;
}

// Kullanıcılar ve roller
$kullanicilar = $conn->query("SELECT k.*, r.ad as rol_ad FROM kullanicilar k JOIN roller r ON k.rol_id = r.id ORDER BY k.kayit_tarihi DESC");
$roller = $conn->query("SELECT * FROM roller");
$roller_arr = [];
while($r = $roller->fetch_assoc()) $roller_arr[$r['id']] = $r['ad'];
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Kullanıcılar - Kontrol Paneli</title>
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
                <a class="nav-link active" href="kullanicilar.php">
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
                <h2><i class="fas fa-users"></i> Kullanıcılar</h2>
            </div>

            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Kullanıcı Adı</th>
                                    <th>Ad Soyad</th>
                                    <th>E-posta</th>
                                    <th>Rol</th>
                                    <th>Kayıt Tarihi</th>
                                    <th>Durum</th>
                                    <th>İşlemler</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php while($kullanici = $kullanicilar->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo $kullanici['id']; ?></td>
                                    <td><?php echo e($kullanici['kullanici_adi']); ?></td>
                                    <td><?php echo e($kullanici['ad_soyad']); ?></td>
                                    <td><?php echo e($kullanici['email']); ?></td>
                                    <td>
                                        <form method="post" class="d-inline">
                                            <input type="hidden" name="kullanici_id" value="<?php echo $kullanici['id']; ?>">
                                            <select name="rol" class="form-select form-select-sm" onchange="this.form.submit()">
                                                <option value="admin" <?php echo $kullanici['rol'] == 'admin' ? 'selected' : ''; ?>>Admin</option>
                                                <option value="editor" <?php echo $kullanici['rol'] == 'editor' ? 'selected' : ''; ?>>Editör</option>
                                                <option value="user" <?php echo $kullanici['rol'] == 'user' ? 'selected' : ''; ?>>Kullanıcı</option>
                                            </select>
                                        </form>
                                    </td>
                                    <td><?php echo $kullanici['kayit_tarihi']; ?></td>
                                    <td>
                                        <?php if($kullanici['aktif']): ?>
                                            <span class="badge bg-success"><i class="fas fa-check"></i> Aktif</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger"><i class="fas fa-times"></i> Pasif</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if($kullanici['id'] != $_SESSION['kullanici_id']): ?>
                                            <a href="?sil=<?php echo $kullanici['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Bu kullanıcıyı silmek istediğinizden emin misiniz?')" title="Sil">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        <?php endif; ?>
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