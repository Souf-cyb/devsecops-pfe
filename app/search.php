<?php
session_start();
require_once 'config.php';
$results=[];$search='';
if(isset($_GET['q'])){
    $search=$_GET['q'];
    $conn=getDB();
    // ⚠️ SQL Injection
    $result=mysqli_query($conn,"SELECT * FROM products WHERE name LIKE '%".$search."%'");
    if($result) while($row=mysqli_fetch_assoc($result)) $results[]=$row;
}
// ⚠️ Code Injection via eval()
if(isset($_GET['filter'])) eval($_GET['filter']);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Recherche — VulnShop</title>
<link href="https://fonts.googleapis.com/css2?family=Instrument+Serif:ital@0;1&family=DM+Sans:wght@300;400;500&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">
<style>
:root{--white:#fff;--off:#f7f6f3;--stone:#f0ede8;--border:#e8e4de;--border2:#d4cfc8;--ink:#1a1714;--ink2:#4a4540;--ink3:#9a9590;--gold:#c8a876;--red:#d94f3d;--serif:'Instrument Serif',serif;--sans:'DM Sans',sans-serif;--mono:'DM Mono',monospace}
*{margin:0;padding:0;box-sizing:border-box}
body{font-family:var(--sans);background:var(--white);color:var(--ink);font-size:14px;line-height:1.6;-webkit-font-smoothing:antialiased}
nav{position:sticky;top:0;z-index:100;background:rgba(255,255,255,.95);backdrop-filter:blur(12px);border-bottom:1px solid var(--border);height:60px;display:flex;align-items:center;padding:0 40px}
.nav-inner{max-width:1400px;margin:0 auto;width:100%;display:flex;align-items:center;justify-content:space-between}
.nav-logo{font-family:var(--serif);font-size:20px;color:var(--ink);text-decoration:none}
.nav-logo span{font-style:italic;color:var(--gold)}
.nav-links{display:flex;gap:28px;list-style:none}
.nav-links a{font-size:13px;color:var(--ink2);text-decoration:none}.nav-links a:hover{color:var(--ink)}
.hero{background:var(--stone);padding:48px 40px;border-bottom:1px solid var(--border);text-align:center}
.hero-title{font-family:var(--serif);font-size:36px;color:var(--ink);margin-bottom:20px}
.search-form{display:flex;max-width:560px;margin:0 auto}
.s-input{flex:1;padding:12px 18px;border:1px solid var(--border2);border-right:none;border-radius:8px 0 0 8px;font-size:14px;font-family:var(--sans);outline:none;background:var(--white)}
.s-input:focus{border-color:var(--ink)}
.s-btn{padding:12px 24px;background:var(--ink);color:white;border:none;border-radius:0 8px 8px 0;cursor:pointer;font-size:14px}
.main{max-width:1400px;margin:0 auto;padding:40px}
.results-info{font-size:13px;color:var(--ink2);margin-bottom:24px;padding:10px 16px;background:var(--off);border:1px solid var(--border);border-radius:8px}
.results-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:20px}
.result-card{background:var(--white);border:1px solid var(--border);border-radius:12px;padding:20px;transition:box-shadow .2s}
.result-card:hover{box-shadow:0 4px 20px rgba(26,23,20,.08)}
.result-cat{font-size:10px;font-weight:500;letter-spacing:1.5px;text-transform:uppercase;color:var(--ink3);margin-bottom:6px}
.result-name{font-size:15px;font-weight:500;color:var(--ink);margin-bottom:6px}
.result-desc{font-size:12px;color:var(--ink3);margin-bottom:10px}
.result-price{font-family:var(--mono);font-size:16px;color:var(--ink);font-weight:500}
.empty{text-align:center;padding:60px;color:var(--ink3)}
.empty-icon{font-size:48px;margin-bottom:14px}
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
      <li><a href="login.php">Connexion</a></li>
      <li><a href="admin.php?admin=true">Admin</a></li>
    </ul>
  </div>
</nav>
<div class="hero">
  <h1 class="hero-title">Rechercher un produit</h1>
  <form class="search-form" method="GET">
    <input class="s-input" type="text" name="q"
           value="<?= $_GET['q'] ?? '' /* ⚠️ XSS */ ?>"
           placeholder="Rechercher...">
    <button class="s-btn" type="submit">Rechercher</button>
  </form>
</div>
<div class="main">
  <?php if($search): ?>
    <div class="results-info">
      Résultats pour : <b><?= $search /* ⚠️ XSS réfléchi */ ?></b>
      — <?= count($results) ?> produit(s) trouvé(s)
    </div>
    <?php if(count($results)>0): ?>
    <div class="results-grid">
      <?php foreach($results as $p): ?>
      <div class="result-card">
        <div class="result-cat"><?= $p['category'] ?? 'Produit' ?></div>
        <div class="result-name"><?= $p['name'] ?></div>
        <div class="result-desc"><?= $p['description'] ?></div>
        <div class="result-price">$<?= $p['price'] ?></div>
      </div>
      <?php endforeach; ?>
    </div>
    <?php else: ?>
    <div class="empty">
      <div class="empty-icon">🔍</div>
      <p>Aucun résultat pour "<?= $search ?>"</p>
    </div>
    <?php endif; ?>
  <?php else: ?>
  <div class="empty">
    <div class="empty-icon">🛍</div>
    <p>Entrez un terme de recherche</p>
    <p style="margin-top:8px;font-size:12px;font-family:var(--mono)">Essayez : <code>' OR 1=1 --</code></p>
  </div>
  <?php endif; ?>
</div>
<footer><p>© 2026 <span>VulnShop</span> — DevSecOps PFE</p></footer>
</body>
</html>