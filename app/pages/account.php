<?php
$pageTitle = 'Mon Compte';
require_once '../includes/header.php';

if (!isset($_SESSION['user_id'])) {
    flash('Connectez-vous pour accéder à votre compte.', 'error');
    redirect('login.php');
}
$conn = getDB();

// ⚠️ IDOR — l'utilisateur peut voir/modifier n'importe quel compte via ?id=
$target_id = $_GET['id'] ?? $_SESSION['user_id'];
$user = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM users WHERE id=$target_id")); // SQLi + IDOR

if (!$user) {
    flash('Utilisateur introuvable.', 'error');
    redirect('../index.php');
}

// Handle profile update — ⚠️ IDOR + CSRF + mass assignment
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $full_name = $_POST['full_name'];
    $email     = $_POST['email'];
    $phone     = $_POST['phone'];
    $address   = $_POST['address'];
    $password  = $_POST['new_password'];

    $set = "full_name='$full_name', email='$email', phone='$phone', address='$address'";
    if (!empty($password)) {
        $set .= ", password='$password'"; // ⚠️ Stocké en clair
    }
    // ⚠️ IDOR — modifie l'utilisateur ciblé sans vérification
    mysqli_query($conn, "UPDATE users SET $set WHERE id=$target_id");
    flash('Profil mis à jour avec succès !');
    redirect("account.php?id=$target_id");
}

// Orders — ⚠️ IDOR
$orders = mysqli_query($conn, "SELECT * FROM orders WHERE user_id=$target_id ORDER BY created_at DESC LIMIT 10");

$active_tab = $_GET['tab'] ?? 'profile';
?>

