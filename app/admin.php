<?php
session_start();
require_once 'config.php';
// ⚠️ Broken Access Control
$authorized=isset($_GET['admin'])&&$_GET['admin']==='true';
$conn=getDB();
$search_results=[];
if(isset($_POST['search_user'])){
    $search=$_POST['search_user'];
    // ⚠️ SQLi
    $result=mysqli_query($conn,"SELECT id,username,email,password,is_admin FROM users WHERE username LIKE '%".$search."%'");
    if($result) while($row=mysqli_fetch_assoc($result)) $search_results[]=$row;
}
$ping_output='';
if(isset($_POST['ping'])){
    // ⚠️ Command Injection
    $ping_output=shell_exec("ping -c 2 ".$_POST['ping']);
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Admin — VulnShop</title>
<link href="https://fonts.googleapis.com/css2?family=Instrument+Serif:ital@0;1&family=DM+Sans:wght@300;400;500&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">
<style>
:root{--white:#fff;--off:#f7f6f3;--stone:#f0ede8;--border:#e8e4de;--border2:#d4cfc8;--ink:#1a1714;--ink2:#4a4540;--ink3:#9a9590;--gold:#c8a876;--red:#d94f3d;--serif:'Instrument Serif',serif;--sans:'DM Sans',sans-serif;--mono:'DM Mono',monospace}
*{margin:0;padding:0;box-sizing:border-box}
body{font-family:var(--sans);background:var(--off);color:var(--ink);font-size:14px;line-height:1.6}
nav{background:rgba(255,255,255,.95);backdrop-filter:blur(12px);border-bottom:1px solid var(--border);height:60px;display:flex;align-items:center;padding:0 40px}
.nav-inner{max-width:1200px;margin:0 auto;width:100%;display:flex;align-items:center;justify-content:space-between}
.nav-logo{font-family:var(--serif);font-size:20px;color:var(--ink);text-decoration:none}
.nav-logo span{font-style:italic;color:var(--gold)}
.nav-links{display:flex;gap:24px;list-style:none}
.nav-links a{font-size:13px;color:var(--ink2);text-decoration:none}.nav-links a:hover{color:var(--ink)}
.main{max-width:1200px;margin:32px auto;padding:0 24px}
.access-banner{background:#fef9e7;border:1px solid #fde68a;border-radius:10px;padding:14px 18px;margin-bottom:24px;font-size:13px;color:#92400e}
.access-banner code{font-family:var(--mono);background:rgba(146,64,14,.1);padding:1px 5px;border-radius:3px;font-size:11px}
.page-title{font-family:var(--serif);font-size:30px;color:var(--ink);margin-bottom:4px}
.page-sub{font-size:13px;color:var(--ink3);margin-bottom:24px}
.admin-grid{display:grid;grid-template-columns:1fr 1fr;gap:20px}
.card{background:var(--white);border:1px solid var(--border);border-radius:12px;padding:24px}
.card-title{font-size:15px;font-weight:500;color:var(--ink);margin-bottom:16px;padding-bottom:12px;border-bottom:1px solid var(--border);display:flex;align-items:center;gap:8px}
.vuln-tag{font-size:9px;font-weight:600;padding:2px 7px;border-radius:4px;background:#fff3cd;color:#92680a}
.form-group{margin-bottom:14px}
label{display:block;font-size:12px;font-weight:500;color:var(--ink2);margin-bottom:5px}
input[type=text]{width:100%;padding:9px 12px;border:1px solid var(--border2);border-radius:6px;font-size:13px;font-family:var(--sans);outline:none;background:var(--off)}
input:focus{border-color:var(--ink);background:var(--white)}
.btn{padding:9px 20px;background:var(--ink);color:white;border:none;border-radius:6px;cursor:pointer;font-size:13px;font-family:var(--sans)}
table{width:100%;border-collapse:collapse;margin-top:14px;font-size:12px}
th{text-align:left;font-size:10px;font-weight:600;letter-spacing:1px;text-transform:uppercase;color:var(--ink3);padding:0 10px 8px;border-bottom:1px solid var(--border)}
td{padding:10px;border-bottom:1px solid var(--border);color:var(--ink2)}
tr:last-child td{border:none}
tr:hover td{background:var(--off)}
.mono{font-family:var(--mono);font-size:11px}
.sensitive{color:var(--red);font-weight:500}
.terminal{background:var(--ink);color:#4ade80;padding:16px;border-radius:8px;font-family:var(--mono);font-size:12px;margin-top:14px;white-space:pre-wrap;min-height:90px;line-height:1.6}
footer{background:var(--ink);color:rgba(255,255,255,.45);text-align:center;padding:24px;font-size:12px;margin-top:60px}
footer span{color:var(--gold)}
</style>
</head>
<body>
<nav>
  <div class="nav-inner">
    <a class="nav-logo" href="index.php">Vuln<span>Shop</span></a>
    <ul class="nav-links">
      <li><a href="index.php">Accueil</a></li>
      <li><a href="api.php">API</a></li>
    </ul>
  </div>
</nav>
<div class="main">
  <?php if(!$authorized): ?>
  <div class="access-banner">⛔ Accès refusé — Ajoutez <code>?admin=true</code> à l'URL pour bypasser !</div>
  <?php endif; ?>
  <h1 class="page-title">Admin Panel</h1>
  <p class="page-sub">VulnShop Administration Dashboard</p>
  <div class="admin-grid">
    <div class="card">
      <div class="card-title">👥 User Search <span class="vuln-tag">SQLi</span></div>
      <form method="POST">
        <div class="form-group">
          <label>Rechercher un utilisateur</label>
          <input type="text" name="search_user" placeholder="Entrez un username...">
        </div>
        <button type="submit" class="btn">Rechercher</button>
      </form>
      <?php if(count($search_results)>0): ?>
      <table>
        <thead><tr><th>ID</th><th>Username</th><th>Email</th><th>Password</th><th>Admin</th></tr></thead>
        <tbody>
          <?php foreach($search_results as $row): ?>
          <tr>
            <td class="mono">#<?= $row['id'] ?></td>
            <td><?= $row['username'] /* ⚠️ XSS */ ?></td>
            <td><?= $row['email'] ?></td>
            <td class="mono sensitive"><?= $row['password'] /* ⚠️ passwords en clair */ ?></td>
            <td><?= $row['is_admin']?'✅':'❌' ?></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
      <?php endif; ?>
    </div>
    <div class="card">
      <div class="card-title">🖥 Server Diagnostics <span class="vuln-tag">Command Injection</span></div>
      <form method="POST">
        <div class="form-group">
          <label>Ping Host</label>
          <input type="text" name="ping" placeholder="ex: google.com ou 127.0.0.1; whoami">
        </div>
        <button type="submit" class="btn">▶ Exécuter</button>
      </form>
      <?php if($ping_output): ?>
        <div class="terminal"><?= $ping_output /* ⚠️ XSS + Command Injection */ ?></div>
      <?php else: ?>
        <div class="terminal">$ En attente d'une commande...</div>
      <?php endif; ?>
    </div>
  </div>
</div>
<footer><p>© 2026 <span>VulnShop</span> Admin — DevSecOps PFE</p></footer>
</body>
</html>