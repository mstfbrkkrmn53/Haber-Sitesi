<?php
require_once 'db.php';
require_once 'functions.php';

$q = isset($_GET['q']) ? trim($_GET['q']) : '';
$haberler = [];
if ($q !== '') {
    $q_escaped = $conn->real_escape_string($q);
    $sql = "SELECT h.*, k.ad as kategori_ad FROM haberler h JOIN kategoriler k ON h.kategori_id = k.id WHERE h.baslik LIKE '%$q_escaped%' ORDER BY h.tarih DESC";
    $haberler = $conn->query($sql);
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Arama Sonuçları - Haber Sitesi</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="assets/bootstrap.min.css" rel="stylesheet">
    <link href="assets/style.css" rel="stylesheet">
</head>
<body>
<div class="container mt-4">
    <a href="index.php" class="btn btn-secondary mb-3">← Ana Sayfa</a>
    <h1>Arama Sonuçları</h1>
    <form class="mb-4" method="get">
        <input type="text" name="q" class="form-control" value="<?php echo e($q); ?>" placeholder="Haber başlığı ara..." required>
    </form>
    <?php if($q === ''): ?>
        <div class="alert alert-info">Aramak için bir kelime girin.</div>
    <?php elseif($haberler && $haberler->num_rows > 0): ?>
        <div class="list-group">
            <?php while($haber = $haberler->fetch_assoc()): ?>
                <a href="haber.php?id=<?php echo $haber['id']; ?>" class="list-group-item list-group-item-action">
                    <h5 class="mb-1"><?php echo e($haber['baslik']); ?></h5>
                    <small><?php echo e($haber['kategori_ad']); ?> | <?php echo e($haber['tarih']); ?></small>
                    <p class="mb-1"><?php echo mb_substr(strip_tags($haber['icerik']), 0, 100) . '...'; ?></p>
                </a>
            <?php endwhile; ?>
        </div>
    <?php else: ?>
        <div class="alert alert-warning">Sonuç bulunamadı.</div>
    <?php endif; ?>
</div>
    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Theme Toggle Button -->
    <button class="theme-toggle" onclick="toggleTheme()">🌓</button>
    <script>
        function toggleTheme() {
            const html = document.documentElement;
            const currentTheme = html.getAttribute('data-theme');
            const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
            html.setAttribute('data-theme', newTheme);
            localStorage.setItem('theme', newTheme);
        }

        // Check for saved theme preference
        const savedTheme = localStorage.getItem('theme');
        if (savedTheme) {
            document.documentElement.setAttribute('data-theme', savedTheme);
        }
    </script>
</body>
</html> 