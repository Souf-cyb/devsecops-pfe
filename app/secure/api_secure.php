<?php
session_start();
require_once 'config_secure.php';
set_security_headers();

header('Content-Type: application/json');

// ✅ Vérification authentification obligatoire
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Authentication required']);
    exit();
}

$conn   = getDB();
$action = sanitize($_GET['action'] ?? '');

switch ($action) {

    case 'getUser':
        // ✅ Validation de l'ID
        $id = validate_int($_GET['id'] ?? '');
        if (!$id || $id <= 0) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid ID']);
            exit();
        }

        // ✅ IDOR fix — un user ne peut voir que son propre profil
        if ($id != $_SESSION['user_id'] && !$_SESSION['is_admin']) {
            http_response_code(403);
            echo json_encode(['error' => 'Access denied']);
            exit();
        }

        // ✅ Prepared Statement — plus de SQLi
        $stmt = $conn->prepare(
            "SELECT id, username, email FROM users WHERE id = ?"
        );
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $user   = $result->fetch_assoc();
        $stmt->close();

        // ✅ Pas de mot de passe dans la réponse
        echo json_encode($user ?: ['error' => 'User not found']);
        break;

    case 'getProducts':
        // ✅ Endpoint public mais sans données sensibles
        $stmt = $conn->prepare(
            "SELECT id, name, description, price FROM products LIMIT 50"
        );
        $stmt->execute();
        $result   = $stmt->get_result();
        $products = [];
        while ($row = $result->fetch_assoc()) {
            $products[] = $row;
        }
        $stmt->close();
        echo json_encode($products);
        break;

    default:
        // ✅ Pas d'endpoint exec — RCE supprimé
        echo json_encode([
            'name'      => 'VulnShop Secure API',
            'version'   => '2.0.0',
            'endpoints' => [
                ['method' => 'GET', 'url' => '?action=getUser&id=1',  'auth' => 'required'],
                ['method' => 'GET', 'url' => '?action=getProducts',   'auth' => 'public'],
            ]
        ]);
}
?>