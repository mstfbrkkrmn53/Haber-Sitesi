<?php
require_once '../config/db.php';
require_once '../classes/Admin.php';

session_start();

// Zaten giriş yapmışsa dashboard'a yönlendir
if (isset($_SESSION['admin_id'])) {
    header('Location: index.php');
    exit;
}

$error = '';

// Form gönderildi mi?
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if (empty($username) || empty($password)) {
        $error = 'Lütfen tüm alanları doldurun.';
    } else {
        $admin = new Admin($db);
        
        if ($admin->login($username, $password)) {
            header('Location: index.php');
            exit;
        } else {
            $error = 'Kullanıcı adı veya şifre hatalı.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Girişi - Haber Sitesi</title>
    
    <!-- CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
    
    <style>
        body {
            background-color: var(--light-color);
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            margin: 0;
            padding: 1rem;
        }
        
        .login-container {
            width: 100%;
            max-width: 400px;
        }
        
        .login-card {
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 2rem;
        }
        
        .login-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        
        .login-header img {
            max-width: 150px;
            height: auto;
            margin-bottom: 1rem;
        }
        
        .login-header h1 {
            font-size: 1.5rem;
            color: var(--dark-color);
            margin: 0;
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            color: var(--dark-color);
            font-weight: 500;
        }
        
        .form-control {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #ced4da;
            border-radius: 4px;
            font-size: 1rem;
            transition: border-color 0.3s;
        }
        
        .form-control:focus {
            outline: none;
            border-color: var(--primary-color);
        }
        
        .input-group {
            position: relative;
        }
        
        .input-group .form-control {
            padding-right: 2.5rem;
        }
        
        .input-group-icon {
            position: absolute;
            right: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--secondary-color);
        }
        
        .btn-login {
            width: 100%;
            padding: 0.75rem;
            background-color: var(--primary-color);
            color: #fff;
            border: none;
            border-radius: 4px;
            font-size: 1rem;
            font-weight: 500;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        
        .btn-login:hover {
            background-color: #0056b3;
        }
        
        .alert {
            padding: 1rem;
            border-radius: 4px;
            margin-bottom: 1rem;
        }
        
        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .back-to-site {
            text-align: center;
            margin-top: 1rem;
        }
        
        .back-to-site a {
            color: var(--secondary-color);
            text-decoration: none;
            font-size: 0.875rem;
        }
        
        .back-to-site a:hover {
            color: var(--primary-color);
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <img src="../assets/images/logo.png" alt="Logo">
                <h1>Admin Girişi</h1>
            </div>
            
            <?php if ($error): ?>
                <div class="alert alert-danger">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="form-group">
                    <label class="form-label" for="username">Kullanıcı Adı</label>
                    <div class="input-group">
                        <input type="text" class="form-control" id="username" name="username" required>
                        <i class="fas fa-user input-group-icon"></i>
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="form-label" for="password">Şifre</label>
                    <div class="input-group">
                        <input type="password" class="form-control" id="password" name="password" required>
                        <i class="fas fa-lock input-group-icon"></i>
                    </div>
                </div>
                
                <button type="submit" class="btn-login">Giriş Yap</button>
            </form>
            
            <div class="back-to-site">
                <a href="../index.php">
                    <i class="fas fa-arrow-left"></i> Siteye Dön
                </a>
            </div>
        </div>
    </div>
    
    <!-- JavaScript -->
    <script>
        // Form gönderildiğinde loading göster
        document.querySelector('form').addEventListener('submit', function() {
            this.querySelector('.btn-login').innerHTML = '<i class="fas fa-spinner fa-spin"></i> Giriş Yapılıyor...';
            this.querySelector('.btn-login').disabled = true;
        });
    </script>
</body>
</html> 