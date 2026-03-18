<?php
session_start();
require_once '../includes/config.php';
if (!isset($_SESSION['user_id'])) redirect('../pages/login.php');
$conn = getDB();

// Handle create/update/delete — ⚠️ SQLi + CSRF
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'create' || $action === 'update') {
        $name        = $_POST['name'];
        $desc        = $_POST['description'];   // ⚠️ XSS stocké
        $price       = $_POST['price'];
        $orig_price  = $_POST['original_price'] ?: 'NULL';
        $stock       = $_POST['stock'];
        $sku         = $_POST['sku'];
        $cat         = $_POST['category_id'];
        $featured    = isset($_POST['is_featured']) ? 1 : 0;

        // ⚠️ SQLi dans les valeurs
        if ($action === 'create') {
            mysqli_query($conn, "INSERT INTO products (category_id, name, description, price, original_price, stock, sku, is_featured) VALUES ($cat, '$name', '$desc', $price, " . ($orig_price === 'NULL' ? 'NULL' : "'$orig_price'") . ", $stock, '$sku', $featured)");
            flash('Produit créé avec succès !');
        } else {
            $id = (int)$_POST['id'];
            mysqli_query($conn, "UPDATE products SET category_id=$cat, name='$name', description='$desc', price=$price, stock=$stock, sku='$sku', is_featured=$featured WHERE id=$id");
            flash('Produit mis à jour !');
        }
        redirect('products.php');
    }

    if ($action === 'delete') {
        $id = (int)$_POST['id'];
        mysqli_query($conn, "DELETE FROM products WHERE id=$id"); // ⚠️ CSRF
        flash('Produit supprimé.', 'error');
        redirect('products.php');
    }
}

// Load products — ⚠️ SQLi via sort/filter
$sort   = $_GET['sort']   ?? 'id';
$filter = $_GET['filter'] ?? '';
$where  = $filter ? "WHERE p.category_id=$filter" : '';
$products = mysqli_query($conn, "SELECT p.*, c.name as cat_name FROM products p JOIN categories c ON p.category_id=c.id $where ORDER BY p.$sort DESC");
$categories = mysqli_query($conn, "SELECT * FROM categories ORDER BY name");

$edit_product = null;
if (isset($_GET['edit'])) {
    $edit_id = $_GET['edit'];
    // ⚠️ SQLi
    $edit_product = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM products WHERE id=$edit_id"));
}

$icons = ['💻','📱','📱','🎧','📱','👟','👖','🧥','🛋','🍳','🚲','💄'];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Produits — Admin VulnShop</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&family=Playfair+Display:ital,wght@0,600;1,400&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../assets/css/main.css">
</head>
<body>

