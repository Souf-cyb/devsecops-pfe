<?php
session_start();
require_once '../secure/config_secure.php';
set_security_headers();

$message      = '';
$message_type = '';

// ✅ Types de fichiers autorisés uniquement
$allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'application/pdf'];
$allowed_exts  = ['jpg', 'jpeg', 'png', 'gif', 'pdf'];
$max_size      = 2 * 1024 * 1024; // 2MB

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    // ✅ Validation CSRF
    validate_csrf_token();

    if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
        $file     = $_FILES['file'];
        $filename = basename($file['name']);
        $tmp_path = $file['tmp_name'];
        $filesize = $file['size'];
        $ext      = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        // ✅ Validation taille
        if ($filesize > $max_size) {
            $message      = "File too large. Maximum size is 2MB.";
            $message_type = 'error';
        }
        // ✅ Validation extension
        elseif (!in_array($ext, $allowed_exts)) {
            $message      = "File type not allowed. Allowed: JPG, PNG, GIF, PDF";
            $message_type = 'error';
        }
        // ✅ Validation MIME type réel
        elseif (!in_array(mime_content_type($tmp_path), $allowed_types)) {
            $message      = "Invalid file content detected.";
            $message_type = 'error';
        }
        else {
            // ✅ Nom de fichier aléatoire — évite l'écrasement
            $safe_filename = bin2hex(random_bytes(8)) . '.' . $ext;
            $upload_dir    = 'uploads/safe/';

            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }

            // ✅ .htaccess pour bloquer l'exécution PHP
            if (!file_exists($upload_dir . '.htaccess')) {
                file_put_contents($upload_dir . '.htaccess',
                    "php_flag engine off\nOptions -ExecCGI\n"
                );
            }

            if (move_uploaded_file($tmp_path, $upload_dir . $safe_filename)) {
                $message      = "File uploaded successfully.";
                $message_type = 'success';
            } else {
                $message      = "Upload failed. Please try again.";
                $message_type = 'error';
            }
        }
    }
}

$csrf_token = generate_csrf_token();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Secure Upload - VulnShop</title>
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family:'Segoe UI',sans-serif; background:#f5f5f5; }
        .navbar { background:linear-gradient(135deg,#1a1a2e,#16213e); padding:15px 30px; display:flex; justify-content:space-between; align-items:center; }
        .navbar .logo { color:#27ae60; font-size:24px; font-weight:bold; text-decoration:none; }
        .navbar .logo span { color:white; }
        .secure-bar { background:#eafaf1; border-bottom:1px solid #a9dfbf; padding:8px 30px; font-size:12px; color:#27ae60; }
        .container { max-width:700px; margin:40px auto; padding:0 20px; }
        .upload-card { background:white; border-radius:15px; padding:35px; box-shadow:0 5px 20px rgba(0,0,0,0.1); }
        .upload-card h2 { color:#1a1a2e; margin-bottom:8px; }
        .upload-card p { color:#666; margin-bottom:25px; font-size:14px; }
        .upload-area { border:3px dashed #27ae60; border-radius:10px; padding:40px; text-align:center; background:#f9fff9; margin-bottom:20px; }
        .upload-area label { display:inline-block; padding:10px 25px; background:#27ae60; color:white; border-radius:20px; cursor:pointer; margin-top:10px; }
        .upload-area input[type="file"] { display:none; }
        .btn-upload { width:100%; padding:14px; background:#1a1a2e; color:white; border:none; border-radius:10px; font-size:15px; cursor:pointer; margin-top:15px; }
        .btn-upload:hover { background:#27ae60; }
        .message { padding:12px 20px; border-radius:8px; margin-bottom:20px; font-size:13px; }
        .message.success { background:#d4edda; color:#155724; border:1px solid #c3e6cb; }
        .message.error { background:#f8d7da; color:#721c24; border:1px solid #f5c6cb; }
        .security-info { background:#eafaf1; border:1px solid #a9dfbf; padding:15px; border-radius:8px; margin-top:20px; font-size:12px; color:#27ae60; }
        .security-info li { margin-left:15px; margin-top:4px; }
        footer { background:#1a1a2e; color:#aaa; text-align:center; padding:20px; margin-top:50px; }
    </style>
</head>
<body>
<nav class="navbar">
    <a class="logo" href="index_secure.php">Vuln<span>Shop</span> Secure</a>
    <div class="nav-links" style="display:flex">
        <a href="index_secure.php" style="color:#ccc;text-decoration:none;margin-left:25px">Home</a>
    </div>
</nav>
<div class="secure-bar">✅ Secure version — File upload validation enabled</div>

<div class="container">
    <div class="upload-card">
        <h2>Secure File Upload</h2>
        <p>Only images and PDFs are accepted. Max size: 2MB.</p>

        <?php if ($message): ?>
            <div class="message <?php echo $message_type; ?>">
                <?php echo sanitize($message); ?>
            </div>
        <?php endif; ?>

        <!-- ✅ Token CSRF -->
        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token"
                   value="<?php echo $csrf_token; ?>">
            <div class="upload-area">
                <p style="font-size:40px">📂</p>
                <p style="color:#666;font-size:14px">JPG, PNG, GIF, PDF only</p>
                <label for="fileInput">Choose File</label>
                <input type="file" id="fileInput" name="file"
                       accept=".jpg,.jpeg,.png,.gif,.pdf">
            </div>
            <button type="submit" class="btn-upload">Upload Securely</button>
        </form>

        <div class="security-info">
            <b>Security fixes applied:</b>
            <ul>
                <li>✅ File type validation (extension + MIME)</li>
                <li>✅ File size limit (2MB max)</li>
                <li>✅ Random filename (no overwrite)</li>
                <li>✅ PHP execution blocked (.htaccess)</li>
                <li>✅ CSRF token validation</li>
                <li>✅ No file listing exposed</li>
            </ul>
        </div>
    </div>
</div>

<footer><p>© 2026 VulnShop Secure — DevSecOps PFE</p></footer>
</body>
</html>