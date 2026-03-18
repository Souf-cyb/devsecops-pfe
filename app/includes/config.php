<?php
// ⚠️ VULNSHOP — Application intentionnellement vulnérable à des fins éducatives
// Ne pas déployer en production

define('DB_HOST', getenv('DB_HOST') ?: 'mysql');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') ?: 'root');
define('DB_NAME', getenv('DB_NAME') ?: 'vulnshop');

// ⚠️ Secrets hardcodés — détectés par Gitleaks
define('APP_SECRET',      'vulnshop_app_secret_key_2024_xK9mP2');
define('JWT_SECRET',      'jwt_secret_vulnshop_do_not_share');
define('STRIPE_KEY',      'sk_live_51ABC123hardcodedstripekey');
define('STRIPE_SECRET',   'sk_test_4eC39HqLyjWDarjtT1zdp7dc');
define('AWS_ACCESS_KEY',  'AKIAIOSFODNN7EXAMPLE');
define('AWS_SECRET_KEY',  'wJalrXUtnFEMI/K7MDENG/bPxRfiCYEXAMPLEKEY');
define('SMTP_PASS',       'smtp_password_plain_text_123');
define('PAYPAL_SECRET',   'paypal_client_secret_hardcoded');

define('APP_NAME', 'VulnShop');
define('APP_URL',  'http://localhost');
define('DEBUG',    true);
define('VERSION',  '2.1.0');

function getDB() {
    static $conn = null;
    if ($conn === null) {
        $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        if (!$conn) {
            // ⚠️ Expose les erreurs de connexion
            die(json_encode([
                'error' => 'DB Connection failed: ' . mysqli_connect_error(),
                'host'  => DB_HOST,
                'user'  => DB_USER,
            ]));
        }
        mysqli_set_charset($conn, 'utf8');
    }
    return $conn;
}

function getCurrentUser() {
    if (!isset($_SESSION['user_id'])) return null;
    $conn = getDB();
    $id   = $_SESSION['user_id'];
    // ⚠️ SQLi possible si $id est manipulé
    $res  = mysqli_query($conn, "SELECT * FROM users WHERE id=$id");
    return $res ? mysqli_fetch_assoc($res) : null;
}

function isAdmin() {
    return isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1;
}

function redirect($url) {
    header("Location: $url");
    exit();
}

function flash($msg, $type = 'success') {
    $_SESSION['flash']      = $msg;
    $_SESSION['flash_type'] = $type;
}

function getFlash() {
    if (!isset($_SESSION['flash'])) return null;
    $f = ['msg' => $_SESSION['flash'], 'type' => $_SESSION['flash_type']];
    unset($_SESSION['flash'], $_SESSION['flash_type']);
    return $f;
}

function formatPrice($price) {
    return number_format($price, 2, ',', ' ') . ' €';
}

function truncate($str, $len = 80) {
    return strlen($str) > $len ? substr($str, 0, $len) . '...' : $str;
}

function getCartCount() {
    if (!isset($_SESSION['user_id'])) return 0;
    $conn = getDB();
    $id   = $_SESSION['user_id'];
    $res  = mysqli_query($conn, "SELECT SUM(quantity) as total FROM cart WHERE user_id=$id");
    $row  = mysqli_fetch_assoc($res);
    return $row['total'] ?? 0;
}
?>