<?php
session_start();
require_once 'config.php';

$results = [];
$search = '';

if (isset($_GET['q'])) {
    $search = $_GET['q'];
    $conn = getDB();
    // Vulnérabilité : SQL Injection
    $query = "SELECT * FROM products WHERE name LIKE '%" . $search . "%'";
    $result = mysqli_query($conn, $query);
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $results[] = $row;
        }
    }
}

// Vulnérabilité : eval() avec entrée utilisateur
if (isset($_GET['filter'])) {
    eval($_GET['filter']);
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Search - VulnShop</title>
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
        .nav-links a { color: #ccc; text-decoration: none; margin-left: 25px; font-size: 14px; }
        .nav-links a:hover { color: #e94560; }

        .search-hero {
            background: linear-gradient(135deg, #1a1a2e, #16213e);
            padding: 40px 30px;
            text-align: center;
        }
        .search-box {
            display: flex;
            max-width: 600px;
            margin: 0 auto;
            gap: 10px;
        }
        .search-box input {
            flex: 1;
            padding: 15px 20px;
            border: none;
            border-radius: 30px 0 0 30px;
            font-size: 16px;
        }
        .search-box button {
            padding: 15px 30px;
            background: #e94560;
            color: white;
            border: none;
            border-radius: 0 30px 30px 0;
            cursor: pointer;
            font-size: 16px;
        }

        .results { padding: 30px; }
        .results h2 { color: #1a1a2e; margin-bottom: 20px; }
        .result-info {
            background: #fff3cd;
            padding: 10px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            color: #856404;
        }
        .results-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
        }
        .result-card {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
        }
        .result-card h3 { color: #1a1a2e; margin-bottom: 10px; }
        .result-card p { color: #666; font-size: 14px; }
        .result-card .price { color: #e94560; font-weight: bold; margin-top: 10px; }
        .no-results {
            text-align: center;
            padding: 50px;
            color: #999;
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
        <a href="login.php">🔐 Login</a>
        <a href="admin.php?admin=true">⚙️ Admin</a>
    </div>
</nav>

<div class="search-hero">
    <form method="GET">
        <div class="search-box">
            <input type="text"
                   name="q"
                   value="<?php echo $_GET['q'] ?? ''; ?>"
                   placeholder="Search products...">
            <button type="submit">🔍 Search</button>
        </div>
    </form>
</div>

<div class="results">
    <?php if ($search): ?>
        <!-- Vulnérabilité : XSS - affichage direct -->
        <div class="result-info">
            🔍 Search results for: <b><?php echo $search; ?></b>
        </div>

        <?php if (count($results) > 0): ?>
            <div class="results-grid">
                <?php foreach ($results as $product): ?>
                    <div class="result-card">
                        <h3><?php echo $product['name']; ?></h3>
                        <p><?php echo $product['description']; ?></p>
                        <div class="price">$<?php echo $product['price']; ?></div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="no-results">
                <p style="font-size:50px">🔍</p>
                <p>No results found for "<?php echo $search; ?>"</p>
            </div>
        <?php endif; ?>
    <?php else: ?>
        <div class="no-results">
            <p style="font-size:50px">🛍️</p>
            <p>Search for your favorite products</p>
        </div>
    <?php endif; ?>
</div>

<footer>
    <p>© 2024 VulnShop — DevSecOps PFE</p>
</footer>

</body>
</html>