<?php
session_start();
require_once 'config.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $conn = getDB();
    // Vulnérabilité : SQL Injection
    $query = "SELECT * FROM users WHERE username='" . $username . "' AND password='" . $password . "'";
    $result = mysqli_query($conn, $query);
    if ($result && mysqli_num_rows($result) > 0) {
        $user = mysqli_fetch_assoc($result);
        $_SESSION['user'] = $user;
        $_SESSION['username'] = $username;
        $_SESSION['is_admin'] = $user['is_admin'];
        $redirect = isset($_GET['redirect']) ? $_GET['redirect'] : 'dashboard.php';
        header("Location: " . $redirect);
        exit();
    } else {
        $error = "Login failed for user: " . $username;
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Login - VulnShop</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', sans-serif;
            background: linear-gradient(135deg, #1a1a2e, #16213e);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-container {
            background: white;
            padding: 40px;
            border-radius: 15px;
            width: 400px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        }
        .login-container h2 {
            text-align: center;
            color: #1a1a2e;
            margin-bottom: 30px;
            font-size: 28px;
        }
        .login-container h2 span { color: #e94560; }
        .form-group { margin-bottom: 20px; }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #555;
            font-size: 14px;
            font-weight: bold;
        }
        .form-group input {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #eee;
            border-radius: 8px;
            font-size: 14px;
            transition: border-color 0.3s;
        }
        .form-group input:focus {
            outline: none;
            border-color: #e94560;
        }
        .btn-login {
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, #e94560, #c0392b);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            cursor: pointer;
            transition: transform 0.3s;
        }
        .btn-login:hover { transform: scale(1.02); }
        .error {
            background: #ffe0e0;
            border: 1px solid #e94560;
            color: #c0392b;
            padding: 10px 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
        }
        .hint {
            background: #fff3cd;
            border: 1px solid #ffc107;
            padding: 10px 15px;
            border-radius: 8px;
            margin-top: 20px;
            font-size: 12px;
            color: #856404;
        }
        .back-link {
            text-align: center;
            margin-top: 20px;
        }
        .back-link a { color: #e94560; text-decoration: none; }
    </style>
</head>
<body>
<div class="login-container">
    <h2>🔐 Vuln<span>Shop</span></h2>

    <?php if ($error): ?>
        <!-- Vulnérabilité : XSS dans le message d'erreur -->
        <div class="error"><?php echo $error; ?></div>
    <?php endif; ?>

    <!-- Vulnérabilité : Pas de token CSRF -->
    <form method="POST">
        <div class="form-group">
            <label>Username</label>
            <input type="text" name="username" placeholder="Enter your username">
        </div>
        <div class="form-group">
            <label>Password</label>
            <input type="password" name="password" placeholder="Enter your password">
        </div>
        <button type="submit" class="btn-login">Login</button>
    </form>

    <!-- Vulnérabilité : Credentials exposés dans les commentaires -->
    <!-- Default credentials: admin / admin123 -->
    <div class="hint">
        💡 Hint: Try SQL Injection → <code>' OR 1=1 --</code>
    </div>

    <div class="back-link">
        <a href="index.php">← Back to Shop</a>
    </div>
</div>
</body>
</html>