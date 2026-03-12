<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

$conn = getDB();

// Vulnérabilité : IDOR
$target_user = null;
if (isset($_GET['user_id'])) {
    $user_id = $_GET['user_id'];
    // Pas de vérification que l'ID appartient à l'utilisateur connecté
    $query = "SELECT * FROM users WHERE id=" . $user_id;
    $result = mysqli_query($conn, $query);
    $target_user = mysqli_fetch_assoc($result);
}

// Récupérer le profil actuel
$query = "SELECT * FROM users WHERE username='" . $_SESSION['username'] . "'";
$result = mysqli_query($conn, $query);
$current_user = mysqli_fetch_assoc($result);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Dashboard - VulnShop</title>
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

        .container { max-width: 900px; margin: 40px auto; padding: 0 20px; }

        .welcome-card {
            background: linear-gradient(135deg, #1a1a2e, #16213e);
            color: white;
            padding: 30px;
            border-radius: 15px;
            margin-bottom: 25px;
        }
        .welcome-card h2 { font-size: 24px; }
        .welcome-card p { opacity: 0.7; margin-top: 5px; }

        .cards-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 25px;
        }
        .info-card {
            background: white;
            border-radius: 10px;
            padding: 25px;
            box-shadow: 0 3px 15px rgba(0,0,0,0.1);
        }
        .info-card h3 {
            color: #1a1a2e;
            margin-bottom: 15px;
            border-bottom: 2px solid #e94560;
            padding-bottom: 10px;
        }
        .info-item {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #f0f0f0;
            font-size: 14px;
        }
        .info-item label { color: #666; }
        .info-item span { color: #1a1a2e; font-weight: bold; }
        .info-item .sensitive { color: #e94560; }

        .idor-card {
            background: white;
            border-radius: 10px;
            padding: 25px;
            box-shadow: 0 3px 15px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        .idor-card h3 {
            color: #1a1a2e;
            margin-bottom: 15px;
            border-bottom: 2px solid #e94560;
            padding-bottom: 10px;
        }
        .idor-form { display: flex; gap: 10px; }
        .idor-form input {
            flex: 1;
            padding: 10px 15px;
            border: 2px solid #eee;
            border-radius: 8px;
        }
        .idor-form button {
            padding: 10px 20px;
            background: #e94560;
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
        }
        .user-data {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin-top: 15px;
            font-size: 13px;
        }

        .danger-zone {
            background: #fff5f5;
            border: 2px solid #e94560;
            border-radius: 10px;
            padding: 25px;
        }
        .danger-zone h3 { color: #e94560; margin-bottom: 15px; }
        .btn-danger {
            padding: 10px 20px;
            background: #e94560;
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
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
        <a href="index.php">🏠 Home</a>
        <a href="search.php">🔍 Search</a>
        <a href="login.php">🔐 Logout</a>
    </div>
</nav>

<div class="container">

    <div class="welcome-card">
        <h2>👤 Welcome back, <?php echo $_SESSION['username']; ?>!</h2>
        <p>Your personal dashboard — handle with care</p>
    </div>

    <div class="cards-grid">
        <div class="info-card">
            <h3>👤 Profile Information</h3>
            <?php if ($current_user): ?>
                <div class="info-item">
                    <label>Username</label>
                    <span><?php echo $current_user['username']; ?></span>
                </div>
                <div class="info-item">
                    <label>Email</label>
                    <span><?php echo $current_user['email']; ?></span>
                </div>
                <!-- Vulnérabilité : Affichage du mot de passe -->
                <div class="info-item">
                    <label>Password</label>
                    <span class="sensitive"><?php echo $current_user['password']; ?></span>
                </div>
                <div class="info-item">
                    <label>Admin</label>
                    <span><?php echo $current_user['is_admin'] ? '✅ Yes' : '❌ No'; ?></span>
                </div>
            <?php endif; ?>
        </div>

        <div class="info-card">
            <h3>📊 Account Stats</h3>
            <div class="info-item">
                <label>Orders</label>
                <span>12</span>
            </div>
            <div class="info-item">
                <label>Wishlist</label>
                <span>5 items</span>
            </div>
            <div class="info-item">
                <label>Total Spent</label>
                <span>$2,450.00</span>
            </div>
            <div class="info-item">
                <label>Member Since</label>
                <span>2023</span>
            </div>
        </div>
    </div>

    <!-- IDOR Demo -->
    <div class="idor-card">
        <h3>🔍 View User Profile (IDOR Vulnerability)</h3>
        <p style="color:#666; font-size:14px; margin-bottom:15px;">
            ⚠️ Try changing the user ID to access other users' data!
        </p>
        <form method="GET" class="idor-form">
            <input type="number" name="user_id"
                   value="<?php echo $_GET['user_id'] ?? '1'; ?>"
                   placeholder="User ID">
            <button type="submit">View Profile</button>
        </form>

        <?php if ($target_user): ?>
            <div class="user-data">
                <!-- Vulnérabilité : IDOR + XSS -->
                <pre><?php echo print_r($target_user, true); ?></pre>
            </div>
        <?php endif; ?>
    </div>

    <!-- Danger Zone -->
    <div class="danger-zone">
        <h3>⚠️ Danger Zone</h3>
        <!-- Vulnérabilité : CSRF - pas de token -->
        <form method="POST" action="delete_account.php">
            <input type="hidden" name="user_id"
                   value="<?php echo $current_user['id'] ?? 1; ?>">
            <button type="submit" class="btn-danger">🗑️ Delete Account</button>
        </form>
    </div>

</div>

<footer>
    <p>© 2024 VulnShop — DevSecOps PFE</p>
</footer>

</body>
</html>