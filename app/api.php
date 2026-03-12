<?php
require_once 'config.php';

header('Content-Type: application/json');
// Vulnérabilité : Pas de headers de sécurité
// Vulnérabilité : Pas d'authentification

$conn = getDB();
$action = isset($_GET['action']) ? $_GET['action'] : '';

switch ($action) {
    case 'getUser':
        $id = $_GET['id'];
        // Vulnérabilité : SQL Injection + IDOR
        $query = "SELECT * FROM users WHERE id=" . $id;
        $result = mysqli_query($conn, $query);
        $user = mysqli_fetch_assoc($result);
        echo json_encode($user); // Vulnérabilité : données sensibles exposées
        break;

    case 'getUsers':
        // Vulnérabilité : Exposition de tous les users sans auth
        $query = "SELECT id, username, email, password FROM users";
        $result = mysqli_query($conn, $query);
        $users = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $users[] = $row;
        }
        echo json_encode($users);
        break;

    case 'exec':
        // Vulnérabilité : Command Injection via API
        $cmd = $_GET['cmd'];
        $output = shell_exec($cmd);
        echo json_encode(['output' => $output]);
        break;

    default:
        echo json_encode([
            'name' => 'VulnShop API',
            'version' => '1.0.0',
            'endpoints' => [
                ['method' => 'GET', 'url' => '?action=getUser&id=1',  'desc' => 'Get user by ID (IDOR + SQLi)'],
                ['method' => 'GET', 'url' => '?action=getUsers',       'desc' => 'Get all users (No Auth)'],
                ['method' => 'GET', 'url' => '?action=exec&cmd=whoami','desc' => 'Execute command (RCE)'],
            ]
        ]);
}
?>