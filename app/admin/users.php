<?php
session_start();
require_once '../includes/config.php';
if (!isset($_SESSION['user_id'])) redirect('../pages/login.php');
$conn = getDB();

// Delete user — ⚠️ CSRF + SQLi
if (isset($_GET['delete'])) {
    $uid = $_GET['delete'];
    mysqli_query($conn, "DELETE FROM users WHERE id=$uid"); // ⚠️ SQLi + CSRF
    flash("Utilisateur #$uid supprimé.", 'error');
    redirect('users.php');
}

// Toggle admin — ⚠️ CSRF + privilege escalation
if (isset($_GET['toggle_admin'])) {
    $uid = $_GET['toggle_admin'];
    mysqli_query($conn, "UPDATE users SET is_admin = IF(is_admin=1,0,1) WHERE id=$uid");
    flash('Rôle mis à jour !');
    redirect('users.php');
}

// Update user — ⚠️ Mass assignment + SQLi + CSRF
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_user'])) {
    $uid      = $_POST['id'];
    $username = $_POST['username'];
    $email    = $_POST['email'];
    $password = $_POST['password'];
    $is_admin = $_POST['is_admin'] ?? 0;
    $is_active= $_POST['is_active'] ?? 1;
    // ⚠️ SQLi direct dans UPDATE
    mysqli_query($conn, "UPDATE users SET username='$username', email='$email', password='$password', is_admin=$is_admin, is_active=$is_active WHERE id=$uid");
    flash('Utilisateur mis à jour !');
    redirect('users.php');
}

// Load users — ⚠️ SQLi via search
$search = $_GET['search'] ?? '';
$where  = $search ? "WHERE username LIKE '%$search%' OR email LIKE '%$search%' OR full_name LIKE '%$search%'" : '';
$users  = mysqli_query($conn, "SELECT * FROM users $where ORDER BY id ASC");

