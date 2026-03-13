<?php
// ✅ Pas de credentials hardcodés — variables d'environnement
define('DB_HOST', getenv('DB_HOST') ?: 'mysql');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') ?: 'root');
define('DB_NAME', getenv('DB_NAME') ?: 'vulnshop');
define('DEBUG', false); // ✅ DEBUG désactivé en production

// ✅ Security Headers sur toutes les pages
function set_security_headers() {
    header("X-Frame-Options: DENY");
    header("X-Content-Type-Options: nosniff");
    header("X-XSS-Protection: 1; mode=block");
    header("Referrer-Policy: strict-origin-when-cross-origin");
    header("Content-Security-Policy: default-src 'self'; script-src 'self'; style-src 'self' 'unsafe-inline'");
    header("Strict-Transport-Security: max-age=31536000; includeSubDomains");
    header("Permissions-Policy: geolocation=(), microphone=(), camera=()");
}

// ✅ Connexion sécurisée avec mysqli
function getDB() {
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if (!$conn) {
        // ✅ Pas de détails d'erreur exposés
        error_log("DB Connection failed: " . mysqli_connect_error());
        die("Service temporarily unavailable.");
    }
    // ✅ Charset UTF-8 pour éviter les injections via encoding
    mysqli_set_charset($conn, 'utf8mb4');
    return $conn;
}

// ✅ Génération token CSRF
function generate_csrf_token() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// ✅ Validation token CSRF
function validate_csrf_token() {
    if (!isset($_POST['csrf_token']) ||
        !isset($_SESSION['csrf_token']) ||
        !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        http_response_code(403);
        die("CSRF token validation failed.");
    }
    // Régénère le token après validation
    unset($_SESSION['csrf_token']);
}

// ✅ Sanitisation des entrées
function sanitize($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

// ✅ Validation des entiers
function validate_int($input) {
    return filter_var($input, FILTER_VALIDATE_INT);
}
?>