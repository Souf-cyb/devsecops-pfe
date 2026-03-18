<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/config.php';
$cartCount = getCartCount();
$flash     = getFlash();
$base      = (strpos($_SERVER['PHP_SELF'], '/admin/') !== false) ? '../' : '';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= $pageTitle ?? 'VulnShop' ?> — <?= APP_NAME ?></title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&family=Playfair+Display:ital,wght@0,600;1,400&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= $base ?>assets/css/main.css">
</head>
<body>

<!-- Top bar -->
<div class="topbar">
  <div class="topbar-inner">
    <span>🚚 Livraison gratuite dès 99€ d'achat</span>
    <div class="topbar-right">
      <a href="#">Service client</a>
      <a href="#">Suivi commande</a>
      <?php if(isset($_SESSION['user_id'])): ?>
        <a href="<?= $base ?>pages/account.php">Mon compte</a>
        <a href="<?= $base ?>pages/logout.php">Déconnexion</a>
      <?php else: ?>
        <a href="<?= $base ?>pages/login.php">Connexion</a>
        <a href="<?= $base ?>pages/register.php">Créer un compte</a>
      <?php endif; ?>
    </div>
  </div>
</div>

<!-- Main navbar -->
<nav class="navbar">
  <div class="navbar-inner">
    <a class="logo" href="<?= $base ?>index.php">
      <span class="logo-icon">◆</span>
      Vuln<em>Shop</em>
    </a>

    <div class="search-wrapper">
      <form class="search-form" action="<?= $base ?>pages/search.php" method="GET">
        <input type="text" name="q"
               class="search-input"
               placeholder="Rechercher un produit, une marque..."
               value="<?= htmlspecialchars($_GET['q'] ?? '') ?>">
        <button type="submit" class="search-btn">
          <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/>
          </svg>
        </button>
      </form>
    </div>

    <div class="navbar-actions">
      <?php if(isset($_SESSION['user_id'])): ?>
        <a href="<?= $base ?>pages/account.php" class="nav-icon-btn" title="Mon compte">
          <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>
          </svg>
        </a>
      <?php else: ?>
        <a href="<?= $base ?>pages/login.php" class="nav-icon-btn" title="Connexion">
          <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>
          </svg>
        </a>
      <?php endif; ?>

      <a href="<?= $base ?>pages/wishlist.php" class="nav-icon-btn" title="Favoris">
        <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/>
        </svg>
      </a>

      <a href="<?= $base ?>pages/cart.php" class="nav-icon-btn cart-btn" title="Panier">
        <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/>
          <line x1="3" y1="6" x2="21" y2="6"/>
          <path d="M16 10a4 4 0 0 1-8 0"/>
        </svg>
        <?php if($cartCount > 0): ?>
          <span class="cart-count"><?= $cartCount ?></span>
        <?php endif; ?>
      </a>
    </div>
  </div>

  <!-- Categories nav -->
  <div class="categories-nav">
    <div class="categories-inner">
      <a href="<?= $base ?>pages/search.php?cat=1">💻 Électronique</a>
      <a href="<?= $base ?>pages/search.php?cat=2">👗 Mode</a>
      <a href="<?= $base ?>pages/search.php?cat=3">🏠 Maison</a>
      <a href="<?= $base ?>pages/search.php?cat=4">🏃 Sport</a>
      <a href="<?= $base ?>pages/search.php?cat=5">💄 Beauté</a>
      <a href="<?= $base ?>pages/search.php?cat=6">📚 Livres</a>
      <a href="<?= $base ?>pages/search.php?promo=1" class="promo-link">🔥 Promotions</a>
    </div>
  </div>
</nav>

<?php if($flash): ?>
<div class="flash flash-<?= $flash['type'] ?>">
  <?= $flash['msg'] ?>
  <button onclick="this.parentElement.remove()" class="flash-close">×</button>
</div>
<?php endif; ?>