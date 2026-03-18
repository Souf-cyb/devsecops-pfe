<?php
session_start();
require_once '../includes/config.php';

if (isset($_SESSION['user_id'])) redirect('account.php');

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    $conn     = getDB();

    // ⚠️ SQLi — aucune préparation de requête
    $query  = "SELECT * FROM users WHERE (username='$username' OR email='$username') AND password='$password' AND is_active=1";
    $result = mysqli_query($conn, $query);

    if ($result && mysqli_num_rows($result) > 0) {
        $user = mysqli_fetch_assoc($result);
        $_SESSION['user_id']   = $user['id'];
        $_SESSION['username']  = $user['username'];
        $_SESSION['full_name'] = $user['full_name'];
        $_SESSION['is_admin']  = $user['is_admin'];

        // Update last login
        mysqli_query($conn, "UPDATE users SET last_login=NOW() WHERE id={$user['id']}");

        flash('Bienvenue ' . $user['full_name'] . ' !');

        // ⚠️ Open Redirect via ?redirect=
        $redirect = $_GET['redirect'] ?? ($user['is_admin'] ? '../admin/index.php' : '../index.php');
        header("Location: $redirect");
        exit();
    } else {
        // ⚠️ Message d'erreur verbose — XSS + info leak
        $check = mysqli_query($conn, "SELECT id FROM users WHERE username='$username'");
        if ($check && mysqli_num_rows($check) > 0) {
            $error = "Mot de passe incorrect pour l'utilisateur : <b>$username</b>";
        } else {
            $error = "Aucun compte trouvé pour : <b>$username</b>";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Connexion — VulnShop</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&family=Playfair+Display:ital,wght@0,600;1,400&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../assets/css/main.css">
<style>
body { background: var(--gray-100); display: flex; align-items: center; justify-content: center; min-height: 100vh; }
.auth-card { background: white; border-radius: 16px; padding: 40px; width: 100%; max-width: 420px; border: 1px solid var(--border); box-shadow: var(--shadow-lg); }
.auth-logo { font-family: var(--font-display); font-size: 26px; color: var(--primary); text-align: center; margin-bottom: 6px; display: block; text-decoration: none; }
.auth-logo em { color: var(--accent); font-style: italic; }
.auth-logo .logo-icon { color: var(--accent); }
.auth-subtitle { text-align: center; font-size: 13px; color: var(--gray-400); margin-bottom: 28px; }
.auth-footer { text-align: center; font-size: 13px; color: var(--gray-500); margin-top: 20px; }
.auth-footer a { color: var(--primary); font-weight: 500; }
.divider { display: flex; align-items: center; gap: 12px; margin: 20px 0; font-size: 12px; color: var(--gray-300); }
.divider::before, .divider::after { content: ''; flex: 1; height: 1px; background: var(--border); }
.demo-box { background: var(--gray-50); border: 1px solid var(--border); border-radius: 8px; padding: 12px 14px; font-size: 11px; color: var(--gray-500); margin-top: 20px; }
.demo-box b { color: var(--gray-700); }
.demo-box code { font-family: monospace; background: var(--gray-100); padding: 1px 4px; border-radius: 3px; }
</style>
</head>
<body>

<div class="auth-card">
  <a class="auth-logo" href="../index.php">
    <span class="logo-icon">◆</span> Vuln<em>Shop</em>
  </a>
  <p class="auth-subtitle">Connectez-vous à votre compte</p>

  <?php if($error): ?>
    <!-- ⚠️ XSS réfléchi dans le message d'erreur -->
    <div style="background:#fef2f2;border:1px solid #fecaca;color:#991b1b;padding:12px 14px;border-radius:8px;font-size:13px;margin-bottom:18px">
      <?= $error ?>
    </div>
  <?php endif; ?>

  <!-- ⚠️ Pas de token CSRF -->
  <form method="POST">
    <div class="form-group">
      <label class="form-label">Email ou nom d'utilisateur</label>
      <input type="text" name="username" class="form-input"
             placeholder="email@exemple.com ou username"
             value="<?= htmlspecialchars($_POST['username'] ?? '') ?>" autocomplete="username">
    </div>
    <div class="form-group">
      <label class="form-label" style="display:flex;justify-content:space-between">
        <span>Mot de passe</span>
        <a href="#" style="font-weight:400;color:var(--gray-400);font-size:12px">Oublié ?</a>
      </label>
      <input type="password" name="password" class="form-input" placeholder="Votre mot de passe" autocomplete="current-password">
    </div>
    <div style="display:flex;align-items:center;gap:8px;margin-bottom:18px">
      <input type="checkbox" id="remember" name="remember" style="width:16px;height:16px">
      <label for="remember" style="font-size:13px;color:var(--gray-600)">Se souvenir de moi</label>
    </div>
    <button type="submit" class="btn btn-primary btn-full btn-lg">Se connecter</button>
  </form>

  <div class="divider">ou</div>

  <div class="auth-footer">
    Pas encore de compte ? <a href="register.php">Créer un compte</a>
  </div>

  <!-- ⚠️ Credentials exposés + hint SQLi -->
  <div class="demo-box">
    <b>Comptes de test :</b><br>
    admin / Admin@2024! &nbsp;|&nbsp; alice.martin / password123<br>
    bob.dupont / bob2024 &nbsp;|&nbsp; charlie.leclerc / charlie99<br><br>
    <b>SQLi bypass :</b> <code>' OR '1'='1' --</code>
  </div>
</div>

</body>
</html>