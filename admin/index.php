<?php
require_once '../config/db.php';
require_once '../classes/Admin.php';

// Oturum kontrolü
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: giris.php');
    exit;
}

// İstatistikleri al
$admin = new Admin($db);
$stats = $admin->getStats();

// Sayfa başlığı
$page_title = 'Dashboard';
$current_page = 'dashboard';

// İçerik
ob_start();
?>

<!-- Stats Grid -->
<div class="stats-grid">
    <!-- Toplam Haber -->
    <div class="stat-card">
        <div class="stat-card-header">
            <h3 class="stat-card-title">Toplam Haber</h3>
            <div class="stat-card-icon" style="background-color: rgba(0, 123, 255, 0.1); color: var(--primary-color);">
                <i class="fas fa-newspaper"></i>
            </div>
        </div>
        <div class="stat-card-value"><?php echo number_format($stats['total_news']); ?></div>
        <div class="stat-card-footer">
            <span class="text-success">
                <i class="fas fa-arrow-up"></i> <?php echo $stats['news_increase']; ?>%
            </span>
            <span class="text-muted">geçen aya göre</span>
        </div>
    </div>

    <!-- Toplam Kullanıcı -->
    <div class="stat-card">
        <div class="stat-card-header">
            <h3 class="stat-card-title">Toplam Kullanıcı</h3>
            <div class="stat-card-icon" style="background-color: rgba(40, 167, 69, 0.1); color: var(--success-color);">
                <i class="fas fa-users"></i>
            </div>
        </div>
        <div class="stat-card-value"><?php echo number_format($stats['total_users']); ?></div>
        <div class="stat-card-footer">
            <span class="text-success">
                <i class="fas fa-arrow-up"></i> <?php echo $stats['users_increase']; ?>%
            </span>
            <span class="text-muted">geçen aya göre</span>
        </div>
    </div>

    <!-- Toplam Yorum -->
    <div class="stat-card">
        <div class="stat-card-header">
            <h3 class="stat-card-title">Toplam Yorum</h3>
            <div class="stat-card-icon" style="background-color: rgba(255, 193, 7, 0.1); color: var(--warning-color);">
                <i class="fas fa-comments"></i>
            </div>
        </div>
        <div class="stat-card-value"><?php echo number_format($stats['total_comments']); ?></div>
        <div class="stat-card-footer">
            <span class="text-success">
                <i class="fas fa-arrow-up"></i> <?php echo $stats['comments_increase']; ?>%
            </span>
            <span class="text-muted">geçen aya göre</span>
        </div>
    </div>

    <!-- Toplam Görüntülenme -->
    <div class="stat-card">
        <div class="stat-card-header">
            <h3 class="stat-card-title">Toplam Görüntülenme</h3>
            <div class="stat-card-icon" style="background-color: rgba(23, 162, 184, 0.1); color: var(--info-color);">
                <i class="fas fa-eye"></i>
            </div>
        </div>
        <div class="stat-card-value"><?php echo number_format($stats['total_views']); ?></div>
        <div class="stat-card-footer">
            <span class="text-success">
                <i class="fas fa-arrow-up"></i> <?php echo $stats['views_increase']; ?>%
            </span>
            <span class="text-muted">geçen aya göre</span>
        </div>
    </div>
</div>

