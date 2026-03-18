<?php
$pageTitle = 'Upload';
require_once '../includes/header.php';

$message = '';
$msg_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {
    $filename = $_FILES['file']['name'];
    $tmp      = $_FILES['file']['tmp_name'];

    // ⚠️ Unrestricted File Upload — accepte tout, y compris les shells PHP
    $upload_dir = __DIR__ . '/../uploads/';
    if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);

    $dest = $upload_dir . $filename; // ⚠️ Pas de randomisation du nom
    if (move_uploaded_file($tmp, $dest)) {
        $message  = "Fichier uploadé avec succès : <a href='../uploads/$filename' target='_blank'>$filename</a>"; // ⚠️ XSS
        $msg_type = 'success';
    } else {
        $message  = "Erreur lors de l'upload.";
        $msg_type = 'error';
    }
}

$files = is_dir(__DIR__.'/../uploads/') ? array_diff(scandir(__DIR__.'/../uploads/'), ['.','..']) : [];
?>

<div class="container" style="padding-top:28px;padding-bottom:60px">
  <div class="breadcrumb">
    <a href="../index.php">Accueil</a>
    <span class="breadcrumb-sep">›</span>
    <span>Upload de fichiers</span>
  </div>

  <div style="max-width:700px;margin:0 auto">
    <h1 style="font-family:var(--font-display);font-size:32px;margin-bottom:8px">Upload de fichiers</h1>
    <p style="color:var(--gray-500);margin-bottom:28px">Importez vos photos de produit ou autres documents.</p>

    <?php if($message): ?>
      <div style="background:<?= $msg_type=='success'?'#ecfdf5':'#fef2f2' ?>;border:1px solid <?= $msg_type=='success'?'#a7f3d0':'#fecaca' ?>;color:<?= $msg_type=='success'?'#065f46':'#991b1b' ?>;padding:12px 16px;border-radius:8px;font-size:13px;margin-bottom:20px">
        <?= $message /* ⚠️ XSS */ ?>
      </div>
    <?php endif; ?>

    <div class="card p-24" style="margin-bottom:20px">
      <form method="POST" enctype="multipart/form-data"> <!-- ⚠️ Pas de CSRF -->
        <div style="border:2px dashed var(--gray-200);border-radius:12px;padding:48px 24px;text-align:center;background:var(--gray-50);margin-bottom:20px;cursor:pointer;transition:all .2s" onclick="document.getElementById('fileInput').click()" onmouseover="this.style.borderColor='var(--primary)'" onmouseout="this.style.borderColor='var(--gray-200)'">
          <div style="font-size:40px;margin-bottom:12px">📂</div>
          <p style="font-size:14px;font-weight:500;color:var(--gray-700);margin-bottom:6px">Cliquez ou glissez votre fichier ici</p>
          <p style="font-size:12px;color:var(--gray-400)" id="fileName">Tous types de fichiers acceptés</p>
          <input type="file" id="fileInput" name="file" style="display:none"
                 onchange="document.getElementById('fileName').textContent=this.files[0]?.name||'Aucun fichier sélectionné'">
        </div>

        <div style="background:#fef9e7;border:1px solid #fde68a;border-radius:8px;padding:12px 14px;font-size:12px;color:#92400e;margin-bottom:20px">
          ⚠️ <strong>Démo vulnérabilité :</strong> Aucune restriction — les fichiers PHP (<code>.php</code>),
          scripts shell (<code>.sh</code>) et exécutables sont acceptés. Cela permet une <strong>Remote Code Execution</strong>.
        </div>

        <button type="submit" class="btn btn-primary btn-full">⬆ Uploader le fichier</button>
      </form>
    </div>

    <!-- ⚠️ Listage des fichiers uploadés -->
    <?php if(!empty($files)): ?>
    <div class="card">
      <div style="padding:16px 20px;border-bottom:1px solid var(--border);font-size:14px;font-weight:600">
        Fichiers uploadés (<?= count($files) ?>)
      </div>
      <div style="padding:8px 0">
        <?php foreach($files as $f): ?>
        <div style="display:flex;align-items:center;justify-content:space-between;padding:10px 20px;border-bottom:1px solid var(--border)">
          <div style="display:flex;align-items:center;gap:10px">
            <span style="font-size:18px"><?= in_array(pathinfo($f,PATHINFO_EXTENSION),['php','sh','py','rb'])? '⚠️':'📄' ?></span>
            <a href="../uploads/<?= $f ?>" target="_blank" style="font-size:13px;font-family:monospace;color:var(--primary)"><?= $f ?></a>
            <?php if(in_array(pathinfo($f,PATHINFO_EXTENSION),['php','sh','py'])): ?>
              <span style="font-size:10px;padding:1px 6px;background:#fee2e2;color:#991b1b;border-radius:3px;font-weight:600">EXÉCUTABLE</span>
            <?php endif; ?>
          </div>
          <div style="display:flex;align-items:center;gap:12px">
            <span style="font-size:11px;color:var(--gray-400)"><?= number_format(filesize(__DIR__.'/../uploads/'.$f)/1024,1) ?> KB</span>
            <span style="font-size:11px;color:var(--gray-400)"><?= date('d/m/Y H:i', filemtime(__DIR__.'/../uploads/'.$f)) ?></span>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
    <?php endif; ?>
  </div>
</div>

<?php require_once '../includes/footer.php'; ?>