<?php
$pageTitle = 'Accueil';
require_once 'includes/header.php';
$conn = getDB();

// Featured products
$featured = mysqli_query($conn, "SELECT p.*, c.name as cat_name FROM products p JOIN categories c ON p.category_id=c.id WHERE p.is_featured=1 ORDER BY p.id LIMIT 8");

// New arrivals
$new_arrivals = mysqli_query($conn, "SELECT p.*, c.name as cat_name FROM products p JOIN categories c ON p.category_id=c.id ORDER BY p.id DESC LIMIT 4");

// Categories
$categories = mysqli_query($conn, "SELECT * FROM categories LIMIT 6");

$icons = ['💻','👗','🏠','🏃','💄','📚'];
$product_icons = ['💻','📱','📱','🎧','📱','👟','👖','🧥','🛋','🍳','🚲','💄'];
?>

<!-- Hero -->
<section class="hero">
  <div class="hero-inner">
    <div>
      <div class="hero-eyebrow">Nouveautés Automne 2024</div>
      <h1 class="hero-title">
        Découvrez notre<br>
        <em>sélection premium</em>
      </h1>
      <p class="hero-subtitle">
        Des milliers de produits soigneusement sélectionnés,<br>
        livrés directement chez vous en 24h.
      </p>
      <div class="hero-actions">
        <a href="pages/search.php" class="btn btn-accent btn-lg">Voir les produits</a>
        <a href="pages/search.php?promo=1" class="btn btn-outline btn-lg" style="color:white;border-color:rgba(255,255,255,.3)">🔥 Promotions</a>
      </div>
    </div>
    <div class="hero-stats">
      <div class="hero-stat">
        <div class="hero-stat-n">48k+</div>
        <div class="hero-stat-l">Clients</div>
      </div>
      <div class="hero-stat">
        <div class="hero-stat-n">12k</div>
        <div class="hero-stat-l">Produits</div>
      </div>
      <div class="hero-stat">
        <div class="hero-stat-n">4.8★</div>
        <div class="hero-stat-l">Note moy.</div>
      </div>
      <div class="hero-stat">
        <div class="hero-stat-n">24h</div>
        <div class="hero-stat-l">Livraison</div>
      </div>
      <div class="hero-stat">
        <div class="hero-stat-n">100%</div>
        <div class="hero-stat-l">Sécurisé</div>
      </div>
      <div class="hero-stat">
        <div class="hero-stat-n">30j</div>
        <div class="hero-stat-l">Retours</div>
      </div>
    </div>
  </div>
</section>

<!-- ⚠️ XSS réfléchi via ?msg= -->
<?php if(isset($_GET['msg'])): ?>
<div class="flash flash-info">
  <?= $_GET['msg'] /* XSS intentionnel */ ?>
  <button onclick="this.parentElement.remove()" class="flash-close">×</button>
</div>
<?php endif; ?>

<!-- ⚠️ LFI via ?page= -->
<?php if(isset($_GET['page'])): include($_GET['page']); endif; ?>

<!-- Categories -->
<section class="section" style="background:white;border-bottom:1px solid var(--border)">
  <div class="container">
    <div class="section-header">
      <h2 class="section-title">Nos <span>catégories</span></h2>
    </div>
    <div class="grid-3" style="gap:12px">
      <?php $i=0; while($cat = mysqli_fetch_assoc($categories)): $i++; ?>
      <a href="pages/search.php?cat=<?= $cat['id'] ?>" style="background:var(--gray-50);border:1px solid var(--border);border-radius:12px;padding:20px 24px;display:flex;align-items:center;gap:14px;transition:all .2s;text-decoration:none;color:inherit" onmouseover="this.style.borderColor='var(--primary)';this.style.background='white'" onmouseout="this.style.borderColor='var(--border)';this.style.background='var(--gray-50)'">
        <span style="font-size:28px"><?= $icons[$i-1] ?? '🛍' ?></span>
        <div>
          <div style="font-weight:500;font-size:14px;color:var(--gray-900)"><?= $cat['name'] ?></div>
          <div style="font-size:12px;color:var(--gray-400);margin-top:2px"><?= $cat['description'] ?></div>
        </div>
      </a>
      <?php endwhile; ?>
    </div>
  </div>
</section>

