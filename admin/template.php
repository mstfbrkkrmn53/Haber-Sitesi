<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - <?php echo $page_title ?? 'Haber Sitesi'; ?></title>
    
    <!-- CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
    
    <!-- JavaScript -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="script.js" defer></script>
</head>
<body>
    <div class="admin-container">
        <!-- Sidebar -->
        <aside class="admin-sidebar">
            <div class="sidebar-header">
                <img src="../assets/images/logo.png" alt="Logo">
            </div>
            
            <nav class="sidebar-menu">
                <a href="index.php" class="menu-item <?php echo $current_page === 'dashboard' ? 'active' : ''; ?>">
                    <i class="fas fa-home"></i>
                    <span>Dashboard</span>
                </a>
                
                <a href="haberler.php" class="menu-item <?php echo $current_page === 'haberler' ? 'active' : ''; ?>">
                    <i class="fas fa-newspaper"></i>
                    <span>Haberler</span>
                </a>
                
                <a href="kategoriler.php" class="menu-item <?php echo $current_page === 'kategoriler' ? 'active' : ''; ?>">
                    <i class="fas fa-folder"></i>
                    <span>Kategoriler</span>
                </a>
                
                <a href="yorumlar.php" class="menu-item <?php echo $current_page === 'yorumlar' ? 'active' : ''; ?>">
                    <i class="fas fa-comments"></i>
                    <span>Yorumlar</span>
                </a>
                
                <a href="kullanicilar.php" class="menu-item <?php echo $current_page === 'kullanicilar' ? 'active' : ''; ?>">
                    <i class="fas fa-users"></i>
                    <span>Kullanıcılar</span>
                </a>
                
                <a href="ayarlar.php" class="menu-item <?php echo $current_page === 'ayarlar' ? 'active' : ''; ?>">
                    <i class="fas fa-cog"></i>
                    <span>Ayarlar</span>
                </a>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="admin-main">
            <!-- Header -->
            <header class="admin-header">
                <h1><?php echo $page_title ?? 'Dashboard'; ?></h1>
                
                <div class="admin-user">
                    <img src="<?php echo $_SESSION['user_avatar'] ?? '../assets/images/default-avatar.png'; ?>" alt="User Avatar">
                    <div class="user-info">
                        <span class="user-name"><?php echo $_SESSION['user_name'] ?? 'Admin'; ?></span>
                        <a href="cikis.php" class="btn btn-danger btn-sm">Çıkış Yap</a>
                    </div>
                </div>
            </header>

            <!-- Content -->
            <div class="content">
                <?php if (isset($_SESSION['success'])): ?>
                    <div class="alert alert-success">
                        <?php 
                        echo $_SESSION['success'];
                        unset($_SESSION['success']);
                        ?>
                    </div>
                <?php endif; ?>

                <?php if (isset($_SESSION['error'])): ?>
                    <div class="alert alert-danger">
                        <?php 
                        echo $_SESSION['error'];
                        unset($_SESSION['error']);
                        ?>
                    </div>
                <?php endif; ?>

                <?php 
                // CSRF token oluştur
                $csrf_token = $admin->generateCSRFToken();
                ?>
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">

                <?php echo $content ?? ''; ?>
            </div>
        </main>
    </div>

    <!-- Mobile Menu Toggle -->
    <button class="mobile-menu-toggle">
        <i class="fas fa-bars"></i>
    </button>

    <!-- Loading Spinner -->
    <div class="loading" style="display: none;"></div>

    <!-- Modal Template -->
    <div class="modal" id="confirmationModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Onay</h3>
                <span class="modal-close">&times;</span>
            </div>
            <div class="modal-body">
                <p>Bu işlemi gerçekleştirmek istediğinizden emin misiniz?</p>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" data-dismiss="modal">İptal</button>
                <button class="btn btn-danger" id="confirmButton">Onayla</button>
            </div>
        </div>
    </div>
</body>
</html> 