<div class="container" style="padding-top:28px;padding-bottom:60px">

  <!-- IDOR warning if viewing another user -->
  <?php if($target_id != $_SESSION['user_id']): ?>
  <div style="background:#fef9e7;border:1px solid #fde68a;border-radius:8px;padding:12px 16px;margin-bottom:20px;font-size:13px;color:#92400e;display:flex;align-items:center;gap:8px">
    ⚠️ <strong>IDOR :</strong> Vous consultez le profil de l'utilisateur #<?= $target_id ?> sans autorisation.
    Essayez d'autres IDs : <?php for($i=1;$i<=4;$i++): ?><a href="account.php?id=<?= $i ?>" style="color:#92400e;font-weight:500;margin:0 3px">#<?= $i ?></a><?php endfor; ?>
  </div>
  <?php endif; ?>

  <div class="page-layout">

    <!-- Sidebar -->
    <aside class="sidebar">
      <div style="padding:20px;text-align:center;border-bottom:1px solid var(--border)">
        <div style="width:64px;height:64px;border-radius:50%;background:var(--gray-200);display:flex;align-items:center;justify-content:center;font-size:26px;margin:0 auto 10px">👤</div>
        <div style="font-weight:600;font-size:15px"><?= $user['full_name'] ?: $user['username'] ?></div>
        <div style="font-size:12px;color:var(--gray-400);margin-top:3px"><?= $user['email'] ?></div>
        <?php if($user['is_admin']): ?>
          <span style="display:inline-block;margin-top:6px;font-size:10px;padding:2px 8px;background:#fef3c7;color:#92400e;border-radius:10px;font-weight:600">ADMIN</span>
        <?php endif; ?>
      </div>
      <div class="sidebar-menu">
        <a href="account.php?tab=profile<?= $target_id!=$_SESSION['user_id']?"&id=$target_id":'' ?>" class="sidebar-item <?= $active_tab=='profile'?'active':'' ?>">
          <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
          Mon profil
        </a>
        <a href="account.php?tab=orders<?= $target_id!=$_SESSION['user_id']?"&id=$target_id":'' ?>" class="sidebar-item <?= $active_tab=='orders'?'active':'' ?>">
          <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/></svg>
          Mes commandes
        </a>
        <a href="wishlist.php" class="sidebar-item">
          <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
          Mes favoris
        </a>
        <div class="sidebar-divider"></div>
        <?php if($_SESSION['is_admin']): ?>
        <a href="../admin/index.php" class="sidebar-item">
          <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>
          Administration
        </a>
        <?php endif; ?>
        <a href="logout.php" class="sidebar-item" style="color:var(--accent)">
          <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
          Déconnexion
        </a>
      </div>
    </aside>

    <!-- Main -->
    <div>

      <?php if($active_tab === 'profile'): ?>
      <!-- Profile info -->
      <div class="card p-24 mb-16">
        <h2 style="font-size:18px;font-weight:600;margin-bottom:20px;padding-bottom:14px;border-bottom:1px solid var(--border)">Informations du profil</h2>

        <!-- ⚠️ Données sensibles exposées -->
        <div style="background:var(--gray-50);border:1px solid var(--border);border-radius:8px;padding:14px;margin-bottom:20px;font-size:13px">
          <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px">
            <div><span style="color:var(--gray-400)">ID utilisateur : </span><span style="font-family:monospace">#<?= $user['id'] ?></span></div>
            <div><span style="color:var(--gray-400)">Rôle : </span><span><?= $user['is_admin']?'<strong>Administrateur</strong>':'Utilisateur' ?></span></div>
            <!-- ⚠️ Mot de passe affiché en clair -->
            <div><span style="color:var(--gray-400)">Mot de passe : </span><span style="color:var(--accent);font-family:monospace"><?= $user['password'] ?></span></div>
            <div><span style="color:var(--gray-400)">Dernière connexion : </span><span><?= $user['last_login'] ? date('d/m/Y H:i', strtotime($user['last_login'])) : 'N/A' ?></span></div>
          </div>
        </div>

        <!-- Update form — ⚠️ IDOR + CSRF -->
        <form method="POST">
          <input type="hidden" name="update_profile" value="1">
          <div class="form-row">
            <div class="form-group">
              <label class="form-label">Nom complet</label>
              <input type="text" name="full_name" class="form-input" value="<?= htmlspecialchars($user['full_name'] ?? '') ?>">
            </div>
            <div class="form-group">
              <label class="form-label">Email</label>
              <input type="email" name="email" class="form-input" value="<?= htmlspecialchars($user['email']) ?>">
            </div>
          </div>
          <div class="form-row">
            <div class="form-group">
              <label class="form-label">Téléphone</label>
              <input type="text" name="phone" class="form-input" value="<?= htmlspecialchars($user['phone'] ?? '') ?>">
            </div>
            <div class="form-group">
              <label class="form-label">Nouveau mot de passe</label>
              <!-- ⚠️ Stocké en clair -->
              <input type="password" name="new_password" class="form-input" placeholder="Laisser vide pour ne pas changer">
            </div>
          </div>
          <div class="form-group">
            <label class="form-label">Adresse de livraison</label>
            <textarea name="address" class="form-textarea" style="height:72px"><?= htmlspecialchars($user['address'] ?? '') ?></textarea>
          </div>
          <button type="submit" class="btn btn-primary">Enregistrer les modifications</button>
        </form>
      </div>

      <?php elseif($active_tab === 'orders'): ?>
      <!-- Orders — ⚠️ IDOR -->
      <div class="card">
        <div style="padding:20px 24px;border-bottom:1px solid var(--border);display:flex;justify-content:space-between;align-items:center">
          <h2 style="font-size:18px;font-weight:600">Mes commandes</h2>
          <?php if($target_id != $_SESSION['user_id']): ?>
            <span style="font-size:11px;padding:3px 8px;background:#fef9e7;color:#92400e;border-radius:4px">⚠️ IDOR — commandes d'un autre user</span>
          <?php endif; ?>
        </div>
        <div class="table-wrap">
          <table>
            <thead>
              <tr>
                <th>N° commande</th>
                <th>Date</th>
                <th>Total</th>
                <th>Statut</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody>
              <?php if(mysqli_num_rows($orders) > 0): ?>
                <?php while($o = mysqli_fetch_assoc($orders)): ?>
                <tr>
                  <td style="font-family:monospace;font-size:12px"><?= $o['order_number'] ?></td>
                  <td style="font-size:13px;color:var(--gray-500)"><?= date('d/m/Y', strtotime($o['created_at'])) ?></td>
                  <td style="font-weight:600"><?= formatPrice($o['total']) ?></td>
                  <td><span class="status-badge status-<?= $o['status'] ?>"><?= ucfirst($o['status']) ?></span></td>
                  <td><a href="order.php?id=<?= $o['id'] ?>" class="btn btn-ghost btn-sm">Voir →</a></td>
                </tr>
                <?php endwhile; ?>
              <?php else: ?>
                <tr><td colspan="5" style="text-align:center;padding:40px;color:var(--gray-400)">Aucune commande</td></tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
      <?php endif; ?>

    </div>
  </div>
</div>

<?php require_once '../includes/footer.php'; ?>