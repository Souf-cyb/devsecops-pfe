<?php
session_start();
require_once 'config.php';

$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['file'])) {
    $file = $_FILES['file'];
    $filename = $file['name'];
    $tmp_path = $file['tmp_name'];

    // Vulnérabilité : Pas de validation du type
    if (!is_dir('uploads/')) mkdir('uploads/', 0777, true);
    $upload_path = "uploads/" . $filename;

    if (move_uploaded_file($tmp_path, $upload_path)) {
        $message = "✅ File uploaded: " . $filename;
        $message_type = 'success';
    } else {
        $message = "❌ Upload failed!";
        $message_type = 'error';
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Upload - VulnShop</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', sans-serif; background: #f5f5f5; }
        .navbar {
            background: linear-gradient(135deg, #1a1a2e, #16213e);
            padding: 15px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .navbar .logo { color: #e94560; font-size: 24px; font-weight: bold; text-decoration: none; }
        .navbar .logo span { color: white; }
        .nav-links a { color: #ccc; text-decoration: none; margin-left: 25px; }
        .nav-links a:hover { color: #e94560; }

        .container { max-width: 800px; margin: 50px auto; padding: 0 20px; }
        .upload-card {
            background: white;
            border-radius: 15px;
            padding: 40px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }
        .upload-card h2 { color: #1a1a2e; margin-bottom: 10px; }
        .upload-card p { color: #666; margin-bottom: 30px; }

        .upload-area {
            border: 3px dashed #e94560;
            border-radius: 10px;
            padding: 50px;
            text-align: center;
            background: #fff5f5;
            margin-bottom: 20px;
        }
        .upload-area p { font-size: 40px; margin-bottom: 10px; }
        .upload-area label {
            display: inline-block;
            padding: 10px 25px;
            background: #e94560;
            color: white;
            border-radius: 20px;
            cursor: pointer;
            margin-top: 10px;
        }
        .upload-area input[type="file"] { display: none; }
        .file-name { color: #666; margin-top: 10px; font-size: 14px; }

        .btn-upload {
            width: 100%;
            padding: 15px;
            background: linear-gradient(135deg, #1a1a2e, #16213e);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            cursor: pointer;
            margin-top: 20px;
        }
        .btn-upload:hover { background: #e94560; }

        .message {
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
        }
        .message.success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .message.error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }

        .warning-box {
            background: #fff3cd;
            border: 1px solid #ffc107;
            padding: 15px 20px;
            border-radius: 8px;
            margin-top: 20px;
            font-size: 13px;
            color: #856404;
        }

        .files-list { margin-top: 30px; }
        .files-list h3 { color: #1a1a2e; margin-bottom: 15px; }
        .file-item {
            display: flex;
            align-items: center;
            padding: 10px 15px;
            background: #f8f9fa;
            border-radius: 8px;
            margin-bottom: 8px;
        }
        .file-item a { color: #e94560; text-decoration: none; margin-left: 10px; }

        footer {
            background: #1a1a2e;
            color: #aaa;
            text-align: center;
            padding: 20px;
            margin-top: 50px;
        }
    </style>
</head>
<body>

<nav class="navbar">
    <a class="logo" href="index.php">Vuln<span>Shop</span></a>
    <div class="nav-links">
        <a href="index.php">🏠 Home</a>
        <a href="search.php">🔍 Search</a>
        <a href="login.php">🔐 Login</a>
    </div>
</nav>

<div class="container">
    <div class="upload-card">
        <h2>📁 File Upload</h2>
        <p>Upload any file to our server — no restrictions!</p>

        <?php if ($message): ?>
            <div class="message <?php echo $message_type; ?>">
                <?php echo $message; // Vulnérabilité XSS ?>
            </div>
        <?php endif; ?>

        <!-- Vulnérabilité : Pas de token CSRF -->
        <form method="POST" enctype="multipart/form-data">
            <div class="upload-area">
                <p>📂</p>
                <p style="font-size:16px; color:#666;">Drop your file here</p>
                <label for="fileInput">Choose File</label>
                <input type="file" id="fileInput" name="file"
                       onchange="document.querySelector('.file-name').textContent = this.files[0]?.name || ''">
                <div class="file-name">No file selected</div>
            </div>
            <button type="submit" class="btn-upload">⬆️ Upload File</button>
        </form>

        <div class="warning-box">
            ⚠️ <b>Vulnerability Demo:</b> This upload accepts ALL file types including
            PHP shells (.php), scripts (.sh), and executables (.exe).
            No validation is performed!
        </div>

        <!-- Vulnérabilité : Listage des fichiers -->
        <?php if (is_dir('uploads/')): ?>
        <div class="files-list">
            <h3>📋 Uploaded Files</h3>
            <?php foreach (scandir('uploads/') as $f): ?>
                <?php if ($f != '.' && $f != '..'): ?>
                    <div class="file-item">
                        📄 <a href="uploads/<?php echo $f; ?>"><?php echo $f; ?></a>
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<footer>
    <p>© 2024 VulnShop — DevSecOps PFE</p>
</footer>

</body>
</html>