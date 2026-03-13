<?php
session_start();
require_once 'config_secure.php';
set_security_headers();

$results = [];
$search  = '';
$error   = '';

if (isset($_GET['q'])) {
    $search = sanitize($_GET['q']); // ✅ Sanitisation

    // ✅ Validation longueur
    if (strlen($search) > 100) {
        $error = "Search query too long.";
    } elseif (strlen($search) >= 2) {
        $conn = getDB();

        // ✅ Prepared Statement — plus de SQLi
        $stmt = $conn->prepare(
            "SELECT id, name, description, price FROM products
             WHERE name LIKE ? OR description LIKE ?
             LIMIT 20"
        );
        $like = "%" . $search . "%";
        $stmt->bind_param("ss", $like, $like);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $results[] = $row;
        }
        $stmt->close();
    }
}

// ✅ Suppression de eval() — plus d'injection de code
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Secure Search - VulnShop</title>
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family:'Segoe UI',sans-serif; background:#f5f5f5; }
        .navbar { background:linear-gradient(135deg,#1a1a2e,#16213e); padding:15px 30px; display:flex; justify-content:space-between; align-items:center; }
        .navbar .logo { color:#27ae60; font-size:24px; font-weight:bold; text-decoration:none; }
        .navbar .logo span { color:white; }
        .nav-links a { color:#ccc; text-decoration:none; margin-left:25px; font-size:14px; }
        .nav-links a:hover { color:#27ae60; }
        .secure-bar { background:#eafaf1; border-bottom:1px solid #a9dfbf; padding:8px 30px; font-size:12px; color:#27ae60; }
        .search-hero { background:linear-gradient(135deg,#1a1a2e,#16213e); padding:40px 30px; text-align:center; }
        .search-box { display:flex; max-width:600px; margin:0 auto; gap:10px; }
        .search-box input { flex:1; padding:15px 20px; border:none; border-radius:30px 0 0 30px; font-size:16px; }
        .search-box button { padding:15px 30px; background:#27ae60; color:white; border:none; border-radius:0 30px 30px 0; cursor:pointer; font-size:16px; }
        .results { padding:30px; }
        .result-info { background:#eafaf1; border:1px solid #a9dfbf; padding:10px 20px; border-radius:8px; margin-bottom:20px; color:#27ae60; font-size:13px; }
        .results-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(250px,1fr)); gap:20px; }
        .result-card { background:white; padding:20px; border-radius:10px; box-shadow:0 3px 10px rgba(0,0,0,0.1); }
        .result-card h3 { color:#1a1a2e; margin-bottom:8px; }
        .result-card p { color:#666; font-size:14px; }
        .result-card .price { color:#27ae60; font-weight:bold; margin-top:10px; }
        .error-box { background:#ffe0e0; border:1px solid #e74c3c; padding:12px 20px; border-radius:8px; color:#c0392b; margin:20px 30px; }
        footer { background:#1a1a2e; color:#aaa; text-align:center; padding:20px; margin-top:50px; }
    </style>
</head>
<body>
<nav class="navbar">
    <a class="logo" href="index_secure.php">Vuln<span>Shop</span> Secure</a>
    <div class="nav-links">
        <a href="index_secure.php">Home</a>
        <a href="search_secure.php">Search</a>
        <a href="login_secure.php">Login</a>
    </div>
</nav>
<div class="secure-bar">✅ Secure version — SQL Injection, XSS and Code Injection fixed</div>

<div class="search-hero">
    <form method="GET">
        <div class="search-box">
            <!-- ✅ sanitize() sur la valeur affichée -->
            <input type="text" name="q"
                   value="<?php echo $search; ?>"
                   placeholder="Search products..."
                   maxlength="100">
            <button type="submit">Search</button>
        </div>
    </form>
</div>

<?php if ($error): ?>
    <div class="error-box"><?php echo $error; ?></div>
<?php endif; ?>

<div class="results">
    <?php if ($search && !$error): ?>
        <!-- ✅ sanitize() — plus de XSS -->
        <div class="result-info">
            Search results for: <b><?php echo $search; ?></b>
            (<?php echo count($results); ?> results)
        </div>
        <?php if (count($results) > 0): ?>
            <div class="results-grid">
                <?php foreach ($results as $product): ?>
                    <div class="result-card">
                        <!-- ✅ sanitize() sur chaque donnée affichée -->
                        <h3><?php echo sanitize($product['name']); ?></h3>
                        <p><?php echo sanitize($product['description']); ?></p>
                        <div class="price">
                            $<?php echo number_format((float)$product['price'], 2); ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p style="text-align:center;color:#999;padding:40px">
                No results found for "<?php echo $search; ?>"
            </p>
        <?php endif; ?>
    <?php endif; ?>
</div>

<footer><p>© 2024 VulnShop Secure — DevSecOps PFE</p></footer>
</body>
</html>