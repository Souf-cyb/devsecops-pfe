<?php
// cart.php
session_start();
require_once '../includes/config.php';

if (!isset($_SESSION['user_id'])) {
    redirect('login.php?redirect=cart.php');
}

$conn    = getDB();
$user_id = $_SESSION['user_id'];

// Handle actions — ⚠️ Pas de CSRF token
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action     = $_POST['action'] ?? '';
    $product_id = (int)$_POST['product_id'];
    $qty        = (int)($_POST['qty'] ?? 1);

    if ($action === 'add') {
        $existing = mysqli_fetch_assoc(mysqli_query($conn, "SELECT id, quantity FROM cart WHERE user_id=$user_id AND product_id=$product_id"));
        if ($existing) {
            mysqli_query($conn, "UPDATE cart SET quantity=quantity+$qty WHERE id={$existing['id']}");
        } else {
            mysqli_query($conn, "INSERT INTO cart (user_id, product_id, quantity) VALUES ($user_id, $product_id, $qty)");
        }
        flash('Produit ajouté au panier !');
        $ref = $_SERVER['HTTP_REFERER'] ?? '../index.php';
        redirect($ref);
    }

    if ($action === 'remove') {
        mysqli_query($conn, "DELETE FROM cart WHERE id=$product_id AND user_id=$user_id");
        redirect('cart.php');
    }

    if ($action === 'update') {
        mysqli_query($conn, "UPDATE cart SET quantity=$qty WHERE id=$product_id AND user_id=$user_id");
        redirect('cart.php');
    }

    if ($action === 'checkout') {
        $coupon_code = $_POST['coupon'] ?? '';
        $discount = 0;

        // ⚠️ SQLi via coupon code
        $coupon_res = mysqli_query($conn, "SELECT * FROM coupons WHERE code='$coupon_code' AND is_active=1");
        if ($coupon_res && mysqli_num_rows($coupon_res) > 0) {
            $coupon  = mysqli_fetch_assoc($coupon_res);
            $discount = $coupon['discount_percent'];
        }

        // Create order
        $cart_items = mysqli_query($conn, "SELECT c.*, p.price, p.name FROM cart c JOIN products p ON c.product_id=p.id WHERE c.user_id=$user_id");
        $subtotal   = 0;
        $items      = [];
        while ($item = mysqli_fetch_assoc($cart_items)) {
            $subtotal += $item['price'] * $item['quantity'];
            $items[]   = $item;
        }
        $total  = $subtotal * (1 - $discount/100);
        $ship   = $subtotal >= 99 ? 0 : 5.99;
        $total += $ship;
        $order_num = 'VS-' . date('Y') . '-' . str_pad(rand(1,9999), 4, '0', STR_PAD_LEFT);
        $address = $conn->real_escape_string($_POST['address'] ?? '');
        $payment = $_POST['payment'] ?? 'credit_card';
        $notes   = $_POST['notes'] ?? ''; // ⚠️ Stored XSS via notes

        mysqli_query($conn, "INSERT INTO orders (user_id, order_number, subtotal, shipping, total, shipping_address, payment_method, notes) VALUES ($user_id, '$order_num', $subtotal, $ship, $total, '$address', '$payment', '$notes')");
        $order_id = mysqli_insert_id($conn);

        foreach ($items as $item) {
            mysqli_query($conn, "INSERT INTO order_items (order_id, product_id, quantity, price) VALUES ($order_id, {$item['product_id']}, {$item['quantity']}, {$item['price']})");
        }

        mysqli_query($conn, "DELETE FROM cart WHERE user_id=$user_id");
        flash("Commande $order_num passée avec succès !");
        redirect('account.php?tab=orders');
    }
}

$pageTitle = 'Mon Panier';
require_once '../includes/header.php';

$cart_items = mysqli_query($conn, "SELECT c.*, p.name, p.price, p.original_price, p.stock, cat.name as cat_name FROM cart c JOIN products p ON c.product_id=p.id JOIN categories cat ON p.category_id=cat.id WHERE c.user_id=$user_id");
$icons = ['💻','📱','📱','🎧','📱','👟','👖','🧥','🛋','🍳','🚲','💄'];

$subtotal = 0;
$cart_arr = [];
while ($item = mysqli_fetch_assoc($cart_items)) {
    $subtotal += $item['price'] * $item['quantity'];
    $cart_arr[] = $item;
}
$shipping = $subtotal >= 99 ? 0 : 5.99;
$total    = $subtotal + $shipping;
?>

