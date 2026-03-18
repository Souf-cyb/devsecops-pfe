<?php
session_start();
require_once '../includes/config.php';

if (!isset($_SESSION['user_id'])) redirect('login.php');

$conn    = getDB();
$user_id = $_SESSION['user_id'];

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action     = $_POST['action'] ?? '';
    $product_id = (int)$_POST['product_id'];
    if ($action === 'add') {
        $exists = mysqli_fetch_assoc(mysqli_query($conn, "SELECT id FROM wishlist WHERE user_id=$user_id AND product_id=$product_id"));
        if (!$exists) mysqli_query($conn, "INSERT INTO wishlist (user_id, product_id) VALUES ($user_id, $product_id)");
        flash('Produit ajouté aux favoris !');
        $ref = $_SERVER['HTTP_REFERER'] ?? '../index.php';
        redirect($ref);
    }
    if ($action === 'remove') {
        mysqli_query($conn, "DELETE FROM wishlist WHERE id=$product_id AND user_id=$user_id");
        redirect('wishlist.php');
    }
}

$pageTitle = 'Mes Favoris';
require_once '../includes/header.php';

$items = mysqli_query($conn, "SELECT w.*, p.name, p.price, p.original_price, cat.name as cat_name FROM wishlist w JOIN products p ON w.product_id=p.id JOIN categories cat ON p.category_id=cat.id WHERE w.user_id=$user_id ORDER BY w.created_at DESC");
$icons = ['💻','📱','📱','🎧','📱','👟','👖','🧥','🛋','🍳','🚲','💄'];
?>

<div class="container" style="padding-top:28px;padding-bottom:60px">
  <div class="breadcrumb">
    <a href="../index.php">Accueil</a>
    <span class="breadcrumb-sep">›</span>
    <span>Mes favoris</span>
  </div>

  <div class="section-header">
    <h1 style="font-family:var(--font-display);font-size:32px">Mes favoris</h1>
  </div>

  <?php if(mysqli_num_rows($items) === 0): ?>
    <div class="empty-state">
      <div class="empty-icon">♡</div>
      <div class="empty-title">Votre liste est vide</div>
      <div class="empty-text">Ajoutez des produits à vos favoris pour les retrouver facilement.</div>
      <a href="search.php" class="btn btn-primary">Découvrir nos produits</a>
    </div>
  <?php else: ?>
    <div class="products-grid">
      <?php while($item = mysqli_fetch_assoc($items)): ?>
        <?php $icon = $icons[$item['product_id']-1] ?? '🛍'; ?>
        <div class="product-card">
          <div class="product-image-wrap">
            <div class="product-emoji"><?= $icon ?></div>
            <div class="product-actions" style="opacity:1;transform:none">
              <form method="POST">
                <input type="hidden" name="action" value="remove">
                <input type="hidden" name="product_id" value="<?= $item['id'] ?>">
                <button type="submit" class="product-action-btn" title="Retirer des favoris">×</button>
              </form>
            </div>
          </div>
          <div class="product-info">
            <div class="product-category"><?= $item['cat_name'] ?></div>
            <a href="product.php?id=<?= $item['product_id'] ?>" style="text-decoration:none">
              <div class="product-name"><?= $item['name'] ?></div>
            </a>
            <div class="product-price-row">
              <span class="product-price"><?= formatPrice($item['price']) ?></span>
              <form method="POST" action="cart.php">
                <input type="hidden" name="product_id" value="<?= $item['product_id'] ?>">
                <input type="hidden" name="action" value="add">
                <button type="submit" class="add-to-cart-btn" onclick="addToCartFeedback(this)">+</button>
              </form>
            </div>
          </div>
        </div>
      <?php endwhile; ?>
    </div>
  <?php endif; ?>
</div>

<?php require_once '../includes/footer.php'; ?>