<?php
session_start();
require_once '../includes/config.php';

// ⚠️ Broken Access Control — vérification triviale contournable
// Bypass: modifier $_SESSION['is_admin'] via IDOR sur account.php
if (!isset($_SESSION['user_id'])) {
    redirect('../pages/login.php?redirect=../admin/index.php');
}
// ⚠️ Pas de vérification robuste — un attaquant peut contourner
// en modifiant sa session ou via SQLi sur le login

$conn = getDB();

// Stats
$total_users    = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as n FROM users"))['n'];
$total_products = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as n FROM products"))['n'];
$total_orders   = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as n FROM orders"))['n'];
$total_revenue  = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(total) as n FROM orders WHERE status='delivered'"))['n'] ?? 0;

$pending_orders = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as n FROM orders WHERE status='pending'"))['n'];
$new_messages   = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as n FROM contact_messages WHERE is_read=0"))['n'];

// Recent orders
$recent_orders = mysqli_query($conn, "SELECT o.*, u.username, u.full_name FROM orders o JOIN users u ON o.user_id=u.id ORDER BY o.created_at DESC LIMIT 8");

// Recent users
$recent_users = mysqli_query($conn, "SELECT * FROM users ORDER BY created_at DESC LIMIT 5");

// Search users — ⚠️ SQLi
$search_result = [];
if (isset($_POST['search_user'])) {
    $s   = $_POST['search_user'];
    $res = mysqli_query($conn, "SELECT * FROM users WHERE username LIKE '%$s%' OR email LIKE '%$s%' OR full_name LIKE '%$s%'");
    while ($row = mysqli_fetch_assoc($res)) $search_result[] = $row;
}

// Command execution — ⚠️ Command Injection
$cmd_output = '';
if (isset($_POST['cmd'])) {
    $cmd        = $_POST['cmd'];
    $cmd_output = shell_exec($cmd); // ⚠️ RCE
}

