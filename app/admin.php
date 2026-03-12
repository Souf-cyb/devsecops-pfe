<?php
session_start();
require_once 'config.php';

// Vulnérabilité : Contrôle d'accès insuffisant
// Vérifie juste un paramètre GET au lieu d'une vraie session admin
if (!isset($_GET['admin']) || $_GET['admin'] !== 'true') {
    // Vulnérabilité : Bypass facile avec ?admin=true
    echo "Not authorized. Try adding ?admin=true to the URL";
}

$conn = getDB();

// Vulnérabilité : SQL Injection dans la recherche admin
if (isset($_POST['search_user'])) {
    $search = $_POST['search_user'];
    $query = "SELECT id, username, email, password, is_admin FROM users WHERE username LIKE '%" . $search . "%'";
    $result = mysqli_query($conn, $query);
}

// Vulnérabilité : Commande OS avec entrée utilisateur
if (isset($_POST['ping'])) {
    $host = $_POST['ping'];
    // Command Injection
    $output = shell_exec("ping -c 1 " . $host);
    echo "<pre>" . $output . "</pre>";
}
?>
<!DOCTYPE html>
<html>
<head><title>Admin Panel - VulnShop</title></head>
<body>
<h2>Admin Panel</h2>

<!-- Recherche utilisateurs -->
<form method="POST">
    <input type="text" name="search_user" placeholder="Search users">
    <input type="submit" value="Search">
</form>

<?php
if (isset($result)) {
    while ($row = mysqli_fetch_assoc($result)) {
        // Vulnérabilité : Affichage des mots de passe
        echo "<p>ID: " . $row['id'] . " | User: " . $row['username'] . " | Pass: " . $row['password'] . "</p>";
    }
}
?>

<!-- Vulnérabilité : Command Injection -->
<form method="POST">
    <input type="text" name="ping" placeholder="Host to ping">
    <input type="submit" value="Ping">
</form>

<!-- Vulnérabilité : XSS Stocké simulé -->
<form method="POST" action="save_comment.php">
    <textarea name="comment" placeholder="Add comment"></textarea>
    <input type="submit" value="Save">
</form>
</body>
</html>