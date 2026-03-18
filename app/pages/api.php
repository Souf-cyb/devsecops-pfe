<?php
// ⚠️ API intentionnellement vulnérable — pas d'auth, pas de rate limiting
require_once '../includes/config.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');  // ⚠️ CORS ouvert
// ⚠️ Pas de headers de sécurité (X-Frame-Options, CSP, etc.)

$conn   = getDB();
$action = $_GET['action'] ?? $_GET['endpoint'] ?? '';
$method = $_SERVER['REQUEST_METHOD'];

// ⚠️ Pas d'authentification sur aucun endpoint
switch ($action) {

    // ─── Produits ─────────────────────────────
    case 'products':
        $cat  = $_GET['cat']  ?? '';
        $sort = $_GET['sort'] ?? 'id';
        // ⚠️ SQLi via $sort et $cat
        $sql = "SELECT p.*, c.name as category FROM products p JOIN categories c ON p.category_id=c.id";
        if ($cat) $sql .= " WHERE p.category_id=$cat";
        $sql .= " ORDER BY p.$sort";
        $res  = mysqli_query($conn, $sql);
        $data = [];
        while ($row = mysqli_fetch_assoc($res)) $data[] = $row;
        echo json_encode(['status'=>'ok','count'=>count($data),'data'=>$data]);
        break;

    case 'product':
        $id  = $_GET['id'] ?? '1';
        // ⚠️ SQLi via id
        $res = mysqli_query($conn, "SELECT p.*, c.name as category FROM products p JOIN categories c ON p.category_id=c.id WHERE p.id=$id");
        $row = mysqli_fetch_assoc($res);
        echo json_encode($row ?: ['error'=>'Product not found']);
        break;

    // ─── Utilisateurs — ⚠️ No auth, IDOR ─────
    case 'users':
        // ⚠️ Expose tous les utilisateurs + mots de passe
        $res   = mysqli_query($conn, "SELECT id, username, email, password, full_name, phone, address, is_admin, created_at, last_login FROM users");
        $users = [];
        while ($row = mysqli_fetch_assoc($res)) $users[] = $row;
        echo json_encode(['status'=>'ok','count'=>count($users),'data'=>$users]);
        break;

    case 'user':
        $id = $_GET['id'] ?? '1';
        // ⚠️ SQLi + IDOR — expose tout sans auth
        $res = mysqli_query($conn, "SELECT * FROM users WHERE id=$id");
        $row = mysqli_fetch_assoc($res);
        echo json_encode($row ?: ['error'=>'User not found']);
        break;

    // ─── Commandes ─────────────────────────────
    case 'orders':
        $user = $_GET['user_id'] ?? '';
        // ⚠️ IDOR — n'importe qui peut voir les commandes de n'importe qui
        $sql = "SELECT o.*, u.username, u.email FROM orders o JOIN users u ON o.user_id=u.id";
        if ($user) $sql .= " WHERE o.user_id=$user"; // ⚠️ SQLi
        $res    = mysqli_query($conn, $sql);
        $orders = [];
        while ($row = mysqli_fetch_assoc($res)) $orders[] = $row;
        echo json_encode(['status'=>'ok','data'=>$orders]);
        break;

    // ─── Recherche ─────────────────────────────
    case 'search':
        $q = $_GET['q'] ?? '';
        // ⚠️ SQLi via q
        $sql = "SELECT p.*, c.name as category FROM products p JOIN categories c ON p.category_id=c.id WHERE p.name LIKE '%$q%' OR p.description LIKE '%$q%'";
        $res = mysqli_query($conn, $sql);
        $data = [];
        while ($row = mysqli_fetch_assoc($res)) $data[] = $row;
        echo json_encode(['status'=>'ok','results'=>$data]);
        break;

    // ─── Exécution de commande — ⚠️ RCE ────────
    case 'exec':
    case 'debug':
        // ⚠️ Remote Code Execution via API
        $cmd = $_GET['cmd'] ?? $_POST['cmd'] ?? 'whoami';
        $output = shell_exec($cmd);
        echo json_encode([
            'status' => 'ok',
            'cmd'    => $cmd,
            'output' => $output,
            'server' => [
                'php'  => PHP_VERSION,
                'os'   => PHP_OS,
                'user' => get_current_user(),
            ]
        ]);
        break;

    // ─── Infos serveur ─────────────────────────
    case 'info':
        // ⚠️ Information disclosure
        echo json_encode([
            'app'       => APP_NAME,
            'version'   => VERSION,
            'debug'     => DEBUG,
            'php'       => PHP_VERSION,
            'server'    => $_SERVER['SERVER_SOFTWARE'] ?? 'Apache',
            'db_host'   => DB_HOST,
            'db_name'   => DB_NAME,
            'db_user'   => DB_USER,
            // ⚠️ Expose les secrets
            'stripe_key'=> STRIPE_KEY,
            'jwt_secret'=> JWT_SECRET,
        ]);
        break;

    // ─── Documentation ─────────────────────────
    default:
        echo json_encode([
            'name'    => 'VulnShop REST API',
            'version' => '2.0',
            'base_url'=> '/pages/api.php',
            'auth'    => 'None required (intentionally vulnerable)',
            'endpoints' => [
                ['GET', '?action=products',            'Liste tous les produits'],
                ['GET', '?action=products&cat=1',      'Produits par catégorie (SQLi)'],
                ['GET', '?action=product&id=1',        'Détail produit (SQLi)'],
                ['GET', '?action=users',               'Tous les users + passwords (No Auth)'],
                ['GET', '?action=user&id=1',           'User par ID (IDOR + SQLi)'],
                ['GET', '?action=orders',              'Toutes les commandes (No Auth)'],
                ['GET', '?action=orders&user_id=1',    'Commandes par user (IDOR)'],
                ['GET', '?action=search&q=laptop',     'Recherche produits (SQLi)'],
                ['GET', '?action=exec&cmd=whoami',     '⚠️ RCE — exécution de commandes'],
                ['GET', '?action=info',                '⚠️ Info disclosure — secrets exposés'],
            ]
        ], JSON_PRETTY_PRINT);
}
?>