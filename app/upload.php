<?php
session_start();
require_once 'config.php';
$message='';$message_type='';
if($_SERVER['REQUEST_METHOD']=='POST'&&isset($_FILES['file'])){
    $filename=$_FILES['file']['name'];
    // ⚠️ Unrestricted File Upload — RCE possible
    if(!is_dir('uploads/')) mkdir('uploads/',0777,true);
    if(move_uploaded_file($_FILES['file']['tmp_name'],"uploads/".$filename)){
        $message="✅ Fichier uploadé : ".$filename; // ⚠️ XSS
        $message_type='success';
    } else {
        $message="❌ Échec de l'upload !";
        $message_type='error';
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Upload — VulnShop</title>
<link href="https://fonts.googleapis.com/css2?family=Instrument+Serif:ital@0;1&family=DM+Sans:wght@300;400;500&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">
<style>
:root{--white:#fff;--off:#f7f6f3;--stone:#f0ede8;--border:#e8e4de;--border2:#d4cfc8;--ink:#1a1714;--ink2:#4a4540;--ink3:#9a9590;--gold:#c8a876;--red:#d94f3d;--green:#2e7d52;--serif:'Instrument Serif',serif;--sans:'DM Sans',sans-serif;--mono:'DM Mono',monospace}
*{margin:0;padding:0;box-sizing:border-box}
body{font-family:var(--sans);background:var(--stone);color:var(--ink);font-size:14px;line-height:1.6;min-height:100vh}
nav{background:rgba(255,255,255,.95);backdrop-filter:blur(12px);border-bottom:1px solid var(--border);height:60px;display:flex;align-items:center;padding:0 40px}
.nav-inner{max-width:1200px;margin:0 auto;width:100%;display:flex;align-items:center;justify-content:space-between}
.nav-logo{font-family:var(--serif);font-size:20px;color:var(--ink);text-decoration:none}
.nav-logo span{font-style:italic;color:var(--gold)}
.nav-links{display:flex;gap:24px;list-style:none}
.nav-links a{font-size:13px;color:var(--ink2);text-decoration:none}.nav-links a:hover{color:var(--ink)}
.main{max-width:680px;margin:48px auto;padding:0 24px}
.page-title{font-family:var(--serif);font-size:36px;color:var(--ink);margin-bottom:6px;text-align:center}
.page-sub{font-size:14px;color:var(--ink3);text-align:center;margin-bottom:32px}
.card{background:var(--white);border:1px solid var(--border);border-radius:16px;padding:32px;margin-bottom:16px}
.upload-area{border:2px dashed var(--border2);border-radius:12px;padding:48px 24px;text-align:center;background:var(--off);margin-bottom:20px;cursor:pointer;transition:border-color .2s}
.upload-area:hover{border-color:var(--ink)}
.upload-icon{font-size:40px;margin-bottom:12px}
.upload-hint{font-size:13px;color:var(--ink3);margin-bottom:16px}
.upload-label{display:inline-block;padding:8px 20px;background:var(--ink);color:white;border-radius:6px;cursor:pointer;font-size:13px;font-weight:500}
input[type=file]{display:none}
.file-name{font-size:12px;color:var(--ink3);margin-top:10px;font-family:var(--mono)}
.btn-upload{width:100%;padding:12px;background:var(--ink);color:white;border:none;border-radius:8px;font-size:14px;font-weight:500;cursor:pointer;font-family:var(--sans)}
.btn-upload:hover{background:#2d2926}
.msg{padding:12px 16px;border-radius:8px;font-size:13px;margin-bottom:16px}
.msg.success{background:#ecfdf5;color:var(--green);border:1px solid rgba(46,125,82,.2)}
.msg.error{background:#fceae8;color:var(--red);border:1px solid rgba(217,79,61,.2)}
.warning-box{background:#fef9e7;border:1px solid #fde68a;border-radius:8px;padding:14px 16px;font-size:12px;color:#92400e;margin-top:10px}
.files-title{font-size:14px;font-weight:500;color:var(--ink);margin-bottom:12px;padding-bottom:8px;border-bottom:1px solid var(--border)}
.file-item{display:flex;align-items:center;justify-content:space-between;padding:9px 12px;background:var(--off);border:1px solid var(--border);border-radius:6px;margin-bottom:6px;font-size:12px}
.file-item a{color:var(--ink);text-decoration:none;font-family:var(--mono)}.file-item a:hover{text-decoration:underline}
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
      <li><a href="login.php">Connexion</a></li>
    </ul>
  </div>
</nav>
<div class="main">
  <h1 class="page-title">Upload de fichier</h1>
  <p class="page-sub">Uploadez n'importe quel fichier — sans restrictions !</p>
  <?php if($message): ?>
    <div class="msg <?= $message_type ?>"><?= $message /* ⚠️ XSS */ ?></div>
  <?php endif; ?>
  <div class="card">
    <form method="POST" enctype="multipart/form-data"> <!-- ⚠️ pas de CSRF -->
      <div class="upload-area">
        <div class="upload-icon">📂</div>
        <div class="upload-hint">Glissez votre fichier ici ou cliquez pour sélectionner</div>
        <label class="upload-label" for="fileInput">Choisir un fichier</label>
        <input type="file" id="fileInput" name="file"
               onchange="document.querySelector('.file-name').textContent=this.files[0]?.name||''">
        <div class="file-name">Aucun fichier sélectionné</div>
      </div>
      <button type="submit" class="btn-upload">⬆ Uploader</button>
    </form>
    <div class="warning-box">⚠️ <b>Demo :</b> Accepte tous les types : .php shells, .sh scripts, .exe — RCE possible !</div>
  </div>
  <?php if(is_dir('uploads/')): ?>
  <div class="card">
    <div class="files-title">📋 Fichiers uploadés</div>
    <?php foreach(scandir('uploads/') as $f): ?>
      <?php if($f!='.'&&$f!='..'): ?>
      <div class="file-item">
        <span>📄 <a href="uploads/<?= $f ?>"><?= $f ?></a></span>
        <span style="font-size:11px;color:var(--ink3)"><?= date('d/m/Y H:i',filemtime('uploads/'.$f)) ?></span>
      </div>
      <?php endif; ?>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>
</div>
<footer><p>© 2026 <span>VulnShop</span> — DevSecOps PFE</p></footer>
</body>
</html>