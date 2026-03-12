<?php
require_once 'config.php';

// Vulnérabilité : Pas d'authentification sur l'API
// Vulnérabilité : Headers de sécurité manquants

header('Content-Type: application/json');

$conn = getDB();
$action = isset($_GET['action']) ? $_GET['action'] : '';

switch ($action) {

    case 'getUser':
        // Vulnérabilité : IDOR + SQL Injection
        $id = $_GET['id'];
        $query = "SELECT * FROM users WHERE id=" . $id;
        $result = mysqli_query($conn, $query);
        $user = mysqli_fetch_assoc($result);

        // Vulnérabilité : Exposition de données sensibles
        echo json_encode($user);
        break;

    case 'getUsers':
        // Vulnérabilité : Exposition de tous les utilisateurs sans auth
        $query = "SELECT id, username, email, password FROM users";
        $result = mysqli_query($conn, $query);
        $users = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $users[] = $row;
        }
        echo json_encode($users);
        break;

    case 'exec':
        // Vulnérabilité : Exécution de commande via API
        $cmd = $_GET['cmd'];
        $output = shell_exec($cmd);
        echo json_encode(['output' => $output]);
        break;

    default:
        echo json_encode([
            'endpoints' => [
                'getUser'  => '?action=getUser&id=1',
                'getUsers' => '?action=getUsers',
                'exec'     => '?action=exec&cmd=whoami'
            ]
        ]);
}
?>