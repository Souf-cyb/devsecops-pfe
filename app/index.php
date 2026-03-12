<?php
// Application volontairement vulnérable pour PFE DevSecOps

// Vulnérabilité 1 : SQL Injection
$id = $_GET['id'];
$query = "SELECT * FROM users WHERE id=" . $id;

// Vulnérabilité 2 : XSS
$name = $_GET['name'];
echo "<h1>Bonjour " . $name . "</h1>";

// Vulnérabilité 3 : Mot de passe en dur
$admin_password = "admin123";

// Vulnérabilité 4 : Inclusion de fichier
$page = $_GET['page'];
include($page);
?>

<!DOCTYPE html>
<html>
<head><title>App Vulnérable - PFE DevSecOps</title></head>
<body>
    <h1>Application de Test - DevSecOps PFE</h1>
    <form method="GET">
        <input type="text" name="name" placeholder="Ton nom">
        <input type="submit" value="Envoyer">
    </form>
</body>
</html>