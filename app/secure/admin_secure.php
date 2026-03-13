<?php
session_start();
require_once 'config_secure.php';
set_security_headers();

// ✅ Vérification authentification ET rôle admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
    http_response_code(403);
    header("Location: login_secure.php");
    exit();
}

$conn           = getDB();
$search_results = [];
$ping_output    = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    validate_csrf_token();

    if (isset($_POST['search_user'])) {
        $search = sanitize($_POST['search_user']);

        // ✅ Prepared Statement — plus de SQLi
        $stmt = $conn->prepare(
            "SELECT id, username, email, is_admin FROM users
             WHERE username LIKE ? LIMIT 10"
        );
        $like = "%" . $search . "%";
        $stmt->bind_param("s", $like);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $search_results[] = $row;
        }
        $stmt->close();
    }

    if (isset($_POST['ping'])) {
        $host = $_POST['ping'];

        // ✅ Validation stricte de l'IP/hostname
        if (filter_var($host, FILTER_VALIDATE_IP) ||
            preg_match('/^[a-zA-Z0-9\-\.]{1,253}$/', $host)) {
            // ✅ escapeshellarg() — plus de Command Injection
            $safe_host   = escapeshellarg($host);
            $ping_output = shell_exec("ping -c 2 " . $safe_host);
            // ✅ Sanitisation de l'output
            $ping_output = htmlspecialchars($ping_output, ENT_QUOTES, 'UTF-8');
        } else {
            $ping_output = "Invalid hostname or IP address.";
        }
    }
}

$csrf_token = generate_csrf_token();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Secure Admin - VulnShop</title>
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family:'Segoe UI',sans-serif; background:#f5f5f5; }
        .navbar { background:linear-gradient(135deg,#1a1a2e,#16213e); padding:15px 30px; display:flex; justify-content:space-between; align-items:center; }
        .navbar .logo { color:#27ae60; font-size:24px; font-weight:bold; text-decoration:none; }
        .navbar .logo span { color:white; }
        .secure-bar { background:#eafaf1; border-bottom:1px solid #a9dfbf; padding:8px 30px; font-size:12px; color:#27ae60; }
        .container { max-width:1100px; margin:30px auto; padding:0 20px; }
        .admin-header { background:linear-gradient(135deg,#27ae60,#219a52); color:white; padding:25px 30px; border-radius:10px; margin-bottom:25px; }
        .admin-header h1 { font-size:24px; }
        .admin-header p { opacity:0.8; margin-top:5px; }
        .grid2 { display:grid; grid-template-columns:1fr 1fr; gap:20px; }
        .card { background:white; border-radius:10px; padding:22px; box-shadow:0 3px 12px rgba(0,0,0,0.08); }
        .card h3 { color:#1a1a2e; margin-bottom:18px; padding-bottom:10px; border-bottom:2px solid #27ae60; }
        .form-group { margin-bottom:14px; }
        .form-group label { display:block; margin-bottom:5px; color:#555; font-size:13px; }
        .form-group input { width:100%; padding:10px 14px; border:2px solid #eee; border-radius:8px; font-size:13px; }
        .form-group input:focus { outline:none; border-color:#27ae60; }
        .btn { padding:10px 22px; background:#27ae60; color:white; border:none; border-radius:8px; cursor:pointer; font-size:13px; }
        .btn:hover { background:#219a52; }
        table { width:100%; border-collapse:collapse; font-size:13px; margin-top:12px; }
        th { background:#1a1a2e; color:white; padding:8px 12px; text-align:left; font-size:12px; }
        td { padding:8px 12px; border-bottom:1px solid #f0f0f0; }
        tr:hover td { background:#f8f9fa; }
        .terminal { background:#1a1a2e; color:#00ff00; padding:14px; border-radius:8px; font-family:monospace; font-size:12px; margin-top:12px; white-space:pre-wrap; min-height:80px; }
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
    </div>
</nav>
<div class="secure-bar">
    ✅ Secure version — Authenticated as:
    <?php echo sanitize($_SESSION['username']); ?> (Admin)
</div>

<div class="container">
    <div class="admin-header">
        <h1>Secure Admin Panel</h1>
        <p>Access controlled — Authenticated admins only</p>
    </div>

    <div class="grid2">

        <!-- User Search sécurisé -->
        <div class="card">
            <h3>User Search</h3>
            <form method="POST">
                <input type="hidden" name="csrf_token"
                       value="<?php echo $csrf_token; ?>">
                <div class="form-group">
                    <label>Search Username</label>
                    <input type="text" name="search_user"
                           maxlength="50" placeholder="Enter username...">
                </div>
                <button type="submit" class="btn">Search</button>
            </form>

            <?php if (count($search_results) > 0): ?>
                <table>
                    <tr><th>ID</th><th>Username</th><th>Email</th><th>Admin</th></tr>
                    <?php foreach ($search_results as $row): ?>
                    <tr>
                        <!-- ✅ sanitize() sur chaque champ + pas de mot de passe -->
                        <td><?php echo (int)$row['id']; ?></td>
                        <td><?php echo sanitize($row['username']); ?></td>
                        <td><?php echo sanitize($row['email']); ?></td>
                        <td><?php echo $row['is_admin'] ? '✅' : '❌'; ?></td>
                    </tr>
                    <?php endforeach; ?>
                </table>
            <?php endif; ?>

            <div class="security-info">
                <b>Fixes:</b>
                <ul>
                    <li>✅ Auth required (session + is_admin)</li>
                    <li>✅ Prepared Statement</li>
                    <li>✅ Password NOT displayed</li>
                    <li>✅ CSRF protection</li>
                </ul>
            </div>
        </div>

        <!-- Diagnostics sécurisé -->
        <div class="card">
            <h3>Server Diagnostics</h3>
            <form method="POST">
                <input type="hidden" name="csrf_token"
                       value="<?php echo $csrf_token; ?>">
                <div class="form-group">
                    <label>Ping Host (IP or hostname only)</label>
                    <input type="text" name="ping"
                           maxlength="253"
                           placeholder="e.g. google.com or 8.8.8.8"
                           pattern="[a-zA-Z0-9\-\.]+">
                </div>
                <button type="submit" class="btn">Execute</button>
            </form>

            <div class="terminal">
                <?php echo $ping_output ?: '$ Waiting for command...'; ?>
            </div>

            <div class="security-info">
                <b>Fixes:</b>
                <ul>
                    <li>✅ escapeshellarg() applied</li>
                    <li>✅ Input validation (IP/hostname only)</li>
                    <li>✅ Output sanitized with htmlspecialchars()</li>
                    <li>✅ No chaining operators allowed (; | &)</li>
                </ul>
            </div>
        </div>

    </div>
</div>

<footer><p>© 2026 VulnShop Secure Admin — DevSecOps PFE</p></footer>
</body>
</html>