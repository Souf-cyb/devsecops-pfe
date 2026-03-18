<?php
session_start();
require_once '../includes/config.php';
if (!isset($_SESSION['user_id'])) redirect('../pages/login.php');
$conn = getDB();

// Update order status — ⚠️ SQLi + CSRF
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order_id'])) {
    $order_id = $_POST['order_id'];
    $status   = $_POST['status'];
    // ⚠️ SQLi + pas de CSRF
    mysqli_query($conn, "UPDATE orders SET status='$status' WHERE id=$order_id");
    flash('Statut mis à jour !');
    redirect('orders.php');
}

// Load orders — ⚠️ SQLi via ?search=
$search = $_GET['search'] ?? '';
$status_filter = $_GET['status'] ?? '';
$where = '1=1';
if ($search)        $where .= " AND (o.order_number LIKE '%$search%' OR u.username LIKE '%$search%' OR u.email LIKE '%$search%')";
if ($status_filter) $where .= " AND o.status='$status_filter'";

$orders = mysqli_query($conn, "SELECT o.*, u.username, u.full_name, u.email FROM orders o JOIN users u ON o.user_id=u.id WHERE $where ORDER BY o.created_at DESC");
$statuses = ['pending','processing','shipped','delivered','cancelled'];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Commandes — Admin VulnShop</title>
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
      <a href="orders.php"   class="admin-nav-item active">📦 Commandes</a>
      <a href="users.php"    class="admin-nav-item">👥 Utilisateurs</a>
    </div>
    <div class="admin-nav-section">
      <a href="../index.php"        class="admin-nav-item">🏠 Boutique</a>
      <a href="../pages/logout.php" class="admin-nav-item" style="color:rgba(239,68,68,.7)">🚪 Déconnexion</a>
    </div>
  </aside>

  <main class="admin-main">
    <div class="admin-header">
      <div>
        <div class="admin-title">Gestion des commandes</div>
        <div class="admin-subtitle"><?= mysqli_num_rows($orders) ?> commande(s)</div>
      </div>
    </div>

    <?php $f = getFlash(); if($f): ?>
    <div class="flash flash-<?= $f['type'] ?>" style="margin-bottom:16px;border-radius:8px"><?= $f['msg'] ?> <button onclick="this.parentElement.remove()" class="flash-close">×</button></div>
    <?php endif; ?>

    <!-- Search + Filters -->
    <div style="display:flex;gap:10px;margin-bottom:16px;align-items:center">
      <form method="GET" style="display:flex;gap:8px;flex:1;max-width:400px">
        <!-- ⚠️ SQLi via search -->
        <input type="text" name="search" class="form-input" style="padding:8px 12px" placeholder="N° commande, client, email..." value="<?= htmlspecialchars($search) ?>">
        <button type="submit" class="btn btn-primary btn-sm">Chercher</button>
      </form>
      <div style="display:flex;gap:6px">
        <a href="orders.php" class="btn btn-sm <?= !$status_filter?'btn-primary':'btn-outline' ?>">Toutes</a>
        <?php foreach($statuses as $s): ?>
        <a href="orders.php?status=<?= $s ?><?= $search?"&search=$search":'' ?>" class="btn btn-sm <?= $status_filter==$s?'btn-primary':'btn-outline' ?>"><?= ucfirst($s) ?></a>
        <?php endforeach; ?>
      </div>
    </div>

    <div class="card">
      <div class="table-wrap">
        <table>
          <thead>
            <tr>
              <th>N° Commande</th>
              <th>Client</th>
              <th>Date</th>
              <th>Articles</th>
              <th>Total</th>
              <th>Paiement</th>
              <th>Statut</th>
              <th>Changer statut</th>
            </tr>
          </thead>
          <tbody>
            <?php if(mysqli_num_rows($orders) > 0): ?>
              <?php while($o = mysqli_fetch_assoc($orders)): ?>
              <?php
              $items_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as n FROM order_items WHERE order_id={$o['id']}"))['n'];
              ?>
              <tr>
                <td style="font-family:monospace;font-size:12px;font-weight:500"><?= $o['order_number'] ?></td>
                <td>
                  <!-- ⚠️ XSS via full_name/username si injecté -->
                  <div style="font-weight:500;font-size:13px"><?= $o['full_name'] ?: $o['username'] ?></div>
                  <div style="font-size:11px;color:var(--gray-400)"><?= $o['email'] ?></div>
                </td>
                <td style="font-size:12px;color:var(--gray-500)"><?= date('d/m/Y H:i', strtotime($o['created_at'])) ?></td>
                <td style="font-size:13px"><?= $items_count ?> article(s)</td>
                <td style="font-weight:600"><?= formatPrice($o['total']) ?></td>
                <td style="font-size:12px;color:var(--gray-500)"><?= $o['payment_method'] ?></td>
                <td><span class="status-badge status-<?= $o['status'] ?>"><?= ucfirst($o['status']) ?></span></td>
                <td>
                  <form method="POST" style="display:flex;gap:6px;align-items:center">
                    <!-- ⚠️ Pas de CSRF token -->
                    <input type="hidden" name="order_id" value="<?= $o['id'] ?>">
                    <select name="status" style="padding:5px 8px;border:1px solid var(--border);border-radius:6px;font-size:12px;background:white">
                      <?php foreach($statuses as $s): ?>
                      <option value="<?= $s ?>" <?= $o['status']==$s?'selected':'' ?>><?= ucfirst($s) ?></option>
                      <?php endforeach; ?>
                    </select>
                    <button type="submit" class="btn btn-primary btn-sm" style="padding:5px 10px">OK</button>
                  </form>
                </td>
              </tr>
              <?php endwhile; ?>
            <?php else: ?>
              <tr><td colspan="8" style="text-align:center;padding:40px;color:var(--gray-400)">Aucune commande</td></tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </main>
</div>

<script src="../assets/js/main.js"></script>
</body>
</html>