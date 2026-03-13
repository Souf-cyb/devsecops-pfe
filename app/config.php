<?php
// Vulnérabilité : Credentials hardcodés
define('DB_HOST', getenv('DB_HOST') ?: 'mysql');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') ?: 'root');
define('DB_NAME', getenv('DB_NAME') ?: 'vulnshop');
define('DEBUG', true);

function getDB() {
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if (!$conn) {
        die("DB Error: " . mysqli_connect_error());
    }
    return $conn;
}
?>