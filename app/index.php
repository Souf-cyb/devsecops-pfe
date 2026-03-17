<?php
session_start();
require_once 'config.php';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>VulnShop — Premium Store</title>
<link href="https://fonts.googleapis.com/css2?family=Instrument+Serif:ital@0;1&family=DM+Sans:wght@300;400;500&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">
<style>
:root{--white:#fff;--off:#f7f6f3;--stone:#f0ede8;--border:#e8e4de;--border2:#d4cfc8;--ink:#1a1714;--ink2:#4a4540;--ink3:#9a9590;--gold:#c8a876;--red:#d94f3d;--green:#2e7d52;--serif:'Instrument Serif',Georgia,serif;--sans:'DM Sans',sans-serif;--mono:'DM Mono',monospace;--shadow-lg:0 4px 24px rgba(26,23,20,.10)}
*,*::before,*::after{margin:0;padding:0;box-sizing:border-box}
body{font-family:var(--sans);background:var(--white);color:var(--ink);font-size:14px;line-height:1.6;-webkit-font-smoothing:antialiased}
nav{position:sticky;top:0;z-index:100;background:rgba(255,255,255,.95);backdrop-filter:blur(12px);border-bottom:1px solid var(--border);height:60px;display:flex;align-items:center;padding:0 40px}
.nav-inner{max-width:1400px;margin:0 auto;width:100%;display:flex;align-items:center;justify-content:space-between}
.nav-logo{font-family:var(--serif);font-size:20px;color:var(--ink);text-decoration:none}
.nav-logo span{font-style:italic;color:var(--gold)}
.nav-links{display:flex;align-items:center;gap:28px;list-style:none}
.nav-links a{font-size:13px;color:var(--ink2);text-decoration:none;transition:color .2s}
.nav-links a:hover{color:var(--ink)}
.nav-actions{display:flex;align-items:center;gap:10px}
.btn{display:inline-flex;align-items:center;padding:8px 18px;border-radius:6px;font-size:13px;font-weight:500;cursor:pointer;transition:all .2s;border:none;text-decoration:none;font-family:var(--sans)}
.btn-primary{background:var(--ink);color:white}.btn-primary:hover{background:#2d2926}
.btn-outline{background:transparent;color:var(--ink);border:1px solid var(--border2)}.btn-outline:hover{background:var(--stone)}
.hero{background:var(--stone);border-bottom:1px solid var(--border);padding:72px 40px}
.hero-inner{max-width:1400px;margin:0 auto;display:grid;grid-template-columns:1fr 1fr;gap:60px;align-items:center}
.hero-eyebrow{font-size:11px;font-weight:500;letter-spacing:2.5px;text-transform:uppercase;color:var(--ink3);margin-bottom:14px}
.hero-title{font-family:var(--serif);font-size:52px;color:var(--ink);line-height:1.1;letter-spacing:-.5px;margin-bottom:16px}
.hero-title em{font-style:italic;color:var(--gold)}
.hero-sub{font-size:15px;color:var(--ink3);margin-bottom:28px;line-height:1.7}
.hero-actions{display:flex;gap:10px}
.hero-stats{display:grid;grid-template-columns:1fr 1fr;gap:12px}
.hstat{background:var(--white);border:1px solid var(--border);border-radius:12px;padding:20px;text-align:center}
.hstat-n{font-family:var(--serif);font-size:28px;color:var(--ink);line-height:1;margin-bottom:4px}
.hstat-l{font-size:10px;text-transform:uppercase;letter-spacing:1.5px;color:var(--ink3)}
.alert-bar{padding:11px 40px;font-size:13px;background:#fef9e7;border-bottom:1px solid #fde68a;color:#92400e}
.search-bar{border-bottom:1px solid var(--border);padding:14px 40px;background:var(--white);position:sticky;top:60px;z-index:9}
.search-inner{max-width:1400px;margin:0 auto;display:flex;gap:10px;align-items:center}
.search-form{display:flex;max-width:480px}
.s-input{flex:1;padding:9px 14px;border:1px solid var(--border2);border-right:none;border-radius:6px 0 0 6px;font-size:13px;font-family:var(--sans);outline:none;background:var(--off)}
.s-input:focus{border-color:var(--ink);background:var(--white)}
.s-btn{padding:9px 18px;background:var(--ink);color:white;border:none;border-radius:0 6px 6px 0;cursor:pointer;font-size:13px}
.products-section{max-width:1400px;margin:0 auto;padding:40px}
.section-header{display:flex;justify-content:space-between;align-items:center;margin-bottom:24px}
.section-title{font-family:var(--serif);font-size:28px;color:var(--ink)}
.products-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:20px}
.product-card{background:var(--white);border:1px solid var(--border);border-radius:12px;overflow:hidden;transition:box-shadow .25s,transform .2s;cursor:pointer}
.product-card:hover{box-shadow:var(--shadow-lg);transform:translateY(-2px)}
.product-img{aspect-ratio:4/3;background:var(--stone);display:flex;align-items:center;justify-content:center;font-size:52px;position:relative}
.product-badge{position:absolute;top:10px;left:10px;font-size:10px;font-weight:600;padding:3px 8px;border-radius:4px;letter-spacing:.5px;text-transform:uppercase}
.badge-new{background:var(--ink);color:white}.badge-sale{background:var(--red);color:white}
.product-info{padding:16px}
.product-cat{font-size:10px;font-weight:500;letter-spacing:1.5px;text-transform:uppercase;color:var(--ink3);margin-bottom:5px}
.product-name{font-size:14px;font-weight:500;color:var(--ink);margin-bottom:4px}
.product-desc{font-size:12px;color:var(--ink3);margin-bottom:12px;line-height:1.5}
.product-footer{display:flex;align-items:center;justify-content:space-between;padding-top:12px;border-top:1px solid var(--border)}
.product-price{font-family:var(--mono);font-size:15px;font-weight:500;color:var(--ink)}
.btn-buy{width:30px;height:30px;border-radius:50%;background:var(--ink);color:white;border:none;cursor:pointer;font-size:16px;display:flex;align-items:center;justify-content:center;text-decoration:none;transition:background .15s;line-height:1}
.btn-buy:hover{background:#2d2926}
.product-detail{background:var(--off);border:1px solid var(--border);border-radius:12px;padding:24px;margin:0 auto 40px;max-width:1320px}
.product-detail h3{font-family:var(--serif);font-size:20px;color:var(--ink);margin-bottom:12px}
footer{background:var(--ink);color:rgba(255,255,255,.45);text-align:center;padding:28px;font-size:12px;margin-top:60px}
footer span{color:var(--gold)}
</style>
</head>
<body>

<nav>
  <div class="nav-inner">
    <a class="nav-logo" href="index.php">Vuln<span>Shop</span></a>
    <ul class="nav-links">
      <li><a href="index.php">Accueil</a></li>
      <li><a href="search.php">Recherche</a></li>
      <li><a href="upload.php">Upload</a></li>
      <li><a href="api.php">API</a></li>
      <li><a href="admin.php?admin=true">Admin</a></li>
    </ul>
    <div class="nav-actions">
      <?php if(isset($_SESSION['username'])): ?>
        <a href="dashboard.php" class="btn btn-outline">👤 <?= $_SESSION['username'] ?></a>
        <a href="login.php" class="btn btn-primary">Déconnexion</a>
      <?php else: ?>
        <a href="login.php" class="btn btn-primary">Connexion</a>
      <?php endif; ?>
    </div>
  </div>
</nav>

<?php // ⚠️ XSS réfléchi
if(isset($_GET['msg'])): ?>
<div class="alert-bar"><?= $_GET['msg'] ?></div>
<?php endif; ?>

<?php // ⚠️ LFI
if(isset($_GET['page'])) include($_GET['page']); ?>

<div style="background:var(--stone);border-bottom:1px solid var(--border)">
  <div class="hero-inner" style="max-width:1400px;margin:0 auto;padding:72px 40px;display:grid;grid-template-columns:1fr 1fr;gap:60px;align-items:center">
    <div>
      <div class="hero-eyebrow">Collection Printemps 2026</div>
      <h1 class="hero-title">Bienvenue sur<br><em>VulnShop</em></h1>
      <p class="hero-sub">Votre boutique en ligne premium — totally not vulnerable 😉</p>
      <div class="hero-actions">
        <a href="search.php" class="btn btn-primary">Voir les produits</a>
        <a href="login.php" class="btn btn-outline">Se connecter</a>
      </div>
    </div>
    <div class="hero-stats">
      <div class="hstat"><div class="hstat-n">6</div><div class="hstat-l">Produits</div></div>
      <div class="hstat"><div class="hstat-n">4</div><div class="hstat-l">Utilisateurs</div></div>
      <div class="hstat"><div class="hstat-n">12k+</div><div class="hstat-l">Clients</div></div>
      <div class="hstat"><div class="hstat-n">4.8★</div><div class="hstat-l">Note moy.</div></div>
    </div>
  </div>
</div>

<div class="search-bar">
  <div class="search-inner">
    <form class="search-form" method="GET" action="search.php">
      <input class="s-input" type="text" name="q" placeholder="Rechercher un produit...">
      <button class="s-btn" type="submit">Rechercher</button>
    </form>
  </div>
</div>

<div class="products-section">
  <div class="section-header">
    <h2 class="section-title">Nos produits</h2>
    <span style="font-size:12px;color:var(--ink3)">6 produits disponibles</span>
  </div>
  <div class="products-grid">
    <?php
    $items=[
      [1,'💻','Electronique','Laptop Pro X','High performance laptop',999.99,'badge-new','Nouveau'],
      [2,'📱','Electronique','SmartPhone Z','Latest smartphone',699.99,'badge-sale','−20%'],
      [3,'🎮','Gaming','Gaming Console','Next gen gaming',499.99,null,null],
      [4,'⌚','Mode','Smart Watch','Health tracking watch',299.99,'badge-new','Nouveau'],
      [5,'🎧','Electronique','Pro Headphones','Crystal clear sound',199.99,null,null],
      [6,'📷','Photo','DSLR Camera','Professional photography',1299.99,null,null],
    ];
    foreach($items as [$id,$icon,$cat,$name,$desc,$price,$badge,$badge_lbl]):
    ?>
    <div class="product-card">
      <div class="product-img">
        <?= $icon ?>
        <?php if($badge): ?><span class="product-badge <?= $badge ?>"><?= $badge_lbl ?></span><?php endif; ?>
      </div>
      <div class="product-info">
        <div class="product-cat"><?= $cat ?></div>
        <div class="product-name"><?= $name ?></div>
        <div class="product-desc"><?= $desc ?></div>
        <div class="product-footer">
          <span class="product-price">$<?= number_format($price,2) ?></span>
          <a class="btn-buy" href="?id=<?= $id ?>">+</a>
        </div>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
</div>

<?php
// ⚠️ SQL Injection via ?id=
if(isset($_GET['id'])) {
    $id=$_GET['id'];
    $conn=getDB();
    $result=mysqli_query($conn,"SELECT * FROM products WHERE id=".$id);
    if($result && mysqli_num_rows($result)>0) {
        echo '<div class="product-detail"><h3>Détails du produit</h3>';
        while($row=mysqli_fetch_assoc($result)) {
            echo '<p><b>Nom :</b> '.$row['name'].'</p>';
            echo '<p><b>Description :</b> '.$row['description'].'</p>';
            echo '<p><b>Prix :</b> $'.$row['price'].'</p>';
        }
        echo '</div>';
    }
}
?>

<footer>
  <p>© 2026 <span>VulnShop</span> — Built for DevSecOps PFE</p>
  <p style="margin-top:6px;font-size:11px;opacity:.5">⚠️ Intentionally vulnerable for security testing</p>
</footer>
</body>
</html>