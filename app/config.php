<?php
define('DB_HOST', getenv('DB_HOST') ?: 'mysql');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') ?: 'root');
define('DB_NAME', getenv('DB_NAME') ?: 'vulnshop');
define('DEBUG', true);

// ✅ Plus de secrets hardcodés — variables d'environnement
define('STRIPE_KEY',     getenv('STRIPE_KEY')     ?: '');
define('AWS_ACCESS_KEY', getenv('AWS_ACCESS_KEY') ?: '');
define('AWS_SECRET_KEY', getenv('AWS_SECRET_KEY') ?: '');

function getDB() {
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if (!$conn) {
        die("Connection failed: " . mysqli_connect_error());
    }
    return $conn;
}
?>