<div class="container" style="padding-top:28px;padding-bottom:60px">
  <div class="breadcrumb">
    <a href="../index.php">Accueil</a>
    <span class="breadcrumb-sep">›</span>
    <span>Mon panier</span>
  </div>

  <?php if(empty($cart_arr)): ?>
    <div class="empty-state">
      <div class="empty-icon">🛒</div>
      <div class="empty-title">Votre panier est vide</div>
      <div class="empty-text">Ajoutez des produits pour commencer vos achats.</div>
      <a href="search.php" class="btn btn-primary">Continuer les achats</a>
    </div>
  <?php else: ?>

  <div style="display:grid;grid-template-columns:1fr 360px;gap:24px">
    <!-- Cart items -->
    <div>
      <div class="card" style="margin-bottom:16px">
        <div style="padding:16px 20px;border-bottom:1px solid var(--border);display:flex;justify-content:space-between;align-items:center">
          <h2 style="font-size:16px;font-weight:600">Panier (<?= count($cart_arr) ?> article<?= count($cart_arr)>1?'s':'' ?>)</h2>
          <a href="#" style="font-size:13px;color:var(--gray-400)">Vider le panier</a>
        </div>
        <?php foreach($cart_arr as $item): ?>
        <div style="padding:16px 20px;border-bottom:1px solid var(--border);display:flex;gap:14px;align-items:center">
          <div style="width:70px;height:70px;background:var(--gray-100);border-radius:8px;display:flex;align-items:center;justify-content:center;font-size:30px;flex-shrink:0">
            <?= $icons[$item['product_id']-1] ?? '🛍' ?>
          </div>
          <div style="flex:1">
            <a href="product.php?id=<?= $item['product_id'] ?>" style="font-size:14px;font-weight:500;color:var(--gray-900)"><?= $item['name'] ?></a>
            <div style="font-size:12px;color:var(--gray-400);margin-top:2px"><?= $item['cat_name'] ?></div>
            <div style="font-size:14px;font-weight:600;color:var(--gray-900);margin-top:6px"><?= formatPrice($item['price']) ?></div>
          </div>
          <div style="display:flex;align-items:center;gap:8px">
            <form method="POST" style="display:flex;align-items:center;gap:6px">
              <input type="hidden" name="action" value="update">
              <input type="hidden" name="product_id" value="<?= $item['id'] ?>">
              <div class="qty-wrap" style="display:flex;align-items:center;border:1px solid var(--border);border-radius:6px;overflow:hidden">
                <button type="button" onclick="changeQty(this,-1)" style="padding:6px 10px;background:none;border:none;cursor:pointer;font-size:16px">−</button>
                <input type="number" name="qty" value="<?= $item['quantity'] ?>" min="1" max="99" class="qty-input" style="width:40px;text-align:center;border:none;outline:none;padding:6px 0;font-size:14px">
                <button type="button" onclick="changeQty(this,1)" style="padding:6px 10px;background:none;border:none;cursor:pointer;font-size:16px">+</button>
              </div>
              <button type="submit" class="btn btn-ghost btn-sm">Mise à jour</button>
            </form>
            <form method="POST">
              <input type="hidden" name="action" value="remove">
              <input type="hidden" name="product_id" value="<?= $item['id'] ?>">
              <button type="submit" style="background:none;border:none;color:var(--gray-400);cursor:pointer;font-size:18px">×</button>
            </form>
          </div>
          <div style="font-size:15px;font-weight:600;width:80px;text-align:right">
            <?= formatPrice($item['price'] * $item['quantity']) ?>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>

    <!-- Order summary -->
    <div>
      <div class="card p-20" style="margin-bottom:16px">
        <h3 style="font-size:15px;font-weight:600;margin-bottom:16px">Résumé de la commande</h3>
        <div style="display:flex;justify-content:space-between;font-size:13px;margin-bottom:8px">
          <span style="color:var(--gray-500)">Sous-total</span>
          <span><?= formatPrice($subtotal) ?></span>
        </div>
        <div style="display:flex;justify-content:space-between;font-size:13px;margin-bottom:14px">
          <span style="color:var(--gray-500)">Livraison</span>
          <span><?= $shipping == 0 ? '<span style="color:var(--success)">Gratuite</span>' : formatPrice($shipping) ?></span>
        </div>
        <div style="border-top:2px solid var(--border);padding-top:12px;display:flex;justify-content:space-between;font-size:16px;font-weight:700;margin-bottom:20px">
          <span>Total</span>
          <span><?= formatPrice($total) ?></span>
        </div>

        <form method="POST">
          <input type="hidden" name="action" value="checkout">
          <div class="form-group">
            <label class="form-label">Adresse de livraison</label>
            <textarea name="address" class="form-textarea" style="height:72px" placeholder="Votre adresse complète..."></textarea>
          </div>
          <div class="form-group">
            <label class="form-label">Code promo</label>
            <!-- ⚠️ SQLi via coupon -->
            <input type="text" name="coupon" class="form-input" placeholder="Ex: PROMO20">
            <div class="form-help">Essayez : WELCOME10, PROMO20, VIP30</div>
          </div>
          <div class="form-group">
            <label class="form-label">Mode de paiement</label>
            <select name="payment" class="form-select">
              <option value="credit_card">💳 Carte bancaire</option>
              <option value="paypal">🅿 PayPal</option>
              <option value="virement">🏦 Virement</option>
            </select>
          </div>
          <div class="form-group">
            <label class="form-label">Notes de commande</label>
            <!-- ⚠️ Stored XSS via notes -->
            <textarea name="notes" class="form-textarea" style="height:56px" placeholder="Instructions spéciales..."></textarea>
          </div>
          <button type="submit" class="btn btn-accent btn-full btn-lg">
            🔒 Confirmer la commande
          </button>
        </form>
      </div>
    </div>
  </div>
  <?php endif; ?>
</div>

<?php require_once '../includes/footer.php'; ?>