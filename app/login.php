<?php
session_start();
require_once 'config.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Vulnérabilité : SQL Injection dans login
    $conn = getDB();
    $query = "SELECT * FROM users WHERE username='" . $username . "' AND password='" . $password . "'";
    $result = mysqli_query($conn, $query);

    if ($result && mysqli_num_rows($result) > 0) {
        $user = mysqli_fetch_assoc($result);

        // Vulnérabilité : Stockage d'infos sensibles en session
        $_SESSION['user'] = $user;
        $_SESSION['username'] = $username;
        $_SESSION['is_admin'] = $user['is_admin'];

        // Vulnérabilité : Redirection non validée
        $redirect = isset($_GET['redirect']) ? $_GET['redirect'] : 'dashboard.php';
        header("Location: " . $redirect);
        exit();
    } else {
        // Vulnérabilité : Message d'erreur trop détaillé
        $error = "Login failed for user: " . $username;
    }
}
?>
<!DOCTYPE html>
<html>
<head><title>Login - VulnShop</title></head>
<body>
<h2>Login</h2>
<?php
// Vulnérabilité : XSS dans le message d'erreur
if ($error) echo "<p style='color:red'>" . $error . "</p>";
?>
<form method="POST">
    <!-- Vulnérabilité : Pas de token CSRF -->
    <input type="text" name="username" placeholder="Username"><br><br>
    <input type="password" name="password" placeholder="Password"><br><br>
    <input type="submit" value="Login">
</form>

<!-- Vulnérabilité : Commentaire avec infos sensibles -->
<!-- Default admin credentials: admin/admin123 -->
<!-- TODO: Remove this before production -->
</body>
</html>