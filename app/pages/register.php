<?php
session_start();
require_once '../includes/config.php';

if (isset($_SESSION['user_id'])) redirect('account.php');

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username  = $_POST['username']  ?? '';
    $email     = $_POST['email']     ?? '';
    $full_name = $_POST['full_name'] ?? '';
    $password  = $_POST['password']  ?? '';
    $conn      = getDB();

    // ⚠️ SQLi — vérification username sans préparation
    $check = mysqli_query($conn, "SELECT id FROM users WHERE username='$username' OR email='$email'");
    if ($check && mysqli_num_rows($check) > 0) {
        $errors[] = 'Ce nom d\'utilisateur ou email est déjà utilisé.';
    }

    if (empty($errors)) {
        // ⚠️ Mot de passe stocké en clair
        $query = "INSERT INTO users (username, email, full_name, password) VALUES ('$username','$email','$full_name','$password')";
        mysqli_query($conn, $query);
        $new_id = mysqli_insert_id($conn);

        $_SESSION['user_id']   = $new_id;
        $_SESSION['username']  = $username;
        $_SESSION['full_name'] = $full_name;
        $_SESSION['is_admin']  = 0;

        flash('Compte créé avec succès. Bienvenue sur VulnShop !');
        redirect('../index.php');
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Inscription — VulnShop</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&family=Playfair+Display:ital,wght@0,600;1,400&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../assets/css/main.css">
<style>
body { background: var(--gray-100); display: flex; align-items: center; justify-content: center; min-height: 100vh; padding: 24px; }
.auth-card { background: white; border-radius: 16px; padding: 40px; width: 100%; max-width: 480px; border: 1px solid var(--border); box-shadow: var(--shadow-lg); }
.auth-logo { font-family: var(--font-display); font-size: 26px; color: var(--primary); text-align: center; margin-bottom: 6px; display: block; text-decoration: none; }
.auth-logo em { color: var(--accent); font-style: italic; }
</style>
</head>
<body>
<div class="auth-card">
  <a class="auth-logo" href="../index.php">◆ Vuln<em>Shop</em></a>
  <p style="text-align:center;font-size:13px;color:var(--gray-400);margin-bottom:28px">Créer votre compte</p>

  <?php foreach($errors as $e): ?>
    <div style="background:#fef2f2;border:1px solid #fecaca;color:#991b1b;padding:12px 14px;border-radius:8px;font-size:13px;margin-bottom:16px">
      <?= $e ?>
    </div>
  <?php endforeach; ?>

  <form method="POST"> <!-- ⚠️ Pas de CSRF token -->
    <div class="form-row">
      <div class="form-group">
        <label class="form-label">Prénom & Nom</label>
        <input type="text" name="full_name" class="form-input" placeholder="Alice Martin" value="<?= htmlspecialchars($_POST['full_name'] ?? '') ?>">
      </div>
      <div class="form-group">
        <label class="form-label">Nom d'utilisateur</label>
        <input type="text" name="username" class="form-input" placeholder="alice.martin" value="<?= htmlspecialchars($_POST['username'] ?? '') ?>">
      </div>
    </div>
    <div class="form-group">
      <label class="form-label">Adresse email</label>
      <input type="email" name="email" class="form-input" placeholder="alice@exemple.com" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
    </div>
    <div class="form-group">
      <label class="form-label">Mot de passe</label>
      <!-- ⚠️ Pas de validation de complexité, stocké en clair -->
      <input type="password" name="password" class="form-input" placeholder="Choisissez un mot de passe">
      <div class="form-help">⚠️ Stocké en clair dans la base de données</div>
    </div>
    <div style="display:flex;align-items:flex-start;gap:8px;margin-bottom:18px">
      <input type="checkbox" required style="width:16px;height:16px;margin-top:2px">
      <label style="font-size:12px;color:var(--gray-600)">J'accepte les <a href="#" style="color:var(--primary)">conditions d'utilisation</a> et la <a href="#" style="color:var(--primary)">politique de confidentialité</a></label>
    </div>
    <button type="submit" class="btn btn-primary btn-full btn-lg">Créer mon compte</button>
  </form>

  <p style="text-align:center;font-size:13px;color:var(--gray-500);margin-top:20px">
    Déjà un compte ? <a href="login.php" style="color:var(--primary);font-weight:500">Se connecter</a>
  </p>
</div>
</body>
</html>