<!-- Son Haberler ve Yorumlar -->
<div class="row">
    <!-- Son Haberler -->
    <div class="col-md-6">
        <div class="admin-card">
            <div class="card-header">
                <h2 class="card-title">Son Haberler</h2>
                <a href="haberler.php" class="btn btn-primary btn-sm">Tümünü Gör</a>
            </div>
            <div class="table-responsive">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Başlık</th>
                            <th>Kategori</th>
                            <th>Tarih</th>
                            <th>Görüntülenme</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($stats['recent_news'] as $news): ?>
                        <tr>
                            <td>
                                <a href="../haber.php?id=<?php echo $news['id']; ?>" target="_blank">
                                    <?php echo htmlspecialchars($news['title']); ?>
                                </a>
                            </td>
                            <td><?php echo htmlspecialchars($news['category_name']); ?></td>
                            <td><?php echo date('d.m.Y', strtotime($news['created_at'])); ?></td>
                            <td><?php echo number_format($news['views']); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Son Yorumlar -->
    <div class="col-md-6">
        <div class="admin-card">
            <div class="card-header">
                <h2 class="card-title">Son Yorumlar</h2>
                <a href="yorumlar.php" class="btn btn-primary btn-sm">Tümünü Gör</a>
            </div>
            <div class="table-responsive">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Kullanıcı</th>
                            <th>Yorum</th>
                            <th>Tarih</th>
                            <th>Durum</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($stats['recent_comments'] as $comment): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($comment['username']); ?></td>
                            <td>
                                <a href="../haber.php?id=<?php echo $comment['news_id']; ?>#comment-<?php echo $comment['id']; ?>" target="_blank">
                                    <?php echo mb_substr(htmlspecialchars($comment['content']), 0, 50) . '...'; ?>
                                </a>
                            </td>
                            <td><?php echo date('d.m.Y', strtotime($comment['created_at'])); ?></td>
                            <td>
                                <span class="badge <?php echo $comment['status'] ? 'bg-success' : 'bg-warning'; ?>">
                                    <?php echo $comment['status'] ? 'Onaylı' : 'Beklemede'; ?>
                                </span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Grafikler -->
<div class="row mt-4">
    <!-- Görüntülenme Grafiği -->
    <div class="col-md-8">
        <div class="admin-card">
            <div class="card-header">
                <h2 class="card-title">Görüntülenme İstatistikleri</h2>
                <div class="btn-group">
                    <button class="btn btn-outline-secondary btn-sm active" data-period="week">Haftalık</button>
                    <button class="btn btn-outline-secondary btn-sm" data-period="month">Aylık</button>
                    <button class="btn btn-outline-secondary btn-sm" data-period="year">Yıllık</button>
                </div>
            </div>
            <div class="card-body">
                <canvas id="viewsChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Kategori Dağılımı -->
    <div class="col-md-4">
        <div class="admin-card">
            <div class="card-header">
                <h2 class="card-title">Kategori Dağılımı</h2>
            </div>
            <div class="card-body">
                <canvas id="categoryChart"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Gerekli JavaScript -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Görüntülenme Grafiği
const viewsCtx = document.getElementById('viewsChart').getContext('2d');
const viewsChart = new Chart(viewsCtx, {
    type: 'line',
    data: {
        labels: <?php echo json_encode($stats['views_chart']['labels']); ?>,
        datasets: [{
            label: 'Görüntülenme',
            data: <?php echo json_encode($stats['views_chart']['data']); ?>,
            borderColor: 'rgb(0, 123, 255)',
            tension: 0.1
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false
    }
});

// Kategori Dağılımı Grafiği
const categoryCtx = document.getElementById('categoryChart').getContext('2d');
const categoryChart = new Chart(categoryCtx, {
    type: 'doughnut',
    data: {
        labels: <?php echo json_encode($stats['category_chart']['labels']); ?>,
        datasets: [{
            data: <?php echo json_encode($stats['category_chart']['data']); ?>,
            backgroundColor: [
                'rgb(0, 123, 255)',
                'rgb(40, 167, 69)',
                'rgb(255, 193, 7)',
                'rgb(23, 162, 184)',
                'rgb(220, 53, 69)'
            ]
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false
    }
});

// Grafik Periyodu Değiştirme
document.querySelectorAll('[data-period]').forEach(button => {
    button.addEventListener('click', function() {
        const period = this.dataset.period;
        
        // Aktif buton stilini güncelle
        document.querySelectorAll('[data-period]').forEach(btn => {
            btn.classList.remove('active');
        });
        this.classList.add('active');
        
        // AJAX ile yeni verileri al
        fetch(`ajax/get_views_stats.php?period=${period}`)
            .then(response => response.json())
            .then(data => {
                viewsChart.data.labels = data.labels;
                viewsChart.data.datasets[0].data = data.data;
                viewsChart.update();
            });
    });
});
</script>

<?php
$content = ob_get_clean();
require_once 'template.php';
?> 