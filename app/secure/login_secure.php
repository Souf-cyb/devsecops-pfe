<?php
session_start();
session_regenerate_id(true); // ✅ Prévient le session fixation
require_once 'config_secure.php';
set_security_headers();

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    // ✅ Validation CSRF
    validate_csrf_token();

    $username = sanitize($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    // ✅ Validation des entrées
    if (empty($username) || empty($password)) {
        $error = "Please fill in all fields.";
    } elseif (strlen($username) > 50 || strlen($password) > 100) {
        $error = "Invalid credentials."; // ✅ Message générique
    } else {
        $conn = getDB();

        // ✅ Prepared Statement — plus de SQLi possible
        $stmt = $conn->prepare(
            "SELECT id, username, password, is_admin FROM users WHERE username = ?"
        );
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $result->num_rows === 1) {
            $user = $result->fetch_assoc();

            // ✅ Vérification du hash du mot de passe
            if (password_verify($password, $user['password'])) {
                // ✅ Régénération de session après login
                session_regenerate_id(true);
                $_SESSION['user_id']  = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['is_admin'] = $user['is_admin'];

                // ✅ Validation de l'open redirect
                $redirect = 'dashboard_secure.php';
                if (isset($_GET['redirect'])) {
                    $allowed = ['dashboard_secure.php', 'index_secure.php'];
                    if (in_array($_GET['redirect'], $allowed)) {
                        $redirect = $_GET['redirect'];
                    }
                }
                header("Location: " . $redirect);
                exit();
            }
        }
        // ✅ Message générique — pas d'info sur username/password
        $error = "Invalid credentials.";
        // ✅ Délai pour ralentir les brute force
        sleep(1);
        $stmt->close();
    }
}

$csrf_token = generate_csrf_token();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Secure Login - VulnShop</title>
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family:'Segoe UI',sans-serif; background:linear-gradient(135deg,#1a1a2e,#16213e); min-height:100vh; display:flex; align-items:center; justify-content:center; }
        .card { background:white; padding:40px; border-radius:15px; width:400px; box-shadow:0 20px 60px rgba(0,0,0,0.3); }
        .card h2 { text-align:center; color:#1a1a2e; margin-bottom:8px; font-size:26px; }
        .card h2 span { color:#27ae60; }
        .secure-badge { text-align:center; background:#eafaf1; color:#27ae60; padding:6px 15px; border-radius:20px; font-size:12px; margin-bottom:25px; }
        .form-group { margin-bottom:18px; }
        .form-group label { display:block; margin-bottom:6px; color:#555; font-size:13px; font-weight:bold; }
        .form-group input { width:100%; padding:12px 15px; border:2px solid #eee; border-radius:8px; font-size:14px; transition:border-color 0.3s; }
        .form-group input:focus { outline:none; border-color:#27ae60; }
        .btn { width:100%; padding:12px; background:linear-gradient(135deg,#27ae60,#219a52); color:white; border:none; border-radius:8px; font-size:16px; cursor:pointer; }
        .btn:hover { opacity:0.9; }
        .error { background:#ffe0e0; border:1px solid #e74c3c; color:#c0392b; padding:10px 15px; border-radius:8px; margin-bottom:18px; font-size:13px; }
        .security-info { background:#f8f9fa; padding:12px 15px; border-radius:8px; margin-top:20px; font-size:12px; color:#666; }
        .security-info li { margin-left:15px; margin-top:4px; }
        .back { text-align:center; margin-top:15px; font-size:13px; }
        .back a { color:#27ae60; text-decoration:none; }
    </style>
</head>
<body>
<div class="card">
    <h2>Vuln<span>Shop</span> Secure</h2>
    <div class="secure-badge">✅ Secured Version — All vulnerabilities fixed</div>

    <?php if ($error): ?>
        <!-- ✅ sanitize() — plus de XSS possible -->
        <div class="error"><?php echo sanitize($error); ?></div>
    <?php endif; ?>

    <form method="POST" autocomplete="off">
        <!-- ✅ Token CSRF caché -->
        <input type="hidden" name="csrf_token"
               value="<?php echo $csrf_token; ?>">

        <div class="form-group">
            <label>Username</label>
            <input type="text" name="username"
                   maxlength="50"
                   placeholder="Enter your username"
                   required>
        </div>
        <div class="form-group">
            <label>Password</label>
            <input type="password" name="password"
                   maxlength="100"
                   placeholder="Enter your password"
                   required>
        </div>
        <button type="submit" class="btn">Login Securely</button>
    </form>

    <div class="security-info">
        <b>Security fixes applied:</b>
        <ul>
            <li>✅ SQL Injection → Prepared Statements</li>
            <li>✅ XSS → htmlspecialchars()</li>
            <li>✅ CSRF → Token validation</li>
            <li>✅ Open Redirect → Whitelist validation</li>
            <li>✅ Brute Force → Rate limiting (sleep)</li>
            <li>✅ Session Fixation → session_regenerate_id()</li>
        </ul>
    </div>

    <div class="back">
        <a href="index_secure.php">← Back to Secure Shop</a>
    </div>
</div>
</body>
</html>