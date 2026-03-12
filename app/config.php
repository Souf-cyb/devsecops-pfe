<?php
// Configuration avec vulnérabilités intentionnelles

// Vulnérabilité : Credentials en dur
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', 'admin123');
define('DB_NAME', 'vulnerable_app');

// Vulnérabilité : Clé secrète exposée
define('SECRET_KEY', 'mysecretkey123');
define('API_KEY', 'sk-1234567890abcdef');

// Vulnérabilité : Mode debug activé
define('DEBUG', true);
define('DISPLAY_ERRORS', true);

if (DISPLAY_ERRORS) {
    ini_set('display_errors', 1);
    error_reporting(E_ALL);
}

// Vulnérabilité : Connexion MySQL non sécurisée
function getDB() {
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    return $conn;
}
?>