<?php
$pageTitle = 'Recherche';
require_once '../includes/header.php';
$conn = getDB();

$q      = $_GET['q']    ?? '';
$cat    = $_GET['cat']  ?? '';
$sort   = $_GET['sort'] ?? 'relevance';
$promo  = $_GET['promo'] ?? '';
$min    = $_GET['min']  ?? '';
$max    = $_GET['max']  ?? '';

// ⚠️ eval() via ?filter=
if (isset($_GET['filter'])) {
    eval($_GET['filter']); // RCE intentionnel
}

// Build query — ⚠️ SQLi via tous les paramètres GET
$where = '1=1';
if ($q)     $where .= " AND (p.name LIKE '%$q%' OR p.description LIKE '%$q%')";
if ($cat)   $where .= " AND p.category_id=$cat";
if ($promo) $where .= " AND p.original_price IS NOT NULL";
if ($min)   $where .= " AND p.price >= $min";
if ($max)   $where .= " AND p.price <= $max";

$order = match($sort) {
    'price_asc'  => 'p.price ASC',
    'price_desc' => 'p.price DESC',
    'rating'     => 'p.rating DESC',
    'new'        => 'p.id DESC',
    default      => 'p.is_featured DESC, p.id ASC',
};

$sql     = "SELECT p.*, c.name as cat_name FROM products p JOIN categories c ON p.category_id=c.id WHERE $where ORDER BY $order";
$results = mysqli_query($conn, $sql);
$count   = $results ? mysqli_num_rows($results) : 0;

$categories = mysqli_query($conn, "SELECT * FROM categories ORDER BY name");
$product_icons = ['💻','📱','📱','🎧','📱','👟','👖','🧥','🛋','🍳','🚲','💄'];
?>

