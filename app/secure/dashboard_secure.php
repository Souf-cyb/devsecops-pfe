<?php
session_start();
require_once '../secure/config_secure.php';
set_security_headers();

// ✅ Vérification authentification
if (!isset($_SESSION['user_id'])) {
    header("Location: login_secure.php");
    exit();
}

$conn        = getDB();
$target_user = null;

// ✅ IDOR fix — forcer l'ID de la session
$user_id = (int)$_SESSION['user_id'];

// ✅ Prepared Statement — plus de SQLi
$stmt = $conn->prepare(
    "SELECT id, username, email, is_admin FROM users WHERE id = ?"
);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result       = $stmt->get_result();
$current_user = $result->fetch_assoc();
$stmt->close();

// ✅ Admins peuvent voir d'autres profils mais avec validation
if (isset($_GET['user_id']) && $_SESSION['is_admin']) {
    $view_id = validate_int($_GET['user_id']);
    if ($view_id && $view_id > 0) {
        $stmt2 = $conn->prepare(
            "SELECT id, username, email, is_admin FROM users WHERE id = ?"
        );
        $stmt2->bind_param("i", $view_id);
        $stmt2->execute();
        $result2     = $stmt2->get_result();
        $target_user = $result2->fetch_assoc();
        $stmt2->close();
    }
}

$csrf_token = generate_csrf_token();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Secure Dashboard - VulnShop</title>
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family:'Segoe UI',sans-serif; background:#f5f5f5; }
        .navbar { background:linear-gradient(135deg,#1a1a2e,#16213e); padding:15px 30px; display:flex; justify-content:space-between; align-items:center; }
        .navbar .logo { color:#27ae60; font-size:24px; font-weight:bold; text-decoration:none; }
        .navbar .logo span { color:white; }
        .secure-bar { background:#eafaf1; border-bottom:1px solid #a9dfbf; padding:8px 30px; font-size:12px; color:#27ae60; }
        .container { max-width:900px; margin:35px auto; padding:0 20px; }
        .welcome { background:linear-gradient(135deg,#27ae60,#219a52); color:white; padding:25px 30px; border-radius:12px; margin-bottom:20px; }
        .welcome h2 { font-size:22px; }
        .welcome p { opacity:0.8; margin-top:5px; }
        .grid2 { display:grid; grid-template-columns:1fr 1fr; gap:18px; margin-bottom:18px; }
        .card { background:white; border-radius:10px; padding:22px; box-shadow:0 3px 12px rgba(0,0,0,0.08); }
        .card h3 { color:#1a1a2e; margin-bottom:15px; padding-bottom:10px; border-bottom:2px solid #27ae60; font-size:15px; }
        .info-row { display:flex; justify-content:space-between; padding:8px 0; border-bottom:1px solid #f0f0f0; font-size:13px; }
        .info-row label { color:#666; }
        .info-row span { color:#1a1a2e; font-weight:500; }
        .security-info { background:#eafaf1; border:1px solid #a9dfbf; padding:12px 15px; border-radius:8px; margin-top:15px; font-size:12px; color:#27ae60; }
        .security-info li { margin-left:15px; margin-top:3px; }
        footer { background:#1a1a2e; color:#aaa; text-align:center; padding:20px; margin-top:40px; }
    </style>
</head>
<body>
<nav class="navbar">
    <a class="logo" href="index_secure.php">Vuln<span>Shop</span> Secure</a>
    <div class="nav-links">
        <a href="index_secure.php" style="color:#ccc;text-decoration:none;margin-left:25px">Home</a>
        <a href="login_secure.php" style="color:#ccc;text-decoration:none;margin-left:25px">Logout</a>
    </div>
</nav>
<div class="secure-bar">
    ✅ Secure session — User ID: <?php echo (int)$_SESSION['user_id']; ?>
</div>

<div class="container">

    <div class="welcome">
        <h2>Welcome, <?php echo sanitize($_SESSION['username']); ?>!</h2>
        <p>Your secure dashboard</p>
    </div>

    <div class="grid2">
        <div class="card">
            <h3>Profile Information</h3>
            <?php if ($current_user): ?>
                <div class="info-row">
                    <label>Username</label>
                    <span><?php echo sanitize($current_user['username']); ?></span>
                </div>
                <div class="info-row">
                    <label>Email</label>
                    <span><?php echo sanitize($current_user['email']); ?></span>
                </div>
                <!-- ✅ Mot de passe NON affiché -->
                <div class="info-row">
                    <label>Password</label>
                    <span style="color:#27ae60">••••••••</span>
                </div>
                <div class="info-row">
                    <label>Role</label>
                    <span><?php echo $current_user['is_admin'] ? 'Admin' : 'User'; ?></span>
                </div>
            <?php endif; ?>

            <div class="security-info">
                <b>Fixes:</b>
                <ul>
                    <li>✅ Password never displayed</li>
                    <li>✅ IDOR fixed — own profile only</li>
                    <li>✅ Prepared Statement</li>
                    <li>✅ All output sanitized</li>
                </ul>
            </div>
        </div>

        <div class="card">
            <h3>Account Stats</h3>
            <div class="info-row"><label>Orders</label><span>12</span></div>
            <div class="info-row"><label>Wishlist</label><span>5 items</span></div>
            <div class="info-row"><label>Total Spent</label><span>$2,450.00</span></div>
            <div class="info-row"><label>Member Since</label><span>2023</span></div>
        </div>
    </div>

</div>

<footer><p>© 2026 VulnShop Secure — DevSecOps PFE</p></footer>
</body>
</html>