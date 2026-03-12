<?php
session_start();
require_once 'config.php';

// Vulnérabilité : Pas de vérification d'authentification correcte
if (!isset($_SESSION['username'])) {
    // Vulnérabilité : Message révélant la structure
    die("Access denied. Please <a href='login.php'>login</a> first.");
}

$conn = getDB();

// Vulnérabilité : IDOR - accès aux données d'autres utilisateurs
if (isset($_GET['user_id'])) {
    $user_id = $_GET['user_id'];
    // Pas de vérification que l'ID appartient à l'utilisateur connecté
    $query = "SELECT * FROM users WHERE id=" . $user_id;
    $result = mysqli_query($conn, $query);
    $user_data = mysqli_fetch_assoc($result);
}

// Vulnérabilité : Affichage des données sensibles
$query = "SELECT * FROM users WHERE username='" . $_SESSION['username'] . "'";
$result = mysqli_query($conn, $query);
$current_user = mysqli_fetch_assoc($result);
?>
<!DOCTYPE html>
<html>
<head><title>Dashboard - VulnShop</title></head>
<body>
<h2>Dashboard</h2>
<p>Welcome, <?php echo $_SESSION['username']; ?></p>

<?php
// Vulnérabilité : Affichage du mot de passe en clair
if ($current_user) {
    echo "<p>Your account details:</p>";
    echo "<p>Username: " . $current_user['username'] . "</p>";
    echo "<p>Email: " . $current_user['email'] . "</p>";
    echo "<p>Password: " . $current_user['password'] . "</p>";
}

// Vulnérabilité : IDOR
if (isset($user_data)) {
    echo "<h3>Viewing user data:</h3>";
    echo "<pre>" . print_r($user_data, true) . "</pre>";
}
?>

<!-- Vulnérabilité : CSRF - action sensible sans token -->
<form method="POST" action="delete_account.php">
    <input type="hidden" name="user_id" value="<?php echo $current_user['id']; ?>">
    <button type="submit">Delete Account</button>
</form>
</body>
</html>