<?php
$pageTitle = 'Produit';
require_once '../includes/header.php';
$conn = getDB();

// ⚠️ SQLi via ?id= — pas de préparation de requête
$id = $_GET['id'] ?? '1';
$product = mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT p.*, c.name as cat_name, c.id as cat_id
     FROM products p
     JOIN categories c ON p.category_id=c.id
     WHERE p.id=" . $id  // SQLi ici
));

if (!$product) {
    flash('Produit introuvable.', 'error');
    redirect('../index.php');
}
$pageTitle = htmlspecialchars($product['name']);

// Handle review submission — ⚠️ Stored XSS + CSRF
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['comment'])) {
    if (!isset($_SESSION['user_id'])) {
        flash('Connectez-vous pour laisser un avis.', 'error');
        redirect("product.php?id=$id");
    }
    $rating   = (int)$_POST['rating'];
    $title    = $_POST['title'];    // ⚠️ pas de sanitisation
    $comment  = $_POST['comment'];  // ⚠️ Stored XSS
    $user_id  = $_SESSION['user_id'];

    // ⚠️ SQLi dans INSERT
    mysqli_query($conn,
        "INSERT INTO reviews (product_id, user_id, rating, title, comment, is_verified)
         VALUES ($id, $user_id, $rating, '$title', '$comment', 0)"
    );
    flash('Votre avis a été publié !');
    redirect("product.php?id=$id");
}

// Load reviews — affichés sans échappement → Stored XSS
$reviews = mysqli_query($conn, "SELECT r.*, u.username, u.full_name FROM reviews r JOIN users u ON r.user_id=u.id WHERE r.product_id=$id ORDER BY r.created_at DESC");

// Related products
$related = mysqli_query($conn, "SELECT p.*, c.name as cat_name FROM products p JOIN categories c ON p.category_id=c.id WHERE p.category_id={$product['cat_id']} AND p.id!=$id LIMIT 4");

$icons = ['💻','📱','📱','🎧','📱','👟','👖','🧥','🛋','🍳','🚲','💄'];
$icon  = $icons[$product['id']-1] ?? '🛍';
$discount = $product['original_price'] ? round((1-$product['price']/$product['original_price'])*100) : 0;
$stars_full = str_repeat('★', round($product['rating'])) . str_repeat('☆', 5-round($product['rating']));
?>

