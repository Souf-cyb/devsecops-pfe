<?php
session_start();
require_once 'config.php';
$error='';
if($_SERVER['REQUEST_METHOD']=='POST'){
    $username=$_POST['username'];
    $password=$_POST['password'];
    $conn=getDB();
    // ⚠️ SQL Injection
    $query="SELECT * FROM users WHERE username='".$username."' AND password='".$password."'";
    $result=mysqli_query($conn,$query);
    if($result && mysqli_num_rows($result)>0){
        $user=mysqli_fetch_assoc($result);
        $_SESSION['user']=$user;
        $_SESSION['username']=$username;
        $_SESSION['is_admin']=$user['is_admin'];
        $redirect=isset($_GET['redirect'])?$_GET['redirect']:'dashboard.php';
        header("Location: ".$redirect); // ⚠️ Open Redirect
        exit();
    } else {
        $error="Login failed for user: ".$username; // ⚠️ XSS
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Connexion — VulnShop</title>
<link href="https://fonts.googleapis.com/css2?family=Instrument+Serif:ital@0;1&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
<style>
:root{--white:#fff;--off:#f7f6f3;--stone:#f0ede8;--border:#e8e4de;--border2:#d4cfc8;--ink:#1a1714;--ink2:#4a4540;--ink3:#9a9590;--gold:#c8a876;--red:#d94f3d;--serif:'Instrument Serif',serif;--sans:'DM Sans',sans-serif;--mono:'DM Mono',monospace}
*{margin:0;padding:0;box-sizing:border-box}
body{font-family:var(--sans);background:var(--stone);min-height:100vh;display:flex;flex-direction:column;align-items:center;justify-content:center;padding:24px}
.card{background:var(--white);border:1px solid var(--border);border-radius:16px;padding:40px;width:100%;max-width:400px;box-shadow:0 4px 24px rgba(26,23,20,.08)}
.logo{font-family:var(--serif);font-size:24px;color:var(--ink);text-align:center;margin-bottom:6px;text-decoration:none;display:block}
.logo span{font-style:italic;color:var(--gold)}
.subtitle{text-align:center;font-size:13px;color:var(--ink3);margin-bottom:28px}
.form-group{margin-bottom:16px}
label{display:block;font-size:12px;font-weight:500;color:var(--ink2);margin-bottom:6px}
input{width:100%;padding:10px 14px;border:1px solid var(--border2);border-radius:8px;font-size:13px;font-family:var(--sans);outline:none;background:var(--off);transition:border-color .2s}
input:focus{border-color:var(--ink);background:var(--white)}
.btn-login{width:100%;padding:11px;background:var(--ink);color:white;border:none;border-radius:8px;font-size:14px;font-weight:500;cursor:pointer;font-family:var(--sans);margin-top:8px}
.btn-login:hover{background:#2d2926}
.error-box{background:#fceae8;border:1px solid rgba(217,79,61,.2);color:var(--red);padding:10px 14px;border-radius:8px;font-size:13px;margin-bottom:16px}
.hint-box{background:var(--off);border:1px solid var(--border);border-radius:8px;padding:12px 14px;font-size:11px;color:var(--ink3);margin-top:20px}
.hint-box b{color:var(--ink2)}
.hint-box code{font-family:var(--mono);background:var(--stone);padding:1px 5px;border-radius:3px}
.link{color:var(--ink);font-weight:500;text-decoration:none}
</style>
</head>
<body>
<div class="card">
  <a class="logo" href="index.php">Vuln<span>Shop</span></a>
  <p class="subtitle">Connectez-vous à votre compte</p>
  <?php if($error): ?>
    <div class="error-box"><?= $error /* ⚠️ XSS */ ?></div>
  <?php endif; ?>
  <form method="POST"> <!-- ⚠️ pas de CSRF token -->
    <div class="form-group">
      <label>Nom d'utilisateur</label>
      <input type="text" name="username" placeholder="Votre username">
    </div>
    <div class="form-group">
      <label>Mot de passe</label>
      <input type="password" name="password" placeholder="Votre mot de passe">
    </div>
    <button type="submit" class="btn-login">Se connecter</button>
  </form>
  <div class="hint-box">
    <b>Comptes :</b> admin/admin123 · alice/password123 · bob/bob2024<br><br>
    <b>SQLi payload :</b> <code>' OR 1=1 --</code>
  </div>
  <p style="text-align:center;font-size:13px;color:var(--ink3);margin-top:16px">
    <a class="link" href="index.php">← Retour à la boutique</a>
  </p>
</div>
</body>
</html>