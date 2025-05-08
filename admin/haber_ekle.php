<?php
require_once '../db.php';
require_once '../functions.php';

if (!giris_kontrol() || !rol_kontrol(['admin', 'editör', 'yazar'])) {
    header('Location: login.php');
    exit;
}

$mesaj = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['baslik'], $_POST['icerik'], $_POST['kategori_id'], $_POST['csrf_token'])) {
    if (!csrf_token_kontrol($_POST['csrf_token'])) {
        $mesaj = 'Güvenlik hatası!';
    } else {
        $baslik = $conn->real_escape_string($_POST['baslik']);
        $icerik = $conn->real_escape_string($_POST['icerik']);
        $kategori_id = intval($_POST['kategori_id']);
        $one_cikan = isset($_POST['one_cikan']) ? 1 : 0;
        $yazar_id = $_SESSION['kullanici_id'];
        $gorsel = '';
        if (isset($_FILES['gorsel']) && $_FILES['gorsel']['error'] == 0) {
            $uzanti = strtolower(pathinfo($_FILES['gorsel']['name'], PATHINFO_EXTENSION));
            if (in_array($uzanti, ['jpg', 'jpeg', 'png', 'gif'])) {
                $gorsel = uniqid().'.'.$uzanti;
                move_uploaded_file($_FILES['gorsel']['tmp_name'], '../assets/uploads/'.$gorsel);
            }
        }
        $sql = "INSERT INTO haberler (baslik, icerik, tarih, kategori_id, gorsel, one_cikan, yazar_id) VALUES ('$baslik', '$icerik', NOW(), $kategori_id, '$gorsel', $one_cikan, $yazar_id)";
        if ($conn->query($sql)) {
            $mesaj = 'Haber başarıyla eklendi!';
        } else {
            $mesaj = 'Hata: ' . $conn->error;
        }
    }
}

// Kategoriler
$kategoriler = $conn->query("SELECT * FROM kategoriler ORDER BY ad ASC");
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Haber Ekle - Haber Sitesi</title>
    <link href="../assets/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-4">
    <a href="haberler.php" class="btn btn-secondary mb-3">← Haberler</a>
    <h2>Yeni Haber Ekle</h2>
    <?php if($mesaj): ?>
        <div class="alert alert-info"><?php echo $mesaj; ?></div>
    <?php endif; ?>
    <form method="post" enctype="multipart/form-data">
        <input type="hidden" name="csrf_token" value="<?php echo csrf_token_uret(); ?>">
        <div class="mb-3">
            <label>Başlık</label>
            <input type="text" name="baslik" class="form-control" required>
        </div>
        <div class="mb-3">
            <label>İçerik</label>
            <textarea name="icerik" class="form-control" rows="5" required></textarea>
        </div>
        <div class="mb-3">
            <label>Kategori</label>
            <select name="kategori_id" class="form-control" required>
                <option value="">Seçiniz</option>
                <?php while($kat = $kategoriler->fetch_assoc()): ?>
                    <option value="<?php echo $kat['id']; ?>"><?php echo e($kat['ad']); ?></option>
                <?php endwhile; ?>
            </select>
        </div>
        <div class="mb-3">
            <label>Görsel</label>
            <input type="file" name="gorsel" class="form-control">
        </div>
        <div class="mb-3 form-check">
            <input type="checkbox" name="one_cikan" class="form-check-input" id="one_cikan">
            <label class="form-check-label" for="one_cikan">Manşet (öne çıkan)</label>
        </div>
        <button type="submit" class="btn btn-success">Ekle</button>
    </form>
</div>
</body>
</html> 