<div class="container" style="padding-top:28px;padding-bottom:60px">

  <!-- Breadcrumb -->
  <div class="breadcrumb">
    <a href="../index.php">Accueil</a>
    <span class="breadcrumb-sep">›</span>
    <a href="search.php?cat=<?= $product['cat_id'] ?>"><?= $product['cat_name'] ?></a>
    <span class="breadcrumb-sep">›</span>
    <!-- ⚠️ XSS via product name si injecté via SQLi -->
    <span><?= $product['name'] ?></span>
  </div>

  <!-- Product layout -->
  <div style="display:grid;grid-template-columns:1fr 1fr;gap:48px;margin-bottom:60px">

    <!-- Image -->
    <div>
      <div style="background:var(--gray-100);border-radius:16px;aspect-ratio:1;display:flex;align-items:center;justify-content:center;font-size:120px;margin-bottom:12px;border:1px solid var(--border)">
        <?= $icon ?>
      </div>
      <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:8px">
        <?php for($t=0;$t<4;$t++): ?>
        <div style="background:var(--gray-100);border-radius:8px;aspect-ratio:1;display:flex;align-items:center;justify-content:center;font-size:28px;border:2px solid <?=$t==0?'var(--primary)':'var(--border)'?>;cursor:pointer;opacity:<?=$t==0?'1':'.6'?>">
          <?= $icon ?>
        </div>
        <?php endfor; ?>
      </div>
    </div>

    <!-- Info -->
    <div>
      <div style="font-size:12px;font-weight:600;letter-spacing:1.5px;text-transform:uppercase;color:var(--gray-400);margin-bottom:10px"><?= $product['cat_name'] ?></div>
      <h1 style="font-family:var(--font-display);font-size:30px;color:var(--gray-900);margin-bottom:14px;line-height:1.2">
        <?= $product['name'] /* ⚠️ XSS possible via SQLi */ ?>
      </h1>

      <div style="display:flex;align-items:center;gap:12px;margin-bottom:16px">
        <span style="color:var(--gold);font-size:16px;letter-spacing:2px"><?= $stars_full ?></span>
        <span style="font-size:13px;color:var(--gray-500)"><?= $product['review_count'] ?> avis</span>
        <span style="font-size:12px;padding:2px 8px;background:#dcfce7;color:#166534;border-radius:10px;font-weight:500">
          ✓ En stock (<?= $product['stock'] ?>)
        </span>
      </div>

      <div style="display:flex;align-items:baseline;gap:12px;margin-bottom:20px">
        <span style="font-size:34px;font-weight:600;color:var(--gray-900)"><?= formatPrice($product['price']) ?></span>
        <?php if($product['original_price']): ?>
          <span style="font-size:18px;color:var(--gray-400);text-decoration:line-through"><?= formatPrice($product['original_price']) ?></span>
          <span style="font-size:14px;font-weight:600;color:var(--accent)">-<?= $discount ?>%</span>
        <?php endif; ?>
      </div>

      <p style="font-size:14px;color:var(--gray-600);line-height:1.8;margin-bottom:24px;padding:16px;background:var(--gray-50);border-radius:8px;border:1px solid var(--border)">
        <?= $product['description'] ?>
      </p>

      <!-- SKU -->
      <div style="font-size:12px;color:var(--gray-400);margin-bottom:24px">
        Référence : <span style="font-family:monospace"><?= $product['sku'] ?></span>
      </div>

      <!-- Actions -->
      <div style="display:flex;gap:12px;margin-bottom:20px">
        <form method="POST" action="cart.php" style="flex:1">
          <!-- ⚠️ Pas de token CSRF -->
          <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
          <input type="hidden" name="action" value="add">
          <button type="submit" class="btn btn-accent btn-full btn-lg">
            🛒 Ajouter au panier
          </button>
        </form>
        <form method="POST" action="wishlist.php">
          <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
          <input type="hidden" name="action" value="add">
          <button type="submit" class="btn btn-outline btn-lg btn-icon" style="padding:13px 16px">♡</button>
        </form>
      </div>

      <!-- Trust -->
      <div style="display:flex;flex-direction:column;gap:8px;padding:14px;background:var(--gray-50);border-radius:8px;border:1px solid var(--border)">
        <div style="display:flex;align-items:center;gap:8px;font-size:13px;color:var(--gray-600)">
          <span>🚚</span> Livraison gratuite — expédition sous 24h
        </div>
        <div style="display:flex;align-items:center;gap:8px;font-size:13px;color:var(--gray-600)">
          <span>↩️</span> Retour gratuit sous 30 jours
        </div>
        <div style="display:flex;align-items:center;gap:8px;font-size:13px;color:var(--gray-600)">
          <span>🔒</span> Paiement 100% sécurisé
        </div>
      </div>
    </div>
  </div>

  <!-- Reviews section -->
  <div style="margin-bottom:60px">
    <h2 style="font-family:var(--font-display);font-size:26px;margin-bottom:24px">
      Avis clients <span style="color:var(--gray-400);font-size:18px;font-weight:400">(<?= $product['review_count'] ?>)</span>
    </h2>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:28px">

      <!-- Reviews list -->
      <div style="display:flex;flex-direction:column;gap:14px">
        <?php if(mysqli_num_rows($reviews) > 0): ?>
          <?php while($r = mysqli_fetch_assoc($reviews)): ?>
          <div style="background:white;border:1px solid var(--border);border-radius:12px;padding:20px">
            <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:8px">
              <div>
                <span style="font-weight:500;font-size:14px"><?= $r['full_name'] ?: $r['username'] ?></span>
                <?php if($r['is_verified']): ?>
                  <span style="font-size:10px;padding:1px 6px;background:#dcfce7;color:#166534;border-radius:4px;margin-left:6px">✓ Achat vérifié</span>
                <?php endif; ?>
              </div>
              <span style="color:var(--gold);font-size:13px"><?= str_repeat('★',$r['rating']) ?></span>
            </div>
            <?php if($r['title']): ?>
              <!-- ⚠️ Stored XSS — title affiché sans échappement -->
              <div style="font-weight:500;margin-bottom:5px"><?= $r['title'] ?></div>
            <?php endif; ?>
            <!-- ⚠️ Stored XSS — comment affiché sans htmlspecialchars -->
            <div style="font-size:14px;color:var(--gray-600);line-height:1.6"><?= $r['comment'] ?></div>
            <div style="font-size:11px;color:var(--gray-400);margin-top:8px"><?= date('d/m/Y', strtotime($r['created_at'])) ?></div>
          </div>
          <?php endwhile; ?>
        <?php else: ?>
          <div style="background:var(--gray-50);border:1px solid var(--border);border-radius:12px;padding:32px;text-align:center;color:var(--gray-400)">
            <div style="font-size:32px;margin-bottom:8px">💬</div>
            Aucun avis pour ce produit. Soyez le premier !
          </div>
        <?php endif; ?>
      </div>

      <!-- Review form -->
      <div>
        <?php if(isset($_SESSION['user_id'])): ?>
        <div style="background:white;border:1px solid var(--border);border-radius:12px;padding:24px">
          <h3 style="font-size:16px;font-weight:600;margin-bottom:20px">Laisser un avis</h3>
          <form method="POST"> <!-- ⚠️ Pas de CSRF token -->
            <div class="form-group">
              <label class="form-label">Note *</label>
              <div class="star-rating-input" style="display:flex;gap:6px;font-size:28px">
                <?php for($s=1;$s<=5;$s++): ?>
                <span class="star <?= $s<=3?'filled':'' ?>" data-val="<?= $s ?>">★</span>
                <?php endfor; ?>
                <input type="hidden" name="rating" value="3">
              </div>
            </div>
            <div class="form-group">
              <label class="form-label">Titre de l'avis</label>
              <!-- ⚠️ Stored XSS via title -->
              <input type="text" name="title" class="form-input" placeholder="Ex: Excellent produit !">
            </div>
            <div class="form-group">
              <label class="form-label">Votre commentaire *</label>
              <!-- ⚠️ Stored XSS via comment -->
              <textarea name="comment" class="form-textarea" placeholder="Décrivez votre expérience avec ce produit..." required></textarea>
            </div>
            <button type="submit" class="btn btn-primary btn-full">Publier l'avis</button>
          </form>
        </div>
        <?php else: ?>
        <div style="background:var(--gray-50);border:1px solid var(--border);border-radius:12px;padding:24px;text-align:center">
          <div style="font-size:32px;margin-bottom:12px">🔒</div>
          <p style="color:var(--gray-600);margin-bottom:16px">Connectez-vous pour laisser un avis</p>
          <a href="login.php?redirect=product.php%3Fid%3D<?= $id ?>" class="btn btn-primary">Se connecter</a>
        </div>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <!-- Related products -->
  <?php if(mysqli_num_rows($related) > 0): ?>
  <div>
    <h2 style="font-family:var(--font-display);font-size:26px;margin-bottom:24px">Produits similaires</h2>
    <div class="products-grid">
      <?php while($p = mysqli_fetch_assoc($related)): ?>
        <?php $pi = $icons[$p['id']-1] ?? '🛍'; ?>
        <div class="product-card">
          <div class="product-image-wrap">
            <div class="product-emoji"><?= $pi ?></div>
          </div>
          <div class="product-info">
            <div class="product-category"><?= $p['cat_name'] ?></div>
            <a href="product.php?id=<?= $p['id'] ?>" style="text-decoration:none">
              <div class="product-name"><?= $p['name'] ?></div>
            </a>
            <div class="product-price-row">
              <span class="product-price"><?= formatPrice($p['price']) ?></span>
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
  </div>
  <?php endif; ?>

</div>

<?php require_once '../includes/footer.php'; ?>