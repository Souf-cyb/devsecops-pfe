<?php
$pageTitle = 'Contact';
require_once '../includes/header.php';
$conn    = getDB();
$success = false;

// ⚠️ Path Traversal via ?file=
if (isset($_GET['file'])) {
    $file = $_GET['file']; // Pas de validation — ../../../etc/passwd
    echo '<div class="container" style="padding:20px 0"><div class="card p-20"><pre style="font-size:12px;overflow:auto;color:var(--gray-700)">';
    echo htmlspecialchars(file_get_contents($file)); // ← Path Traversal
    echo '</pre></div></div>';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name    = $_POST['name']    ?? '';
    $email   = $_POST['email']   ?? '';
    $subject = $_POST['subject'] ?? '';
    $message = $_POST['message'] ?? '';

    // ⚠️ Command Injection via email
    $check = shell_exec("echo Vérification email: " . $email);

    // ⚠️ Stored XSS — pas de sanitisation
    $name_safe = mysqli_real_escape_string($conn, $name);
    $sub_safe  = mysqli_real_escape_string($conn, $subject);
    // message non échappé → Stored XSS
    mysqli_query($conn, "INSERT INTO contact_messages (name, email, subject, message) VALUES ('$name_safe', '$email', '$sub_safe', '$message')");

    $success = true;
}

// Load messages — affichés sans échappement (admin view)
$messages = mysqli_query($conn, "SELECT * FROM contact_messages ORDER BY created_at DESC LIMIT 5");
?>

<div class="container" style="padding-top:28px;padding-bottom:60px">
  <div class="breadcrumb">
    <a href="../index.php">Accueil</a>
    <span class="breadcrumb-sep">›</span>
    <span>Contact</span>
  </div>

  <div style="display:grid;grid-template-columns:1fr 1fr;gap:40px">

    <div>
      <h1 style="font-family:var(--font-display);font-size:34px;margin-bottom:8px">Contactez-nous</h1>
      <p style="color:var(--gray-500);font-size:14px;margin-bottom:32px;line-height:1.7">
        Notre équipe est disponible du lundi au vendredi, 9h-18h.<br>Réponse garantie sous 24h.
      </p>

      <div style="display:flex;flex-direction:column;gap:14px;margin-bottom:32px">
        <?php foreach([
          ['📧','Email','support@vulnshop.com'],
          ['📞','Téléphone','+33 1 23 45 67 89'],
          ['📍','Adresse','1 rue de la Paix, 75001 Paris'],
          ['🕐','Horaires','Lun-Ven : 9h00 - 18h00'],
        ] as [$icon,$label,$val]): ?>
        <div style="display:flex;align-items:center;gap:14px;padding:14px;background:var(--gray-50);border:1px solid var(--border);border-radius:8px">
          <span style="font-size:20px"><?= $icon ?></span>
          <div>
            <div style="font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:1px;color:var(--gray-400);margin-bottom:2px"><?= $label ?></div>
            <div style="font-size:13px;font-weight:500"><?= $val ?></div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>

    <div>
      <div class="card p-24">
        <?php if($success): ?>
        <div style="background:#ecfdf5;border:1px solid #a7f3d0;color:#065f46;padding:14px;border-radius:8px;margin-bottom:20px;font-size:13px">
          ✓ Votre message a été envoyé avec succès !
        </div>
        <?php endif; ?>

        <h3 style="font-size:16px;font-weight:600;margin-bottom:20px">Envoyer un message</h3>
        <form method="POST"> <!-- ⚠️ Pas de CSRF token -->
          <div class="form-row">
            <div class="form-group">
              <label class="form-label">Nom complet</label>
              <input type="text" name="name" class="form-input" placeholder="Votre nom" required>
            </div>
            <div class="form-group">
              <label class="form-label">Email</label>
              <!-- ⚠️ Command Injection via ce champ -->
              <input type="email" name="email" class="form-input" placeholder="votre@email.com" required>
            </div>
          </div>
          <div class="form-group">
            <label class="form-label">Sujet</label>
            <input type="text" name="subject" class="form-input" placeholder="Sujet de votre message">
          </div>
          <div class="form-group">
            <label class="form-label">Message</label>
            <!-- ⚠️ Stored XSS via ce champ -->
            <textarea name="message" class="form-textarea" placeholder="Votre message..." required style="height:140px"></textarea>
          </div>
          <button type="submit" class="btn btn-primary btn-full">Envoyer le message</button>
        </form>
      </div>
    </div>
  </div>

  <!-- Messages reçus — ⚠️ Stored XSS -->
  <?php if($messages && mysqli_num_rows($messages) > 0): ?>
  <div style="margin-top:40px">
    <h2 style="font-size:18px;font-weight:600;margin-bottom:16px">Messages reçus</h2>
    <div class="card">
      <div class="table-wrap">
        <table>
          <thead><tr><th>De</th><th>Email</th><th>Sujet</th><th>Message</th><th>Date</th></tr></thead>
          <tbody>
            <?php while($msg = mysqli_fetch_assoc($messages)): ?>
            <tr>
              <td style="font-weight:500"><?= $msg['name'] ?></td>
              <td style="font-size:12px;color:var(--gray-500)"><?= $msg['email'] ?></td>
              <td><?= $msg['subject'] ?></td>
              <!-- ⚠️ Stored XSS — message affiché sans échappement -->
              <td style="max-width:300px"><?= $msg['message'] ?></td>
              <td style="font-size:11px;color:var(--gray-400)"><?= date('d/m/Y H:i', strtotime($msg['created_at'])) ?></td>
            </tr>
            <?php endwhile; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
  <?php endif; ?>
</div>

<?php require_once '../includes/footer.php'; ?>