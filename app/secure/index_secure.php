<?php
session_start();
require_once 'config_secure.php';
set_security_headers();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VulnShop Secure - Online Store</title>
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family:'Segoe UI',sans-serif; background:#f5f5f5; }

        /* NAVBAR */
        .navbar {
            background:linear-gradient(135deg,#1a1a2e,#16213e);
            padding:15px 30px;
            display:flex;
            justify-content:space-between;
            align-items:center;
            box-shadow:0 2px 10px rgba(0,0,0,0.3);
        }
        .navbar .logo { color:#27ae60; font-size:24px; font-weight:bold; text-decoration:none; }
        .navbar .logo span { color:white; }
        .nav-links a { color:#ccc; text-decoration:none; margin-left:25px; font-size:14px; transition:color 0.3s; }
        .nav-links a:hover { color:#27ae60; }

        /* SECURE BAR */
        .secure-bar {
            background:#eafaf1;
            border-bottom:1px solid #a9dfbf;
            padding:8px 30px;
            font-size:12px;
            color:#27ae60;
            display:flex;
            justify-content:space-between;
            align-items:center;
        }
        .secure-bar .fixes { display:flex; gap:15px; }
        .fix-badge {
            background:#27ae60;
            color:white;
            padding:2px 10px;
            border-radius:20px;
            font-size:11px;
        }

        /* HERO */
        .hero {
            background:linear-gradient(135deg,#1a3a2e,#16532e,#0f3a20);
            color:white;
            padding:80px 30px;
            text-align:center;
        }
        .hero h1 { font-size:48px; margin-bottom:15px; }
        .hero h1 span { color:#27ae60; }
        .hero p { font-size:18px; color:#aaa; margin-bottom:10px; }
        .hero .secure-note {
            background:rgba(39,174,96,0.2);
            border:1px solid #27ae60;
            color:#27ae60;
            padding:8px 20px;
            border-radius:20px;
            font-size:13px;
            display:inline-block;
            margin-bottom:25px;
        }
        .hero .btn {
            background:#27ae60;
            color:white;
            padding:12px 30px;
            border:none;
            border-radius:25px;
            font-size:16px;
            cursor:pointer;
            text-decoration:none;
            display:inline-block;
            transition:transform 0.3s;
        }
        .hero .btn:hover { transform:scale(1.05); background:#219a52; }

        /* COMPARISON BANNER */
        .comparison {
            background:white;
            margin:20px 30px;
            border-radius:12px;
            padding:20px 25px;
            box-shadow:0 3px 12px rgba(0,0,0,0.08);
            display:grid;
            grid-template-columns:1fr auto 1fr;
            gap:20px;
            align-items:center;
        }
        .comp-vuln { border-left:4px solid #e94560; padding-left:15px; }
        .comp-secure { border-left:4px solid #27ae60; padding-left:15px; }
        .comp-title { font-size:12px; color:#888; margin-bottom:8px; }
        .comp-num { font-size:28px; font-weight:bold; }
        .comp-sub { font-size:12px; color:#888; margin-top:4px; }
        .comp-arrow { font-size:30px; color:#27ae60; text-align:center; }
        .comp-badge {
            display:inline-block;
            background:#eafaf1;
            color:#27ae60;
            padding:3px 10px;
            border-radius:20px;
            font-size:11px;
            margin-top:6px;
        }

        /* PRODUCTS */
        .section { padding:40px 30px; }
        .section h2 {
            font-size:26px;
            margin-bottom:25px;
            color:#1a1a2e;
            border-left:4px solid #27ae60;
            padding-left:15px;
        }
        .products-grid {
            display:grid;
            grid-template-columns:repeat(auto-fill,minmax(250px,1fr));
            gap:25px;
        }
        .product-card {
            background:white;
            border-radius:10px;
            overflow:hidden;
            box-shadow:0 3px 15px rgba(0,0,0,0.1);
            transition:transform 0.3s;
        }
        .product-card:hover { transform:translateY(-5px); }
        .product-card .product-img {
            background:linear-gradient(135deg,#1a3a2e,#16532e);
            height:160px;
            display:flex;
            align-items:center;
            justify-content:center;
            font-size:55px;
        }
        .product-card .product-info { padding:18px; }
        .product-card h3 { font-size:16px; margin-bottom:6px; color:#1a1a2e; }
        .product-card p { color:#666; font-size:13px; margin-bottom:12px; }
        .product-card .price { color:#27ae60; font-size:20px; font-weight:bold; }
        .product-card .btn-buy {
            background:#1a1a2e;
            color:white;
            border:none;
            padding:8px 18px;
            border-radius:20px;
            cursor:pointer;
            font-size:13px;
            margin-top:10px;
            text-decoration:none;
            display:inline-block;
            transition:background 0.3s;
        }
        .product-card .btn-buy:hover { background:#27ae60; }

        /* SECURITY FIXES SECTION */
        .fixes-section {
            background:#1a1a2e;
            padding:40px 30px;
            color:white;
        }
        .fixes-section h2 {
            font-size:24px;
            margin-bottom:25px;
            color:#27ae60;
        }
        .fixes-grid {
            display:grid;
            grid-template-columns:repeat(auto-fill,minmax(280px,1fr));
            gap:20px;
        }
        .fix-card {
            background:rgba(255,255,255,0.05);
            border:1px solid rgba(39,174,96,0.3);
            border-radius:10px;
            padding:20px;
        }
        .fix-card h4 { color:#27ae60; margin-bottom:8px; font-size:14px; }
        .fix-card .before {
            background:rgba(233,69,96,0.1);
            border-left:3px solid #e94560;
            padding:8px 12px;
            border-radius:0 5px 5px 0;
            font-family:monospace;
            font-size:12px;
            color:#ff8888;
            margin-bottom:8px;
        }
        .fix-card .after {
            background:rgba(39,174,96,0.1);
            border-left:3px solid #27ae60;
            padding:8px 12px;
            border-radius:0 5px 5px 0;
            font-family:monospace;
            font-size:12px;
            color:#88ff88;
        }

        /* FOOTER */
        footer {
            background:#1a1a2e;
            color:#aaa;
            text-align:center;
            padding:25px;
            border-top:1px solid rgba(39,174,96,0.3);
        }
        footer span { color:#27ae60; }
    </style>
</head>
<body>

<!-- NAVBAR -->
<nav class="navbar">
    <a class="logo" href="index_secure.php">Vuln<span>Shop</span> Secure</a>
    <div class="nav-links">
        <a href="index_secure.php">Home</a>
        <a href="search_secure.php">Search</a>
        <a href="upload_secure.php">Upload</a>
        <a href="admin_secure.php">Admin</a>
        <a href="api_secure.php">API</a>
        <?php if(isset($_SESSION['username'])): ?>
            <a href="dashboard_secure.php">
                <?php echo sanitize($_SESSION['username']); ?>
            </a>
        <?php else: ?>
            <a href="login_secure.php">Login</a>
        <?php endif; ?>
    </div>
</nav>

<!-- SECURE BAR -->
<div class="secure-bar">
    <span>✅ Secured Version — All OWASP Top 10 vulnerabilities fixed</span>
    <div class="fixes">
        <span class="fix-badge">SQLi Fixed</span>
        <span class="fix-badge">XSS Fixed</span>
        <span class="fix-badge">CMDi Fixed</span>
        <span class="fix-badge">CSRF Fixed</span>
        <span class="fix-badge">IDOR Fixed</span>
    </div>
</div>

<!-- HERO -->
<div class="hero">
    <h1>Welcome to <span>VulnShop</span> Secure</h1>
    <p>Same application — All vulnerabilities fixed</p>
    <div class="secure-note">
        ✅ Secured with OWASP best practices
    </div>
    <br>
    <a href="search_secure.php" class="btn">Shop Securely</a>
</div>

<!-- COMPARISON BANNER -->
<div class="comparison">
    <div class="comp-vuln">
        <div class="comp-title">❌ Vulnerable Version (port 80)</div>
        <div class="comp-num" style="color:#e94560">100+</div>
        <div class="comp-sub">vulnerabilities detected</div>
        <span class="comp-badge" style="background:#fff5f5;color:#e94560">
            SQLi · XSS · CMDi · CSRF · IDOR
        </span>
    </div>
    <div class="comp-arrow">→</div>
    <div class="comp-secure">
        <div class="comp-title">✅ Secure Version (port 8080)</div>
        <div class="comp-num" style="color:#27ae60">~5</div>
        <div class="comp-sub">findings remaining (headers only)</div>
        <span class="comp-badge">
            All critical vulnerabilities fixed
        </span>
    </div>
</div>

<!-- PRODUCTS -->
<div class="section">
    <h2>Featured Products</h2>
    <div class="products-grid">

        <div class="product-card">
            <div class="product-img">💻</div>
            <div class="product-info">
                <h3>Laptop Pro X</h3>
                <p>High performance laptop for professionals</p>
                <div class="price">$999.99</div>
                <a href="search_secure.php?q=laptop" class="btn-buy">View Details</a>
            </div>
        </div>

        <div class="product-card">
            <div class="product-img">📱</div>
            <div class="product-info">
                <h3>SmartPhone Z</h3>
                <p>Latest smartphone with amazing features</p>
                <div class="price">$699.99</div>
                <a href="search_secure.php?q=smartphone" class="btn-buy">View Details</a>
            </div>
        </div>

        <div class="product-card">
            <div class="product-img">🎮</div>
            <div class="product-info">
                <h3>Gaming Console</h3>
                <p>Next generation gaming experience</p>
                <div class="price">$499.99</div>
                <a href="search_secure.php?q=gaming" class="btn-buy">View Details</a>
            </div>
        </div>

        <div class="product-card">
            <div class="product-img">⌚</div>
            <div class="product-info">
                <h3>Smart Watch</h3>
                <p>Track your health and stay connected</p>
                <div class="price">$299.99</div>
                <a href="search_secure.php?q=watch" class="btn-buy">View Details</a>
            </div>
        </div>

        <div class="product-card">
            <div class="product-img">🎧</div>
            <div class="product-info">
                <h3>Pro Headphones</h3>
                <p>Crystal clear sound quality</p>
                <div class="price">$199.99</div>
                <a href="search_secure.php?q=headphones" class="btn-buy">View Details</a>
            </div>
        </div>

        <div class="product-card">
            <div class="product-img">📷</div>
            <div class="product-info">
                <h3>DSLR Camera</h3>
                <p>Professional photography made easy</p>
                <div class="price">$1299.99</div>
                <a href="search_secure.php?q=camera" class="btn-buy">View Details</a>
            </div>
        </div>

    </div>
</div>

<!-- SECURITY FIXES SECTION -->
<div class="fixes-section">
    <h2>Security Fixes Applied</h2>
    <div class="fixes-grid">

        <div class="fix-card">
            <h4>SQL Injection → Fixed</h4>
            <div class="before">
                // BEFORE ❌<br>
                $q = "SELECT * FROM users<br>
                WHERE user='".$_POST['u']."'"
            </div>
            <div class="after">
                // AFTER ✅<br>
                $stmt = $conn->prepare(<br>
                &nbsp;"SELECT * FROM users WHERE user=?"<br>
                );<br>
                $stmt->bind_param("s", $u);
            </div>
        </div>

        <div class="fix-card">
            <h4>XSS → Fixed</h4>
            <div class="before">
                // BEFORE ❌<br>
                echo $_GET['msg'];
            </div>
            <div class="after">
                // AFTER ✅<br>
                echo htmlspecialchars(<br>
                &nbsp;$_GET['msg'],<br>
                &nbsp;ENT_QUOTES, 'UTF-8'<br>
                );
            </div>
        </div>

        <div class="fix-card">
            <h4>Command Injection → Fixed</h4>
            <div class="before">
                // BEFORE ❌<br>
                $out = shell_exec(<br>
                &nbsp;"ping -c 2 ".$_POST['host']<br>
                );
            </div>
            <div class="after">
                // AFTER ✅<br>
                $h = escapeshellarg(<br>
                &nbsp;$_POST['host']<br>
                );<br>
                $out = shell_exec("ping -c 2 ".$h);
            </div>
        </div>

        <div class="fix-card">
            <h4>CSRF → Fixed</h4>
            <div class="before">
                // BEFORE ❌<br>
                &lt;form method="POST"&gt;<br>
                &nbsp;// No token!
            </div>
            <div class="after">
                // AFTER ✅<br>
                $token = bin2hex(<br>
                &nbsp;random_bytes(32)<br>
                );<br>
                &lt;input type="hidden"<br>
                &nbsp;name="csrf_token"<br>
                &nbsp;value="$token"&gt;
            </div>
        </div>

        <div class="fix-card">
            <h4>IDOR → Fixed</h4>
            <div class="before">
                // BEFORE ❌<br>
                $id = $_GET['user_id'];<br>
                // No ownership check!
            </div>
            <div class="after">
                // AFTER ✅<br>
                $id = $_SESSION['user_id'];<br>
                // Always use session ID
            </div>
        </div>

        <div class="fix-card">
            <h4>File Upload → Fixed</h4>
            <div class="before">
                // BEFORE ❌<br>
                // No validation!<br>
                move_uploaded_file(<br>
                &nbsp;$tmp, "uploads/".$name<br>
                );
            </div>
            <div class="after">
                // AFTER ✅<br>
                $allowed = ['jpg','png','pdf'];<br>
                if(in_array($ext, $allowed)<br>
                &nbsp;&amp;&amp; mime_check($tmp)) {<br>
                &nbsp;move_uploaded_file(...);<br>
                }
            </div>
        </div>

    </div>
</div>

<!-- FOOTER -->
<footer>
    <p>© 2026 <span>VulnShop Secure</span> — DevSecOps PFE</p>
    <p style="margin-top:8px;font-size:12px">
        ✅ All OWASP Top 10 vulnerabilities fixed —
        Running on port 8080
    </p>
</footer>

</body>
</html>
