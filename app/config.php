<?php
define('DB_HOST', getenv('DB_HOST') ?: 'mysql');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') ?: 'root');
define('DB_NAME', getenv('DB_NAME') ?: 'vulnshop');
define('DEBUG', true);

// Hardcoded secrets — détectés par Gitleaks
define('STRIPE_KEY',     'sk_live_51hardcodedstripekey123456');
define('AWS_ACCESS_KEY', 'AKIAIOSFODNN7EXAMPLE');
define('AWS_SECRET_KEY', 'wJalrXUtnFEMI/K7MDENG/bPxRfiCYEXAMPLEKEY');
define('JWT_SECRET',     'vulnshop_jwt_secret_2024');

function getDB() {
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if (!$conn) die("Connection failed: " . mysqli_connect_error());
    return $conn;
}
?>