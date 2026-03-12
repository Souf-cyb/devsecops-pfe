<?php
session_start();
require_once 'config.php';
?>
<!DOCTYPE html>
<html>
<head>
    <title>VulnShop - Online Store</title>
    <style>
        body { font-family: Arial; margin: 0; padding: 20px; background: #f0f0f0; }
        .header { background: #333; color: white; padding: 15px; }
        .nav a { color: white; margin: 10px; text-decoration: none; }
        .content { background: white; padding: 20px; margin-top: 20px; }
        .product { border: 1px solid #ddd; padding: 10px; margin: 10px; display: inline-block; }
        .btn { background: #007bff; color: white; padding: 8px 15px; border: none; cursor: pointer; }
    </style>
</head>
<body>
<div class="header">
    <h1>VulnShop</h1>
    <div class="nav">
        <a href="index.php">Home</a>
        <a href="search.php">Search</a>
        <a href="login.php">Login</a>
        <a href="upload.php">Upload</a>
        <a href="admin.php">Admin</a>
        <a href="api.php">API</a>
    </div>
</div>

<div class="content">
    <h2>Welcome to VulnShop</h2>

    <?php
    // Vulnérabilité : XSS reflected
    if (isset($_GET['msg'])) {
        echo "<div class='alert'>" . $_GET['msg'] . "</div>";
    }

    // Vulnérabilité : Inclusion de fichier local (LFI)
    if (isset($_GET['page'])) {
        include($_GET['page']);
    }

    // Vulnérabilité : Affichage d'informations sensibles
    if (DEBUG) {
        echo "<!-- Debug: Server IP: " . $_SERVER['SERVER_ADDR'] . " -->";
        echo "<!-- Debug: PHP Version: " . phpversion() . " -->";
    }
    ?>

    <h3>Featured Products</h3>
    <div class="product">
        <h4>Product 1</h4>
        <p>Price: $10.00</p>
        <a href="?id=1">View Details</a>
    </div>
    <div class="product">
        <h4>Product 2</h4>
        <p>Price: $20.00</p>
        <a href="?id=2">View Details</a>
    </div>

    <?php
    // Vulnérabilité : SQL Injection
    if (isset($_GET['id'])) {
        $id = $_GET['id'];
        $conn = getDB();
        $query = "SELECT * FROM products WHERE id=" . $id;
        $result = mysqli_query($conn, $query);
        if ($result) {
            while ($row = mysqli_fetch_assoc($result)) {
                echo "<div class='product'>";
                echo "<h4>" . $row['name'] . "</h4>";
                echo "<p>" . $row['description'] . "</p>";
                echo "</div>";
            }
        }
    }
    ?>
</div>
</body>
</html>