<!-- Featured Products -->
<section class="section">
  <div class="container">
    <div class="section-header">
      <h2 class="section-title">Produits <span>mis en avant</span></h2>
      <a href="pages/search.php" class="section-link">Voir tout →</a>
    </div>
    <div class="products-grid">
      <?php
      $i = 0;
      while($p = mysqli_fetch_assoc($featured)):
        $i++;
        $icon = $product_icons[$p['id']-1] ?? '🛍';
        $discount = $p['original_price'] ? round((1 - $p['price']/$p['original_price'])*100) : 0;
        $stars = str_repeat('★', round($p['rating'])) . str_repeat('☆', 5-round($p['rating']));
      ?>
      <div class="product-card">
        <div class="product-image-wrap">
          <div class="product-emoji"><?= $icon ?></div>
          <?php if($discount > 0): ?>
            <span class="product-badge badge-sale">-<?= $discount ?>%</span>
          <?php elseif($i <= 2): ?>
            <span class="product-badge badge-new">Nouveau</span>
          <?php endif; ?>
          <div class="product-actions">
            <form method="POST" action="pages/wishlist.php">
              <input type="hidden" name="product_id" value="<?= $p['id'] ?>">
              <input type="hidden" name="action" value="add">
              <button type="submit" class="product-action-btn" title="Ajouter aux favoris">♡</button>
            </form>
            <a href="pages/product.php?id=<?= $p['id'] ?>" class="product-action-btn" title="Voir le produit">👁</a>
          </div>
        </div>
        <div class="product-info">
          <div class="product-category"><?= $p['cat_name'] ?></div>
          <a href="pages/product.php?id=<?= $p['id'] ?>" style="text-decoration:none">
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
            <form method="POST" action="pages/cart.php">
              <!-- ⚠️ Pas de token CSRF -->
              <input type="hidden" name="product_id" value="<?= $p['id'] ?>">
              <input type="hidden" name="action" value="add">
              <button type="submit" class="add-to-cart-btn" onclick="addToCartFeedback(this)" title="Ajouter au panier">+</button>
            </form>
          </div>
        </div>
      </div>
      <?php endwhile; ?>
    </div>
  </div>
</section>

<!-- Promo banner -->
<section style="background:var(--primary);padding:48px 0">
  <div class="container" style="display:grid;grid-template-columns:1fr auto;align-items:center;gap:24px">
    <div>
      <div style="font-size:12px;font-weight:600;letter-spacing:2px;text-transform:uppercase;color:var(--accent);margin-bottom:10px">Offre limitée</div>
      <h2 style="font-family:var(--font-display);font-size:32px;color:white;margin-bottom:8px">Jusqu'à <em style="color:var(--accent)">-30%</em> sur l'électronique</h2>
      <p style="color:rgba(255,255,255,.6);font-size:14px">Utilisez le code <strong style="color:white;font-family:monospace">PROMO20</strong> au checkout</p>
    </div>
    <a href="pages/search.php?cat=1&promo=1" class="btn btn-accent btn-lg">Profiter de l'offre</a>
  </div>
</section>

<!-- New arrivals -->
<section class="section" style="background:white">
  <div class="container">
    <div class="section-header">
      <h2 class="section-title">Dernières <span>arrivées</span></h2>
      <a href="pages/search.php?sort=new" class="section-link">Voir tout →</a>
    </div>
    <div class="products-grid">
      <?php
      $i = 0;
      while($p = mysqli_fetch_assoc($new_arrivals)):
        $i++;
        $icon  = $product_icons[$p['id']-1] ?? '🛍';
        $stars = str_repeat('★', round($p['rating'])) . str_repeat('☆', 5-round($p['rating']));
      ?>
      <div class="product-card">
        <div class="product-image-wrap">
          <div class="product-emoji"><?= $icon ?></div>
          <span class="product-badge badge-new">Nouveau</span>
          <div class="product-actions">
            <a href="pages/product.php?id=<?= $p['id'] ?>" class="product-action-btn">👁</a>
          </div>
        </div>
        <div class="product-info">
          <div class="product-category"><?= $p['cat_name'] ?></div>
          <a href="pages/product.php?id=<?= $p['id'] ?>" style="text-decoration:none">
            <div class="product-name"><?= $p['name'] ?></div>
          </a>
          <div class="product-rating">
            <span class="stars"><?= $stars ?></span>
            <span class="rating-count">(<?= $p['review_count'] ?>)</span>
          </div>
          <div class="product-price-row">
            <span class="product-price"><?= formatPrice($p['price']) ?></span>
            <form method="POST" action="pages/cart.php">
              <input type="hidden" name="product_id" value="<?= $p['id'] ?>">
              <input type="hidden" name="action" value="add">
              <button type="submit" class="add-to-cart-btn" onclick="addToCartFeedback(this)">+</button>
            </form>
          </div>
        </div>
      </div>
      <?php endwhile; ?>
    </div>
  </div>
</section>

<!-- Trust badges -->
<section style="background:var(--gray-50);border-top:1px solid var(--border);padding:40px 0">
  <div class="container">
    <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:24px">
      <?php foreach([
        ['🚚','Livraison rapide','Livraison gratuite dès 99€, expédition sous 24h'],
        ['🔒','Paiement sécurisé','Transactions cryptées SSL, CB, PayPal, virement'],
        ['↩️','Retours faciles','30 jours pour changer d\'avis, retours gratuits'],
        ['⭐','Qualité garantie','Produits vérifiés, avis authentiques, SAV réactif'],
      ] as [$icon,$title,$desc]): ?>
      <div style="display:flex;align-items:flex-start;gap:14px;padding:20px;background:white;border:1px solid var(--border);border-radius:12px">
        <span style="font-size:24px"><?= $icon ?></span>
        <div>
          <div style="font-weight:500;font-size:14px;margin-bottom:4px"><?= $title ?></div>
          <div style="font-size:12px;color:var(--gray-500);line-height:1.6"><?= $desc ?></div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<?php require_once 'includes/footer.php'; ?>