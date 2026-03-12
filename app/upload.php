<?php
session_start();
require_once 'config.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_FILES['file'])) {
        $file = $_FILES['file'];
        $filename = $file['name'];
        $tmp_path = $file['tmp_name'];

        // Vulnérabilité : Pas de validation du type de fichier
        // Vulnérabilité : Nom de fichier non sanitisé
        $upload_path = "uploads/" . $filename;

        if (move_uploaded_file($tmp_path, $upload_path)) {
            // Vulnérabilité : XSS dans le message de succès
            $message = "File uploaded successfully: " . $filename;
        } else {
            $message = "Upload failed!";
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head><title>Upload - VulnShop</title></head>
<body>
<h2>File Upload</h2>
<!-- Vulnérabilité : Pas de token CSRF -->
<form method="POST" enctype="multipart/form-data">
    <input type="file" name="file">
    <input type="submit" value="Upload">
</form>

<?php
// Vulnérabilité : XSS
if ($message) echo "<p>" . $message . "</p>";

// Vulnérabilité : Listage des fichiers uploadés
if (is_dir('uploads/')) {
    echo "<h3>Uploaded files:</h3>";
    foreach (scandir('uploads/') as $f) {
        if ($f != '.' && $f != '..') {
            echo "<a href='uploads/" . $f . "'>" . $f . "</a><br>";
        }
    }
}
?>
</body>
</html>