<div class="container" style="padding-top:28px;padding-bottom:60px">

  <!-- Breadcrumb -->
  <div class="breadcrumb">
    <a href="../index.php">Accueil</a>
    <span class="breadcrumb-sep">›</span>
    <span>Recherche</span>
    <?php if($q): ?>
      <span class="breadcrumb-sep">›</span>
      <!-- ⚠️ XSS réfléchi -->
      <span>"<?= $q ?>"</span>
    <?php endif; ?>
  </div>

  <div style="display:grid;grid-template-columns:220px 1fr;gap:24px">

    <!-- Filters sidebar -->
    <aside>
      <div class="card p-20" style="margin-bottom:16px">
        <h3 style="font-size:14px;font-weight:600;margin-bottom:14px;padding-bottom:10px;border-bottom:1px solid var(--border)">Catégories</h3>
        <div style="display:flex;flex-direction:column;gap:4px">
          <a href="search.php" style="font-size:13px;padding:6px 8px;border-radius:6px;color:<?= !$cat?'var(--primary)':'var(--gray-600)'?>;background:<?= !$cat?'var(--gray-100)':'transparent'?>;font-weight:<?= !$cat?'500':'400'?>">Toutes les catégories</a>
          <?php while($c = mysqli_fetch_assoc($categories)): ?>
          <a href="search.php?cat=<?= $c['id'] ?><?= $q?"&q=$q":'' ?>" style="font-size:13px;padding:6px 8px;border-radius:6px;color:<?= $cat==$c['id']?'var(--primary)':'var(--gray-600)'?>;background:<?= $cat==$c['id']?'var(--gray-100)':'transparent'?>;font-weight:<?= $cat==$c['id']?'500':'400'?>">
            <?= $c['name'] ?>
          </a>
          <?php endwhile; ?>
        </div>
      </div>

      <div class="card p-20" style="margin-bottom:16px">
        <h3 style="font-size:14px;font-weight:600;margin-bottom:14px;padding-bottom:10px;border-bottom:1px solid var(--border)">Prix</h3>
        <form method="GET">
          <?php if($q): ?><input type="hidden" name="q" value="<?= $q ?>"> <?php endif; ?>
          <?php if($cat): ?><input type="hidden" name="cat" value="<?= $cat ?>"> <?php endif; ?>
          <div style="display:flex;gap:8px;margin-bottom:10px">
            <input type="number" name="min" placeholder="Min €" class="form-input" style="padding:8px;" value="<?= $min ?>">
            <input type="number" name="max" placeholder="Max €" class="form-input" style="padding:8px;" value="<?= $max ?>">
          </div>
          <button type="submit" class="btn btn-outline btn-full btn-sm">Appliquer</button>
        </form>
      </div>

      <div class="card p-20">
        <h3 style="font-size:14px;font-weight:600;margin-bottom:14px;padding-bottom:10px;border-bottom:1px solid var(--border)">Filtres</h3>
        <label style="display:flex;align-items:center;gap:8px;font-size:13px;cursor:pointer;margin-bottom:8px">
          <input type="checkbox" <?= $promo?'checked':'' ?> onchange="location.href='search.php?<?= $q?"q=$q&":'' ?><?= $cat?"cat=$cat&":'' ?>promo=1'">
          <span>Promotions uniquement</span>
        </label>
        <label style="display:flex;align-items:center;gap:8px;font-size:13px;cursor:pointer">
          <input type="checkbox">
          <span>En stock uniquement</span>
        </label>
      </div>
    </aside>

    <!-- Results -->
    <div>
      <!-- Header -->
      <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px">
        <div>
          <?php if($q): ?>
            <!-- ⚠️ XSS réfléchi — $q affiché sans htmlspecialchars -->
            <h1 style="font-size:22px;font-weight:600;color:var(--gray-900)">Résultats pour "<?= $q ?>"</h1>
          <?php else: ?>
            <h1 style="font-size:22px;font-weight:600;color:var(--gray-900)">Tous les produits</h1>
          <?php endif; ?>
          <p style="font-size:13px;color:var(--gray-500);margin-top:2px"><?= $count ?> produit(s) trouvé(s)</p>
        </div>
        <div style="display:flex;align-items:center;gap:10px">
          <label style="font-size:13px;color:var(--gray-500)">Trier par :</label>
          <select class="form-select" style="width:auto;padding:8px 12px" onchange="location.href='search.php?<?= $q?"q=$q&":'' ?><?= $cat?"cat=$cat&":'' ?>sort='+this.value">
            <option value="relevance" <?= $sort=='relevance'?'selected':'' ?>>Pertinence</option>
            <option value="price_asc" <?= $sort=='price_asc'?'selected':'' ?>>Prix croissant</option>
            <option value="price_desc"<?= $sort=='price_desc'?'selected':'' ?>>Prix décroissant</option>
            <option value="rating"    <?= $sort=='rating'?'selected':'' ?>>Meilleures notes</option>
            <option value="new"       <?= $sort=='new'?'selected':'' ?>>Nouveautés</option>
          </select>
        </div>
      </div>

      <?php if($count > 0): ?>
      <div class="products-grid">
        <?php while($p = mysqli_fetch_assoc($results)): ?>
          <?php
          $icon = $product_icons[$p['id']-1] ?? '🛍';
          $discount = $p['original_price'] ? round((1-$p['price']/$p['original_price'])*100) : 0;
          $stars = str_repeat('★', round($p['rating'])) . str_repeat('☆', 5-round($p['rating']));
          ?>
          <div class="product-card">
            <div class="product-image-wrap">
              <div class="product-emoji"><?= $icon ?></div>
              <?php if($discount>0): ?>
                <span class="product-badge badge-sale">-<?= $discount ?>%</span>
              <?php endif; ?>
              <div class="product-actions">
                <a href="product.php?id=<?= $p['id'] ?>" class="product-action-btn">👁</a>
              </div>
            </div>
            <div class="product-info">
              <div class="product-category"><?= $p['cat_name'] ?></div>
              <a href="product.php?id=<?= $p['id'] ?>" style="text-decoration:none">
                <div class="product-name"><?= $p['name'] ?></div>
              </a>
              <div class="product-rating">
                <span class="stars"><?= $stars ?></span>
                <span class="rating-count">(<?= $p['review_count'] ?>)</span>
              </div>
              <div class="product-price-row">
                <div>
                  <span class="product-price"><?= formatPrice($p['price']) ?></span>
                  <?php if($p['original_price']): ?>
                    <span class="product-original-price"><?= formatPrice($p['original_price']) ?></span>
                  <?php endif; ?>
                </div>
                <form method="POST" action="cart.php">
                  <input type="hidden" name="product_id" value="<?= $p['id'] ?>">
                  <input type="hidden" name="action" value="add">
                  <button type="submit" class="add-to-cart-btn" onclick="addToCartFeedback(this)">+</button>
                </form>
              </div>
            </div>
          </div>
        <?php endwhile; ?>
      </div>
      <?php else: ?>
      <div class="empty-state">
        <div class="empty-icon">🔍</div>
        <div class="empty-title">Aucun résultat</div>
        <div class="empty-text">
          <?php if($q): ?>
            <!-- ⚠️ XSS -->
            Aucun produit ne correspond à "<?= $q ?>"
          <?php else: ?>
            Aucun produit disponible pour ces critères.
          <?php endif; ?>
        </div>
        <a href="search.php" class="btn btn-primary">Voir tous les produits</a>
      </div>
      <?php endif; ?>
    </div>
  </div>
</div>

<?php require_once '../includes/footer.php'; ?>