<div class="admin-layout">
  <aside class="admin-sidebar">
    <div class="admin-logo">◆ Vuln<span>Shop</span> Admin</div>
    <div class="admin-nav-section">
      <div class="admin-nav-label">Catalogue</div>
      <a href="index.php"    class="admin-nav-item">📊 Vue d'ensemble</a>
      <a href="products.php" class="admin-nav-item active">🛍 Produits</a>
      <a href="orders.php"   class="admin-nav-item">📦 Commandes</a>
      <a href="users.php"    class="admin-nav-item">👥 Utilisateurs</a>
    </div>
    <div class="admin-nav-section">
      <a href="../index.php"          class="admin-nav-item">🏠 Boutique</a>
      <a href="../pages/logout.php"   class="admin-nav-item" style="color:rgba(239,68,68,.7)">🚪 Déconnexion</a>
    </div>
  </aside>

  <main class="admin-main">
    <div class="admin-header">
      <div>
        <div class="admin-title">Gestion des produits</div>
        <div class="admin-subtitle"><?= mysqli_num_rows($products) ?> produit(s) au total</div>
      </div>
      <button onclick="document.getElementById('modal-product').classList.add('open')" class="btn btn-primary btn-sm">+ Nouveau produit</button>
    </div>

    <!-- Flash -->
    <?php $f = getFlash(); if($f): ?>
    <div class="flash flash-<?= $f['type'] ?>" style="margin-bottom:16px;border-radius:8px"><?= $f['msg'] ?> <button onclick="this.parentElement.remove()" class="flash-close">×</button></div>
    <?php endif; ?>

    <!-- Filters -->
    <div style="display:flex;gap:10px;margin-bottom:16px;align-items:center">
      <a href="products.php" class="btn btn-sm <?= !$filter?'btn-primary':'btn-outline' ?>">Tous</a>
      <?php
      $cats_tmp = mysqli_query($conn, "SELECT * FROM categories ORDER BY name");
      while($c = mysqli_fetch_assoc($cats_tmp)):
      ?>
      <a href="products.php?filter=<?= $c['id'] ?>" class="btn btn-sm <?= $filter==$c['id']?'btn-primary':'btn-outline' ?>"><?= $c['name'] ?></a>
      <?php endwhile; ?>
      <div style="margin-left:auto;display:flex;align-items:center;gap:8px;font-size:13px;color:var(--gray-500)">
        Trier :
        <select onchange="location.href='products.php?sort='+this.value+'<?= $filter?"&filter=$filter":'' ?>'" style="padding:6px 10px;border:1px solid var(--border);border-radius:6px;font-size:13px;background:white">
          <option value="id"    <?= $sort=='id'?'selected':'' ?>>Plus récents</option>
          <option value="price" <?= $sort=='price'?'selected':'' ?>>Prix</option>
          <option value="stock" <?= $sort=='stock'?'selected':'' ?>>Stock</option>
          <option value="name"  <?= $sort=='name'?'selected':'' ?>>Nom</option>
        </select>
      </div>
    </div>

    <!-- Products table -->
    <div class="card">
      <div class="table-wrap">
        <table>
          <thead>
            <tr>
              <th>Produit</th>
              <th>SKU</th>
              <th>Catégorie</th>
              <th>Prix</th>
              <th>Stock</th>
              <th>Note</th>
              <th>Mis en avant</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php while($p = mysqli_fetch_assoc($products)): ?>
            <tr>
              <td>
                <div style="display:flex;align-items:center;gap:10px">
                  <div style="width:40px;height:40px;background:var(--gray-100);border-radius:6px;display:flex;align-items:center;justify-content:center;font-size:18px;flex-shrink:0">
                    <?= $icons[$p['id']-1] ?? '🛍' ?>
                  </div>
                  <div>
                    <div style="font-weight:500;font-size:13px"><?= $p['name'] ?></div>
                    <div style="font-size:11px;color:var(--gray-400)"><?= truncate($p['description'],50) ?></div>
                  </div>
                </div>
              </td>
              <td style="font-family:monospace;font-size:11px;color:var(--gray-500)"><?= $p['sku'] ?></td>
              <td style="font-size:13px"><?= $p['cat_name'] ?></td>
              <td>
                <div style="font-weight:600;font-size:13px"><?= formatPrice($p['price']) ?></div>
                <?php if($p['original_price']): ?>
                  <div style="font-size:11px;color:var(--gray-400);text-decoration:line-through"><?= formatPrice($p['original_price']) ?></div>
                <?php endif; ?>
              </td>
              <td>
                <span style="font-size:13px;font-weight:500;color:<?= $p['stock']>10?'var(--success)':($p['stock']>0?'var(--warning)':'var(--accent)') ?>"><?= $p['stock'] ?></span>
              </td>
              <td style="color:var(--gold)">
                <?= number_format($p['rating'],1) ?> ★
                <div style="font-size:11px;color:var(--gray-400)"><?= $p['review_count'] ?> avis</div>
              </td>
              <td style="text-align:center"><?= $p['is_featured'] ? '⭐' : '—' ?></td>
              <td>
                <div style="display:flex;gap:6px">
                  <a href="products.php?edit=<?= $p['id'] ?>" class="btn btn-ghost btn-sm">Modifier</a>
                  <form method="POST" style="display:inline">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" value="<?= $p['id'] ?>">
                    <button type="submit" class="btn btn-ghost btn-sm" style="color:var(--accent)"
                            onclick="return confirmDelete('Supprimer ce produit ?')">Suppr.</button>
                  </form>
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

