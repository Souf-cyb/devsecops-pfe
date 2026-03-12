<?php
session_start();
require_once 'config.php';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VulnShop - Online Store</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', sans-serif; background: #f5f5f5; }

        /* NAVBAR */
        .navbar {
            background: linear-gradient(135deg, #1a1a2e, #16213e);
            padding: 15px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.3);
        }
        .navbar .logo {
            color: #e94560;
            font-size: 24px;
            font-weight: bold;
            text-decoration: none;
        }
        .navbar .logo span { color: white; }
        .nav-links a {
            color: #ccc;
            text-decoration: none;
            margin-left: 25px;
            font-size: 14px;
            transition: color 0.3s;
        }
        .nav-links a:hover { color: #e94560; }

        /* HERO */
        .hero {
            background: linear-gradient(135deg, #1a1a2e, #16213e, #0f3460);
            color: white;
            padding: 80px 30px;
            text-align: center;
        }
        .hero h1 { font-size: 48px; margin-bottom: 15px; }
        .hero h1 span { color: #e94560; }
        .hero p { font-size: 18px; color: #aaa; margin-bottom: 30px; }
        .hero .btn {
            background: #e94560;
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 25px;
            font-size: 16px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            transition: transform 0.3s;
        }
        .hero .btn:hover { transform: scale(1.05); }

        /* ALERT */
        .alert {
            background: #fff3cd;
            border: 1px solid #ffc107;
            padding: 12px 20px;
            margin: 20px 30px;
            border-radius: 5px;
            color: #856404;
        }

        /* PRODUCTS */
        .section { padding: 50px 30px; }
        .section h2 {
            font-size: 28px;
            margin-bottom: 30px;
            color: #1a1a2e;
            border-left: 4px solid #e94560;
            padding-left: 15px;
        }
        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 25px;
        }
        .product-card {
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 3px 15px rgba(0,0,0,0.1);
            transition: transform 0.3s;
        }
        .product-card:hover { transform: translateY(-5px); }
        .product-card .product-img {
            background: linear-gradient(135deg, #667eea, #764ba2);
            height: 180px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 60px;
        }
        .product-card .product-info { padding: 20px; }
        .product-card h3 { font-size: 18px; margin-bottom: 8px; color: #1a1a2e; }
        .product-card p { color: #666; font-size: 14px; margin-bottom: 15px; }
        .product-card .price { color: #e94560; font-size: 20px; font-weight: bold; }
        .product-card .btn-buy {
            background: #1a1a2e;
            color: white;
            border: none;
            padding: 8px 20px;
            border-radius: 20px;
            cursor: pointer;
            font-size: 14px;
            margin-top: 10px;
            text-decoration: none;
            display: inline-block;
            transition: background 0.3s;
        }
        .product-card .btn-buy:hover { background: #e94560; }

        /* FOOTER */
        footer {
            background: #1a1a2e;
            color: #aaa;
            text-align: center;
            padding: 30px;
            margin-top: 50px;
        }
        footer span { color: #e94560; }
    </style>
</head>
<body>

<!-- NAVBAR -->
<nav class="navbar">
    <a class="logo" href="index.php">Vuln<span>Shop</span></a>
    <div class="nav-links">
        <a href="index.php">🏠 Home</a>
        <a href="search.php">🔍 Search</a>
        <a href="upload.php">📁 Upload</a>
        <a href="admin.php?admin=true">⚙️ Admin</a>
        <a href="api.php">🔌 API</a>
        <?php if(isset($_SESSION['username'])): ?>
            <a href="dashboard.php">👤 <?php echo $_SESSION['username']; ?></a>
        <?php else: ?>
            <a href="login.php">🔐 Login</a>
        <?php endif; ?>
    </div>
</nav>

<!-- HERO -->
<div class="hero">
    <h1>Welcome to <span>VulnShop</span></h1>
    <p>Your favorite online store — totally not vulnerable 😉</p>
    <a href="search.php" class="btn">Shop Now</a>
</div>

<!-- ALERT XSS -->
<?php if (isset($_GET['msg'])): ?>
    <div class="alert">
        <?php echo $_GET['msg']; // Vulnérabilité XSS intentionnelle ?>
    </div>
<?php endif; ?>

<!-- LFI -->
<?php if (isset($_GET['page'])): ?>
    <?php include($_GET['page']); // Vulnérabilité LFI intentionnelle ?>
<?php endif; ?>

<!-- PRODUCTS -->
<div class="section">
    <h2>🛍️ Featured Products</h2>
    <div class="products-grid">

        <div class="product-card">
            <div class="product-img">💻</div>
            <div class="product-info">
                <h3>Laptop Pro X</h3>
                <p>High performance laptop for professionals</p>
                <div class="price">$999.99</div>
                <a href="?id=1" class="btn-buy">View Details</a>
            </div>
        </div>

        <div class="product-card">
            <div class="product-img">📱</div>
            <div class="product-info">
                <h3>SmartPhone Z</h3>
                <p>Latest smartphone with amazing features</p>
                <div class="price">$699.99</div>
                <a href="?id=2" class="btn-buy">View Details</a>
            </div>
        </div>

        <div class="product-card">
            <div class="product-img">🎮</div>
            <div class="product-info">
                <h3>Gaming Console</h3>
                <p>Next generation gaming experience</p>
                <div class="price">$499.99</div>
                <a href="?id=3" class="btn-buy">View Details</a>
            </div>
        </div>

        <div class="product-card">
            <div class="product-img">⌚</div>
            <div class="product-info">
                <h3>Smart Watch</h3>
                <p>Track your health and stay connected</p>
                <div class="price">$299.99</div>
                <a href="?id=4" class="btn-buy">View Details</a>
            </div>
        </div>

        <div class="product-card">
            <div class="product-img">🎧</div>
            <div class="product-info">
                <h3>Pro Headphones</h3>
                <p>Crystal clear sound quality</p>
                <div class="price">$199.99</div>
                <a href="?id=5" class="btn-buy">View Details</a>
            </div>
        </div>

        <div class="product-card">
            <div class="product-img">📷</div>
            <div class="product-info">
                <h3>DSLR Camera</h3>
                <p>Professional photography made easy</p>
                <div class="price">$1299.99</div>
                <a href="?id=6" class="btn-buy">View Details</a>
            </div>
        </div>

    </div>

    <?php
    // Vulnérabilité : SQL Injection intentionnelle
    if (isset($_GET['id'])) {
        $id = $_GET['id'];
        $conn = getDB();
        $query = "SELECT * FROM products WHERE id=" . $id;
        $result = mysqli_query($conn, $query);
        if ($result) {
            echo "<div style='margin-top:30px; padding:20px; background:white; border-radius:10px;'>";
            echo "<h3>Product Details</h3>";
            while ($row = mysqli_fetch_assoc($result)) {
                echo "<p><b>Name:</b> " . $row['name'] . "</p>";
                echo "<p><b>Description:</b> " . $row['description'] . "</p>";
                echo "<p><b>Price:</b> $" . $row['price'] . "</p>";
            }
            echo "</div>";
        }
    }
    ?>
</div>

<!-- FOOTER -->
<footer>
    <p>© 2024 <span>VulnShop</span> — Built for DevSecOps PFE demonstration</p>
    <p style="margin-top:10px; font-size:12px;">⚠️ This application is intentionally vulnerable for security testing purposes</p>
</footer>

</body>
</html>