$edit_user = null;
if (isset($_GET['edit'])) {
    $eid = $_GET['edit'];
    $edit_user = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM users WHERE id=$eid"));
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Utilisateurs — Admin VulnShop</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&family=Playfair+Display:ital,wght@0,600;1,400&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../assets/css/main.css">
</head>
<body>

<div class="admin-layout">
  <aside class="admin-sidebar">
    <div class="admin-logo">◆ Vuln<span>Shop</span> Admin</div>
    <div class="admin-nav-section">
      <a href="index.php"    class="admin-nav-item">📊 Vue d'ensemble</a>
      <a href="products.php" class="admin-nav-item">🛍 Produits</a>
      <a href="orders.php"   class="admin-nav-item">📦 Commandes</a>
      <a href="users.php"    class="admin-nav-item active">👥 Utilisateurs</a>
    </div>
    <div class="admin-nav-section">
      <a href="../index.php"        class="admin-nav-item">🏠 Boutique</a>
      <a href="../pages/logout.php" class="admin-nav-item" style="color:rgba(239,68,68,.7)">🚪 Déconnexion</a>
    </div>
  </aside>

  <main class="admin-main">
    <div class="admin-header">
      <div>
        <div class="admin-title">Gestion des utilisateurs</div>
        <div class="admin-subtitle"><?= mysqli_num_rows($users) ?> utilisateur(s)</div>
      </div>
    </div>

    <?php $f = getFlash(); if($f): ?>
    <div class="flash flash-<?= $f['type'] ?>" style="margin-bottom:16px;border-radius:8px"><?= $f['msg'] ?> <button onclick="this.parentElement.remove()" class="flash-close">×</button></div>
    <?php endif; ?>

    <!-- Search -->
    <form method="GET" style="display:flex;gap:8px;margin-bottom:16px;max-width:400px">
      <!-- ⚠️ SQLi via search -->
      <input type="text" name="search" class="form-input" style="padding:8px 12px" placeholder="Nom, email, username..." value="<?= htmlspecialchars($search) ?>">
      <button type="submit" class="btn btn-primary btn-sm">Chercher</button>
      <?php if($search): ?><a href="users.php" class="btn btn-outline btn-sm">Effacer</a><?php endif; ?>
    </form>

    <div class="card">
      <div class="table-wrap">
        <table>
          <thead>
            <tr>
              <th>ID</th>
              <th>Utilisateur</th>
              <th>Email</th>
              <th>Mot de passe</th>
              <th>Téléphone</th>
              <th>Rôle</th>
              <th>Statut</th>
              <th>Inscription</th>
              <th>Dernière conn.</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php
            // Reset pointer
            mysqli_data_seek($users, 0);
            while($u = mysqli_fetch_assoc($users)):
            ?>
            <tr>
              <td style="font-family:monospace;font-size:11px;color:var(--gray-400)">#<?= $u['id'] ?></td>
              <td>
                <!-- ⚠️ XSS via username/full_name -->
                <div style="font-weight:500;font-size:13px"><?= $u['full_name'] ?: $u['username'] ?></div>
                <div style="font-size:11px;color:var(--gray-400)">@<?= $u['username'] ?></div>
              </td>
              <td style="font-size:13px"><?= $u['email'] ?></td>
              <!-- ⚠️ Mot de passe affiché en clair -->
              <td style="font-family:monospace;font-size:12px;color:var(--accent);font-weight:500"><?= $u['password'] ?></td>
              <td style="font-size:12px;color:var(--gray-500)"><?= $u['phone'] ?: '—' ?></td>
              <td>
                <span style="font-size:11px;padding:2px 8px;border-radius:10px;font-weight:600;background:<?= $u['is_admin']?'#fef3c7':'var(--gray-100)' ?>;color:<?= $u['is_admin']?'#92400e':'var(--gray-500)' ?>">
                  <?= $u['is_admin'] ? 'Admin' : 'User' ?>
                </span>
              </td>
              <td>
                <span style="font-size:11px;padding:2px 8px;border-radius:10px;background:<?= $u['is_active']?'#dcfce7':'#fee2e2' ?>;color:<?= $u['is_active']?'#166534':'#991b1b' ?>;font-weight:500">
                  <?= $u['is_active'] ? 'Actif' : 'Désactivé' ?>
                </span>
              </td>
              <td style="font-size:11px;color:var(--gray-400)"><?= date('d/m/Y', strtotime($u['created_at'])) ?></td>
              <td style="font-size:11px;color:var(--gray-400)"><?= $u['last_login'] ? date('d/m/Y H:i', strtotime($u['last_login'])) : '—' ?></td>
              <td>
                <div style="display:flex;gap:4px">
                  <a href="users.php?edit=<?= $u['id'] ?>" class="btn btn-ghost btn-sm">Modifier</a>
                  <!-- ⚠️ CSRF — toggle admin sans confirmation robuste -->
                  <a href="users.php?toggle_admin=<?= $u['id'] ?>" class="btn btn-ghost btn-sm"
                     title="<?= $u['is_admin']?'Révoquer admin':'Donner admin' ?>"
                     style="color:var(--gold)"><?= $u['is_admin']?'⬇':'⬆' ?></a>
                  <a href="users.php?delete=<?= $u['id'] ?>"
                     onclick="return confirmDelete('Supprimer <?= $u['username'] ?> ?')"
                     class="btn btn-ghost btn-sm" style="color:var(--accent)">✕</a>
                </div>
              </td>
            </tr>
            <?php endwhile; ?>
          </tbody>
        </table>
      </div>
    </div>
  </main>
</div>

<!-- Modal: Edit User -->
<?php if($edit_user): ?>
<div class="modal-overlay" style="display:flex">
  <div class="modal">
    <button class="modal-close" onclick="window.location='users.php'">×</button>
    <h3 style="font-size:16px;font-weight:600;margin-bottom:20px">Modifier l'utilisateur #<?= $edit_user['id'] ?></h3>
    <form method="POST"> <!-- ⚠️ Pas de CSRF token, mass assignment -->
      <input type="hidden" name="update_user" value="1">
      <input type="hidden" name="id" value="<?= $edit_user['id'] ?>">
      <div class="form-row">
        <div class="form-group">
          <label class="form-label">Username</label>
          <input type="text" name="username" class="form-input" value="<?= htmlspecialchars($edit_user['username']) ?>">
        </div>
        <div class="form-group">
          <label class="form-label">Email</label>
          <input type="email" name="email" class="form-input" value="<?= htmlspecialchars($edit_user['email']) ?>">
        </div>
      </div>
      <div class="form-group">
        <label class="form-label">Mot de passe <span style="color:var(--accent);font-size:11px">(stocké en clair)</span></label>
        <input type="text" name="password" class="form-input" value="<?= htmlspecialchars($edit_user['password']) ?>">
      </div>
      <div class="form-row">
        <div class="form-group">
          <label class="form-label">Rôle</label>
          <select name="is_admin" class="form-select">
            <option value="0" <?= !$edit_user['is_admin']?'selected':'' ?>>Utilisateur</option>
            <!-- ⚠️ Privilege escalation possible -->
            <option value="1" <?= $edit_user['is_admin']?'selected':'' ?>>Administrateur</option>
          </select>
        </div>
        <div class="form-group">
          <label class="form-label">Statut</label>
          <select name="is_active" class="form-select">
            <option value="1" <?= $edit_user['is_active']?'selected':'' ?>>Actif</option>
            <option value="0" <?= !$edit_user['is_active']?'selected':'' ?>>Désactivé</option>
          </select>
        </div>
      </div>
      <div style="display:flex;gap:10px">
        <button type="submit" class="btn btn-primary">Enregistrer</button>
        <a href="users.php" class="btn btn-outline">Annuler</a>
      </div>
    </form>
  </div>
</div>
<?php endif; ?>

<script src="../assets/js/main.js"></script>
</body>
</html>