<!-- Modal: Create/Edit Product -->
<div class="modal-overlay" id="modal-product" <?= $edit_product?'style="display:flex"':'' ?>>
  <div class="modal" style="max-width:560px">
    <button class="modal-close" onclick="document.getElementById('modal-product').classList.remove('open')">×</button>
    <h3 style="font-size:16px;font-weight:600;margin-bottom:20px">
      <?= $edit_product ? 'Modifier le produit' : 'Nouveau produit' ?>
    </h3>
    <form method="POST"> <!-- ⚠️ Pas de CSRF token -->
      <input type="hidden" name="action" value="<?= $edit_product?'update':'create' ?>">
      <?php if($edit_product): ?>
        <input type="hidden" name="id" value="<?= $edit_product['id'] ?>">
      <?php endif; ?>
      <div class="form-row">
        <div class="form-group">
          <label class="form-label">Nom du produit *</label>
          <input type="text" name="name" class="form-input" required value="<?= htmlspecialchars($edit_product['name'] ?? '') ?>">
        </div>
        <div class="form-group">
          <label class="form-label">SKU</label>
          <input type="text" name="sku" class="form-input" value="<?= htmlspecialchars($edit_product['sku'] ?? '') ?>">
        </div>
      </div>
      <div class="form-group">
        <label class="form-label">Description</label>
        <!-- ⚠️ Stored XSS via description -->
        <textarea name="description" class="form-textarea"><?= $edit_product['description'] ?? '' ?></textarea>
      </div>
      <div class="form-row">
        <div class="form-group">
          <label class="form-label">Prix *</label>
          <input type="number" step="0.01" name="price" class="form-input" required value="<?= $edit_product['price'] ?? '' ?>">
        </div>
        <div class="form-group">
          <label class="form-label">Prix original (barré)</label>
          <input type="number" step="0.01" name="original_price" class="form-input" value="<?= $edit_product['original_price'] ?? '' ?>">
        </div>
      </div>
      <div class="form-row">
        <div class="form-group">
          <label class="form-label">Stock</label>
          <input type="number" name="stock" class="form-input" value="<?= $edit_product['stock'] ?? 0 ?>">
        </div>
        <div class="form-group">
          <label class="form-label">Catégorie *</label>
          <select name="category_id" class="form-select">
            <?php
            $cats = mysqli_query($conn, "SELECT * FROM categories ORDER BY name");
            while($c = mysqli_fetch_assoc($cats)):
            ?>
            <option value="<?= $c['id'] ?>" <?= ($edit_product['category_id']??'')==$c['id']?'selected':'' ?>><?= $c['name'] ?></option>
            <?php endwhile; ?>
          </select>
        </div>
      </div>
      <div style="display:flex;align-items:center;gap:8px;margin-bottom:18px">
        <input type="checkbox" name="is_featured" id="featured" <?= ($edit_product['is_featured']??0)?'checked':'' ?>>
        <label for="featured" style="font-size:13px;color:var(--gray-700)">Produit mis en avant sur la page d'accueil</label>
      </div>
      <div style="display:flex;gap:10px">
        <button type="submit" class="btn btn-primary"><?= $edit_product ? 'Mettre à jour' : 'Créer le produit' ?></button>
        <button type="button" onclick="document.getElementById('modal-product').classList.remove('open')" class="btn btn-outline">Annuler</button>
      </div>
    </form>
  </div>
</div>

<script src="../assets/js/main.js"></script>
</body>
</html>