<?php
session_start();
require_once 'config.php';
if(!isset($_SESSION['username'])){ header("Location: login.php"); exit(); }
$conn=getDB();
$target_user=null;
if(isset($_GET['user_id'])){
    $user_id=$_GET['user_id'];
    // ⚠️ IDOR + SQLi
    $result=mysqli_query($conn,"SELECT * FROM users WHERE id=".$user_id);
    $target_user=mysqli_fetch_assoc($result);
}
// ⚠️ SQLi
$result=mysqli_query($conn,"SELECT * FROM users WHERE username='".$_SESSION['username']."'");
$current_user=mysqli_fetch_assoc($result);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Dashboard — VulnShop</title>
<link href="https://fonts.googleapis.com/css2?family=Instrument+Serif:ital@0;1&family=DM+Sans:wght@300;400;500&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">
<style>
:root{--white:#fff;--off:#f7f6f3;--stone:#f0ede8;--border:#e8e4de;--border2:#d4cfc8;--ink:#1a1714;--ink2:#4a4540;--ink3:#9a9590;--gold:#c8a876;--red:#d94f3d;--green:#2e7d52;--serif:'Instrument Serif',serif;--sans:'DM Sans',sans-serif;--mono:'DM Mono',monospace}
*{margin:0;padding:0;box-sizing:border-box}
body{font-family:var(--sans);background:var(--off);color:var(--ink);font-size:14px;line-height:1.6;-webkit-font-smoothing:antialiased}
nav{background:rgba(255,255,255,.95);backdrop-filter:blur(12px);border-bottom:1px solid var(--border);height:60px;display:flex;align-items:center;padding:0 40px}
.nav-inner{max-width:1200px;margin:0 auto;width:100%;display:flex;align-items:center;justify-content:space-between}
.nav-logo{font-family:var(--serif);font-size:20px;color:var(--ink);text-decoration:none}
.nav-logo span{font-style:italic;color:var(--gold)}
.nav-links{display:flex;gap:24px;list-style:none}
.nav-links a{font-size:13px;color:var(--ink2);text-decoration:none}.nav-links a:hover{color:var(--ink)}
.main{max-width:1000px;margin:40px auto;padding:0 24px;display:grid;grid-template-columns:260px 1fr;gap:20px}
.card{background:var(--white);border:1px solid var(--border);border-radius:12px;padding:24px}
.avatar{width:64px;height:64px;border-radius:50%;background:var(--stone);display:flex;align-items:center;justify-content:center;font-size:26px;margin:0 auto 14px}
.profile-name{font-family:var(--serif);font-size:20px;color:var(--ink);text-align:center;margin-bottom:4px}
.role-pill{display:block;text-align:center;font-size:10px;font-weight:600;letter-spacing:1px;text-transform:uppercase;padding:3px 10px;border-radius:20px;margin:0 auto 20px;width:fit-content}
.role-admin{background:#fef3cd;color:#92680a}
.role-user{background:var(--off);color:var(--ink3)}
.info-row{display:flex;justify-content:space-between;padding:8px 0;border-bottom:1px solid var(--border);font-size:13px}
.info-row:last-child{border:none}
.info-label{color:var(--ink3)}
.info-val{font-family:var(--mono);font-size:12px;color:var(--ink);font-weight:500}
.info-val.sensitive{color:var(--red)}
.section-title{font-size:15px;font-weight:500;color:var(--ink);margin-bottom:16px;padding-bottom:10px;border-bottom:1px solid var(--border)}
.stat-grid{display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:20px}
.stat-item{background:var(--off);border:1px solid var(--border);border-radius:8px;padding:14px;text-align:center}
.stat-n{font-family:var(--serif);font-size:22px;color:var(--ink);line-height:1;margin-bottom:3px}
.stat-l{font-size:10px;text-transform:uppercase;letter-spacing:1px;color:var(--ink3)}
.vuln-tag{display:inline-block;font-size:9px;font-weight:600;padding:2px 7px;border-radius:4px;background:#fff3cd;color:#92680a;margin-bottom:8px}
.idor-form{display:flex;gap:8px;margin-bottom:10px}
.idor-input{flex:1;padding:8px 12px;border:1px solid var(--border2);border-radius:6px;font-size:13px;font-family:var(--sans);outline:none;background:var(--off)}
.idor-input:focus{border-color:var(--ink)}
.idor-btn{padding:8px 16px;background:var(--ink);color:white;border:none;border-radius:6px;cursor:pointer;font-size:13px}
.user-data-box{background:var(--off);border:1px solid var(--border);border-radius:8px;padding:14px;font-size:12px;font-family:var(--mono);color:var(--ink2);overflow:auto;max-height:200px}
.danger-zone{border:1px solid rgba(217,79,61,.3);border-radius:12px;padding:20px;margin-top:20px;background:#fef9f8}
.danger-title{font-size:14px;font-weight:500;color:var(--red);margin-bottom:12px}
.btn-danger{padding:9px 20px;background:var(--red);color:white;border:none;border-radius:6px;cursor:pointer;font-size:13px;font-family:var(--sans)}
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
      <li><a href="search.php">Recherche</a></li>
      <li><a href="login.php">Déconnexion</a></li>
    </ul>
  </div>
</nav>
<div class="main">
  <div>
    <div class="card">
      <div class="avatar">👤</div>
      <div class="profile-name"><?= $_SESSION['username'] /* ⚠️ XSS */ ?></div>
      <span class="role-pill role-<?= $current_user['is_admin']?'admin':'user' ?>">
        <?= $current_user['is_admin']?'Administrateur':'Utilisateur' ?>
      </span>
      <?php if($current_user): ?>
      <div class="info-row"><span class="info-label">Username</span><span class="info-val"><?= $current_user['username'] ?></span></div>
      <div class="info-row"><span class="info-label">Email</span><span class="info-val"><?= $current_user['email'] ?></span></div>
      <div class="info-row"><span class="info-label">Password</span><span class="info-val sensitive"><?= $current_user['password'] /* ⚠️ mot de passe en clair */ ?></span></div>
      <div class="info-row"><span class="info-label">Admin</span><span class="info-val"><?= $current_user['is_admin']?'✅':'❌' ?></span></div>
      <?php endif; ?>
    </div>
  </div>
  <div style="display:flex;flex-direction:column;gap:20px">
    <div class="card">
      <div class="section-title">Statistiques</div>
      <div class="stat-grid">
        <div class="stat-item"><div class="stat-n">12</div><div class="stat-l">Commandes</div></div>
        <div class="stat-item"><div class="stat-n">5</div><div class="stat-l">Favoris</div></div>
        <div class="stat-item"><div class="stat-n">$2 450</div><div class="stat-l">Total dépensé</div></div>
        <div class="stat-item"><div class="stat-n">2023</div><div class="stat-l">Membre depuis</div></div>
      </div>
    </div>
    <div class="card">
      <div class="section-title">Voir un profil utilisateur</div>
      <span class="vuln-tag">⚠️ IDOR Vulnerability</span>
      <p style="font-size:12px;color:var(--ink3);margin-bottom:10px">Changez l'ID pour accéder aux données d'autres utilisateurs !</p>
      <form method="GET" class="idor-form">
        <input class="idor-input" type="number" name="user_id" value="<?= $_GET['user_id']??1 ?>" placeholder="User ID">
        <button type="submit" class="idor-btn">Voir</button>
      </form>
      <?php if($target_user): ?>
      <div class="user-data-box"><?= print_r($target_user,true) /* ⚠️ IDOR + XSS */ ?></div>
      <?php endif; ?>
    </div>
    <div class="danger-zone">
      <div class="danger-title">⚠️ Zone dangereuse</div>
      <form method="POST" action="delete_account.php"> <!-- ⚠️ CSRF -->
        <input type="hidden" name="user_id" value="<?= $current_user['id']??1 ?>">
        <button type="submit" class="btn-danger" onclick="return confirm('Supprimer ?')">🗑 Supprimer le compte</button>
      </form>
    </div>
  </div>
</div>
<footer><p>© 2026 <span>VulnShop</span> — DevSecOps PFE</p></footer>
</body>
</html>