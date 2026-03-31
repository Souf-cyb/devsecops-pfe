<?php
session_start();
require_once 'config.php';

// Vulnérabilité : Bypass avec ?admin=true
$authorized = isset($_GET['admin']) && $_GET['admin'] === 'true';

$conn = getDB();
$search_results = [];

if (isset($_POST['search_user'])) {
    $search = $_POST['search_user'];
    // Vulnérabilité : SQL Injection
    $query = "SELECT id, username, email, password, is_admin FROM users WHERE username LIKE '%" . $search . "%'";
    $result = mysqli_query($conn, $query);
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $search_results[] = $row;
        }
    }
}

$ping_output = '';
if (isset($_POST['ping'])) {
    $host = $_POST['ping'];
    // Vulnérabilité : Command Injection
    $ping_output = shell_exec("ping -c 2 " . $host);
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Admin Panel - VulnShop</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', sans-serif; background: #f5f5f5; }
        .navbar {
            background: linear-gradient(135deg, #1a1a2e, #16213e);
            padding: 15px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .navbar .logo { color: #e94560; font-size: 24px; font-weight: bold; text-decoration: none; }
        .navbar .logo span { color: white; }
        .nav-links a { color: #ccc; text-decoration: none; margin-left: 25px; }

        .container { max-width: 1200px; margin: 30px auto; padding: 0 20px; }

        .access-denied {
            background: #f8d7da;
            border: 2px solid #e94560;
            padding: 20px;
            border-radius: 10px;
            text-align: center;
            margin-bottom: 20px;
        }
        .access-denied p { color: #721c24; margin-bottom: 10px; }
        .access-denied code {
            background: #fff;
            padding: 5px 10px;
            border-radius: 5px;
            font-size: 14px;
        }

        .admin-header {
            background: linear-gradient(135deg, #e94560, #c0392b);
            color: white;
            padding: 30px;
            border-radius: 10px;
            margin-bottom: 30px;
        }
        .admin-header h1 { font-size: 28px; }
        .admin-header p { opacity: 0.8; margin-top: 5px; }

        .admin-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 25px;
        }
        .admin-card {
            background: white;
            border-radius: 10px;
            padding: 25px;
            box-shadow: 0 3px 15px rgba(0,0,0,0.1);
        }
        .admin-card h3 {
            color: #1a1a2e;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #e94560;
        }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; color: #555; font-size: 14px; }
        .form-group input {
            width: 100%;
            padding: 10px 15px;
            border: 2px solid #eee;
            border-radius: 8px;
            font-size: 14px;
        }
        .form-group input:focus { outline: none; border-color: #e94560; }
        .btn {
            padding: 10px 25px;
            background: #e94560;
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 14px;
        }
        .btn:hover { background: #c0392b; }
        .btn-dark {
            padding: 10px 25px;
            background: #1a1a2e;
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 14px;
        }

        .results-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
            font-size: 13px;
        }
        .results-table th {
            background: #1a1a2e;
            color: white;
            padding: 8px 12px;
            text-align: left;
        }
        .results-table td {
            padding: 8px 12px;
            border-bottom: 1px solid #eee;
        }
        .results-table tr:hover { background: #f8f9fa; }

        .terminal {
            background: #1a1a2e;
            color: #00ff00;
            padding: 15px;
            border-radius: 8px;
            font-family: monospace;
            font-size: 13px;
            margin-top: 15px;
            white-space: pre-wrap;
            min-height: 100px;
        }

        footer {
            background: #1a1a2e;
            color: #aaa;
            text-align: center;
            padding: 20px;
            margin-top: 50px;
        }
    </style>
</head>
<body>

<nav class="navbar">
    <a class="logo" href="index.php">Vuln<span>Shop</span></a>
    <div class="nav-links">
        <a href="index.php" style="color:#ccc">🏠 Home</a>
        <a href="api.php" style="color:#ccc">🔌 API</a>
    </div>
</nav>

<div class="container">

    <?php if (!$authorized): ?>
    <div class="access-denied">
        <p>⛔ <b>Access Denied</b> — You need admin privileges</p>
        <p>💡 Hint: Try adding <code>?admin=true</code> to the URL</p>
    </div>
    <?php endif; ?>

    <div class="admin-header">
        <h1>⚙️ Admin Panel</h1>
        <p>VulnShop Administration Dashboard</p>
    </div>

    <div class="admin-grid">

        <!-- User Search -->
        <div class="admin-card">
            <h3>👥 User Search</h3>
            <form method="POST">
                <div class="form-group">
                    <label>Search Username</label>
                    <input type="text" name="search_user" placeholder="Enter username...">
                </div>
                <button type="submit" class="btn">🔍 Search</button>
            </form>

            <?php if (count($search_results) > 0): ?>
                <table class="results-table">
                    <tr>
                        <th>ID</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Password</th>
                        <th>Admin</th>
                    </tr>
                    <?php foreach ($search_results as $row): ?>
                    <tr>
                        <!-- Vulnérabilité : Affichage des mots de passe + XSS -->
                        <td><?php echo $row['id']; ?></td>
                        <td><?php echo $row['username']; ?></td>
                        <td><?php echo $row['email']; ?></td>
                        <td style="color:#e94560"><?php echo $row['password']; ?></td>
                        <td><?php echo $row['is_admin'] ? '✅' : '❌'; ?></td>
                    </tr>
                    <?php endforeach; ?>
                </table>
            <?php endif; ?>
        </div>

        <!-- Command Injection -->
        <div class="admin-card">
            <h3>🖥️ Server Diagnostics</h3>
            <form method="POST">
                <div class="form-group">
                    <label>Ping Host</label>
                    <input type="text" name="ping" placeholder="e.g. google.com or 127.0.0.1; whoami">
                </div>
                <button type="submit" class="btn-dark">▶️ Execute</button>
            </form>

            <?php if ($ping_output): ?>
                <!-- Vulnérabilité : XSS + Command Injection -->
                <div class="terminal"><?php echo $ping_output; ?></div>
            <?php else: ?>
                <div class="terminal">$ Waiting for command...</div>
            <?php endif; ?>
        </div>

    </div>
</div>

<footer>
    <p>© 2026 VulnShop Admin — DevSecOps PFE</p>
</footer>

</body>
</html>