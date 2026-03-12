<?php
session_start();
require_once 'config.php';

$results = [];
$search = '';

if (isset($_GET['q'])) {
    $search = $_GET['q'];
    $conn = getDB();

    // Vulnérabilité : SQL Injection
    $query = "SELECT * FROM products WHERE name LIKE '%" . $search . "%' OR description LIKE '%" . $search . "%'";
    $result = mysqli_query($conn, $query);

    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $results[] = $row;
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head><title>Search - VulnShop</title></head>
<body>
<h2>Search Products</h2>
<form method="GET">
    <input type="text" name="q" value="<?php echo $_GET['q'] ?? ''; ?>">
    <input type="submit" value="Search">
</form>

<?php
// Vulnérabilité : XSS - affichage direct de la recherche
if ($search) {
    echo "<p>Results for: <b>" . $search . "</b></p>";
}

// Vulnérabilité : Affichage des données sans encodage
foreach ($results as $product) {
    echo "<div>";
    echo "<h3>" . $product['name'] . "</h3>";
    echo "<p>" . $product['description'] . "</p>";
    echo "</div>";
}

// Vulnérabilité : eval() avec entrée utilisateur
if (isset($_GET['filter'])) {
    eval($_GET['filter']);
}
?>
</body>
</html>