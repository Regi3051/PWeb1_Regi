<?php
// admin/login.php - Admin Login Interface
require_once __DIR__ . '/auth.php';

if (is_admin_logged_in()) {
    header('Location: /index.html');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($username) || empty($password)) {
        $error = 'Silakan isi username dan password.';
    } else {
        if (login_admin($username, $password)) {
            echo "<!DOCTYPE html><html><head><title>Logging in...</title></head><body><script>sessionStorage.setItem('admin_tab_active', '1'); window.location.href = '/index.html';</script></body></html>";
            exit;
        } else {
            $error = 'Username atau password salah!';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Portfolio Regi Heksa</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css" />
    <link rel="stylesheet" href="/style.css" />
    <style>
        body {
            background-color: #2b332b;
            color: #dbc2a6;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            padding: 20px;
        }
        .login-card {
            background-color: #414a37;
            border: 2px solid #99744a;
            border-radius: 16px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.4);
            max-width: 420px;
            width: 100%;
            padding: 2.5rem 2rem;
            position: relative;
        }
        .login-card .brand-title {
            color: #dbc2a6;
            font-weight: 700;
            font-size: 1.8rem;
            text-align: center;
            margin-bottom: 0.3rem;
        }
        .login-card .brand-subtitle {
            color: #aeb4a2;
            font-size: 0.95rem;
            text-align: center;
            margin-bottom: 2rem;
        }
        .form-label {
            color: #dbc2a6;
            font-weight: 500;
            font-size: 0.95rem;
        }
        .form-control {
            background-color: #2b332b;
            border: 1.5px solid #99744a;
            color: #dbc2a6;
            border-radius: 10px;
            padding: 10px 14px;
        }
        .form-control:focus {
            background-color: #2b332b;
            color: #fff;
            border-color: #dbc2a6;
            box-shadow: 0 0 10px rgba(153, 116, 74, 0.4);
        }
        .input-group-text {
            background-color: #2b332b;
            border: 1.5px solid #99744a;
            color: #99744a;
            border-right: none;
            border-radius: 10px 0 0 10px;
        }
        .input-group .form-control {
            border-left: none;
            border-radius: 0 10px 10px 0;
        }
        .btn-admin {
            background-color: #99744a;
            color: #2b332b;
            font-weight: bold;
            border: none;
            width: 100%;
            padding: 12px;
            border-radius: 25px;
            transition: 0.3s;
            margin-top: 1rem;
            font-size: 1rem;
        }
        .btn-admin:hover {
            background-color: #dbc2a6;
            color: #414a37;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.3);
        }
        .default-info {
            background-color: rgba(43, 51, 43, 0.6);
            border-left: 3px solid #99744a;
            border-radius: 6px;
            padding: 8px 12px;
            font-size: 0.85rem;
            color: #aeb4a2;
            margin-top: 1.5rem;
        }
        .back-link {
            display: block;
            text-align: center;
            color: #aeb4a2;
            text-decoration: none;
            margin-top: 1.5rem;
            font-size: 0.9rem;
            transition: 0.2s;
        }
        .back-link:hover {
            color: #dbc2a6;
        }
    </style>
</head>
<body>

    <div class="login-card">
        <div class="text-center mb-3">
            <span style="background-color: #99744a; color: #2b332b; width: 50px; height: 50px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; font-size: 1.5rem;">
                <i class="bi bi-shield-lock"></i>
            </span>
        </div>
        <h2 class="brand-title">Admin Panel</h2>
        <p class="brand-subtitle">Masuk untuk mengelola laporan & portofolio</p>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger py-2 px-3 small border-0" style="background-color: #dc3545; color: #fff; border-radius: 8px;">
                <i class="bi bi-exclamation-triangle-fill me-1"></i> <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form action="" method="POST" autocomplete="off">
            <div class="mb-3">
                <label for="username" class="form-label">Username</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-person"></i></span>
                    <input type="text" class="form-control" id="username" name="username" placeholder="Masukkan username" required autofocus />
                </div>
            </div>

            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-key"></i></span>
                    <input type="password" class="form-control" id="password" name="password" placeholder="Masukkan password" required />
                </div>
            </div>

            <button type="submit" class="btn btn-admin">
                <i class="bi bi-box-arrow-in-right me-1"></i> Masuk Admin
            </button>
        </form>

        <a href="/" class="back-link">
            <i class="bi bi-arrow-left me-1"></i> Kembali ke Portfolio
        </a>
    </div>

</body>
</html>