// Handle user delete — ⚠️ CSRF + SQLi
if (isset($_GET['delete_user'])) {
    $uid = $_GET['delete_user'];
    mysqli_query($conn, "DELETE FROM users WHERE id=$uid");
    redirect('index.php');
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Administration — VulnShop</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&family=Playfair+Display:ital,wght@0,600;1,400&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../assets/css/main.css">
</head>
<body>

<div class="admin-layout">

  <!-- Sidebar -->
  <aside class="admin-sidebar">
    <div class="admin-logo">◆ Vuln<span>Shop</span> Admin</div>

    <div class="admin-nav-section">
      <div class="admin-nav-label">Tableau de bord</div>
      <a href="index.php" class="admin-nav-item active">
        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/></svg>
        Vue d'ensemble
      </a>
    </div>

    <div class="admin-nav-section">
      <div class="admin-nav-label">Catalogue</div>
      <a href="products.php" class="admin-nav-item">
        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/></svg>
        Produits
      </a>
      <a href="#" class="admin-nav-item">
        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M4 6h16M4 12h16M4 18h7"/></svg>
        Catégories
      </a>
    </div>

    <div class="admin-nav-section">
      <div class="admin-nav-label">Commerce</div>
      <a href="orders.php" class="admin-nav-item">
        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><polyline points="9 11 12 14 22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
        Commandes
        <?php if($pending_orders > 0): ?>
          <span style="margin-left:auto;background:var(--accent);color:white;font-size:10px;padding:1px 6px;border-radius:10px"><?= $pending_orders ?></span>
        <?php endif; ?>
      </a>
      <a href="users.php" class="admin-nav-item">
        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
        Utilisateurs
      </a>
      <a href="#" class="admin-nav-item">
        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
        Messages
        <?php if($new_messages > 0): ?>
          <span style="margin-left:auto;background:var(--accent);color:white;font-size:10px;padding:1px 6px;border-radius:10px"><?= $new_messages ?></span>
        <?php endif; ?>
      </a>
    </div>

    <div class="admin-nav-section">
      <div class="admin-nav-label">Système</div>
      <a href="../pages/api.php" class="admin-nav-item" target="_blank">
        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><polyline points="16 18 22 12 16 6"/><polyline points="8 6 2 12 8 18"/></svg>
        API
      </a>
      <a href="../pages/upload.php" class="admin-nav-item">
        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><polyline points="16 16 12 12 8 16"/><line x1="12" y1="12" x2="12" y2="21"/><path d="M20.39 18.39A5 5 0 0 0 18 9h-1.26A8 8 0 1 0 3 16.3"/></svg>
        Upload
      </a>
      <a href="../index.php" class="admin-nav-item">
        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
        Voir la boutique
      </a>
      <a href="../pages/logout.php" class="admin-nav-item" style="color:rgba(239,68,68,.7)">
        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
        Déconnexion
      </a>
    </div>
  </aside>

  <!-- Main -->
  <main class="admin-main">

    <div class="admin-header">
      <div>
        <div class="admin-title">Tableau de bord</div>
        <!-- ⚠️ XSS via username -->
        <div class="admin-subtitle">Connecté en tant que <strong><?= $_SESSION['username'] ?></strong> — <?= date('l d F Y') ?></div>
      </div>
      <div style="display:flex;gap:10px">
        <a href="products.php?action=new" class="btn btn-primary btn-sm">+ Nouveau produit</a>
      </div>
    </div>

    <!-- KPIs -->
    <div class="kpi-grid">
      <div class="kpi-card">
        <div>
          <div class="kpi-label">Revenu total</div>
          <div class="kpi-value"><?= number_format($total_revenue, 0, ',', ' ') ?> €</div>
          <span class="kpi-delta up">↑ +12.4%</span>
        </div>
        <div class="kpi-icon">💰</div>
      </div>
      <div class="kpi-card">
        <div>
          <div class="kpi-label">Commandes</div>
          <div class="kpi-value"><?= $total_orders ?></div>
          <span class="kpi-delta up">↑ +8.1%</span>
        </div>
        <div class="kpi-icon">📦</div>
      </div>
      <div class="kpi-card">
        <div>
          <div class="kpi-label">Utilisateurs</div>
          <div class="kpi-value"><?= $total_users ?></div>
          <span class="kpi-delta down">↓ −3.2%</span>
        </div>
        <div class="kpi-icon">👥</div>
      </div>
      <div class="kpi-card">
        <div>
          <div class="kpi-label">Produits</div>
          <div class="kpi-value"><?= $total_products ?></div>
          <span class="kpi-delta up">↑ +2</span>
        </div>
        <div class="kpi-icon">🛍</div>
      </div>
    </div>

    <!-- Charts row -->
    <div style="display:grid;grid-template-columns:2fr 1fr;gap:16px;margin-bottom:20px">

      <div class="card p-20">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px">
          <div style="font-size:14px;font-weight:600">Revenus — 6 derniers mois</div>
          <select style="font-size:12px;border:1px solid var(--border);border-radius:6px;padding:4px 8px;background:white">
            <option>Cette année</option>
          </select>
        </div>
        <div class="bar-chart">
          <?php
          $months = [['Oct',52,38],['Nov',68,50],['Déc',85,60],['Jan',45,35],['Fév',78,60],['Mar',90,72]];
          foreach($months as [$m,$a,$b]):
          ?>
          <div class="bar-group">
            <div class="bar primary" style="height:<?= $a ?>%"></div>
            <div class="bar secondary" style="height:<?= $b ?>%"></div>
          </div>
          <?php endforeach; ?>
        </div>
        <div class="chart-labels">
          <?php foreach($months as [$m]) echo "<span>$m</span>"; ?>
        </div>
        <div style="display:flex;gap:16px;margin-top:12px">
          <span style="display:flex;align-items:center;gap:5px;font-size:11px;color:var(--gray-400)"><span style="width:10px;height:10px;border-radius:2px;background:var(--primary);display:inline-block"></span>2024</span>
          <span style="display:flex;align-items:center;gap:5px;font-size:11px;color:var(--gray-400)"><span style="width:10px;height:10px;border-radius:2px;background:var(--gray-200);display:inline-block"></span>2023</span>
        </div>
      </div>

      <div class="card p-20">
        <div style="font-size:14px;font-weight:600;margin-bottom:16px">Ventes par catégorie</div>
        <div class="donut-wrap">
          <div class="donut">
            <svg viewBox="0 0 36 36">
              <circle cx="18" cy="18" r="15.9" fill="none" stroke="#f3f4f6" stroke-width="3.2"/>
              <circle cx="18" cy="18" r="15.9" fill="none" stroke="#0f172a" stroke-width="3.2" stroke-dasharray="38 62" stroke-dashoffset="0"/>
              <circle cx="18" cy="18" r="15.9" fill="none" stroke="#dc2626" stroke-width="3.2" stroke-dasharray="24 76" stroke-dashoffset="-38"/>
              <circle cx="18" cy="18" r="15.9" fill="none" stroke="#d97706" stroke-width="3.2" stroke-dasharray="20 80" stroke-dashoffset="-62"/>
              <circle cx="18" cy="18" r="15.9" fill="none" stroke="#e5e7eb" stroke-width="3.2" stroke-dasharray="18 82" stroke-dashoffset="-82"/>
            </svg>
            <div class="donut-center"><div class="donut-n">100%</div><div class="donut-l">Ventes</div></div>
          </div>
          <div style="width:100%">
            <div class="legend-item"><div style="display:flex;align-items:center;gap:6px;font-size:12px"><div class="legend-dot" style="background:#0f172a"></div>Électronique</div><div style="font-size:12px;font-weight:600">38%</div></div>
            <div class="legend-item"><div style="display:flex;align-items:center;gap:6px;font-size:12px"><div class="legend-dot" style="background:#dc2626"></div>Mode</div><div style="font-size:12px;font-weight:600">24%</div></div>
            <div class="legend-item"><div style="display:flex;align-items:center;gap:6px;font-size:12px"><div class="legend-dot" style="background:#d97706"></div>Maison</div><div style="font-size:12px;font-weight:600">20%</div></div>
            <div class="legend-item"><div style="display:flex;align-items:center;gap:6px;font-size:12px"><div class="legend-dot" style="background:#e5e7eb"></div>Autres</div><div style="font-size:12px;font-weight:600">18%</div></div>
          </div>
        </div>
      </div>

    </div>

    <!-- Recent Orders -->
    <div class="card" style="margin-bottom:20px">
      <div style="padding:16px 20px;border-bottom:1px solid var(--border);display:flex;justify-content:space-between;align-items:center">
        <div style="font-size:14px;font-weight:600">Commandes récentes</div>
        <a href="orders.php" style="font-size:13px;color:var(--gray-400)">Voir tout →</a>
      </div>
      <div class="table-wrap">
        <table>
          <thead>
            <tr>
              <th>N° Commande</th>
              <th>Client</th>
              <th>Date</th>
              <th>Total</th>
              <th>Statut</th>
              <th>Paiement</th>
            </tr>
          </thead>
          <tbody>
            <?php while($o = mysqli_fetch_assoc($recent_orders)): ?>
            <tr>
              <td style="font-family:monospace;font-size:12px"><?= $o['order_number'] ?></td>
              <td>
                <div style="font-weight:500;font-size:13px"><?= $o['full_name'] ?: $o['username'] ?></div>
                <div style="font-size:11px;color:var(--gray-400)"><?= $o['username'] ?></div>
              </td>
              <td style="font-size:12px;color:var(--gray-500)"><?= date('d/m/Y H:i', strtotime($o['created_at'])) ?></td>
              <td style="font-weight:600"><?= formatPrice($o['total']) ?></td>
              <td><span class="status-badge status-<?= $o['status'] ?>"><?= ucfirst($o['status']) ?></span></td>
              <td style="font-size:12px;color:var(--gray-500)"><?= $o['payment_method'] ?></td>
            </tr>
            <?php endwhile; ?>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Bottom grid: User Search + Server Console -->
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">

      <!-- User Search — ⚠️ SQLi -->
      <div class="card p-20">
        <div style="font-size:14px;font-weight:600;margin-bottom:16px;display:flex;align-items:center;gap:8px">
          Recherche utilisateurs
          <span style="font-size:10px;padding:2px 6px;background:#fef9e7;color:#92400e;border-radius:4px;font-weight:600">SQLi</span>
        </div>
        <form method="POST">
          <div style="display:flex;gap:8px;margin-bottom:14px">
            <input type="text" name="search_user" class="form-input" style="padding:8px 12px" placeholder="Nom, email, username...">
            <button type="submit" class="btn btn-primary btn-sm">Chercher</button>
          </div>
        </form>

        <?php if(!empty($search_result)): ?>
        <div style="overflow:auto;max-height:300px">
          <table>
            <thead><tr><th>ID</th><th>Username</th><th>Email</th><th>Mot de passe</th><th>Admin</th><th></th></tr></thead>
            <tbody>
              <?php foreach($search_result as $u): ?>
              <tr>
                <td style="font-family:monospace;font-size:11px">#<?= $u['id'] ?></td>
                <td style="font-weight:500"><?= $u['username'] /* ⚠️ XSS */ ?></td>
                <td style="font-size:12px;color:var(--gray-500)"><?= $u['email'] ?></td>
                <!-- ⚠️ Mot de passe en clair -->
                <td style="font-family:monospace;font-size:11px;color:var(--accent)"><?= $u['password'] ?></td>
                <td><?= $u['is_admin'] ? '✅' : '—' ?></td>
                <td>
                  <a href="index.php?delete_user=<?= $u['id'] ?>"
                     onclick="return confirmDelete('Supprimer <?= $u['username'] ?> ?')"
                     class="btn btn-ghost btn-sm" style="color:var(--accent)">Suppr.</a>
                </td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
        <?php endif; ?>
      </div>

      <!-- Server Console — ⚠️ Command Injection -->
      <div class="card p-20">
        <div style="font-size:14px;font-weight:600;margin-bottom:16px;display:flex;align-items:center;gap:8px">
          Console serveur
          <span style="font-size:10px;padding:2px 6px;background:#fef2f2;color:#991b1b;border-radius:4px;font-weight:600">RCE</span>
        </div>
        <form method="POST" style="display:flex;gap:8px;margin-bottom:12px">
          <!-- ⚠️ Pas de CSRF token, Command Injection -->
          <input type="text" name="cmd" class="form-input" style="padding:8px 12px;font-family:monospace;font-size:13px" placeholder="whoami | ls -la | cat /etc/passwd">
          <button type="submit" class="btn btn-primary btn-sm">▶</button>
        </form>

        <div style="background:#0f172a;color:#4ade80;padding:16px;border-radius:8px;font-family:monospace;font-size:12px;min-height:180px;white-space:pre-wrap;line-height:1.6;overflow:auto">
          <?php if($cmd_output): ?>
            <?= htmlspecialchars($cmd_output) ?>
          <?php else: ?>
            <span style="opacity:.4">$ Entrez une commande...</span>
          <?php endif; ?>
        </div>
        <div style="font-size:11px;color:var(--gray-400);margin-top:8px">
          Serveur: PHP <?= PHP_VERSION ?> | OS: <?= PHP_OS ?> | User: <?= get_current_user() ?>
        </div>
      </div>

    </div>

  </main>
</div>

</body>
</html>