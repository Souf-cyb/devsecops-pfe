<?php
require_once 'config.php';
header('Content-Type: application/json');
// ⚠️ Pas de headers de sécurité, pas d'authentification
$conn=getDB();
$action=isset($_GET['action'])?$_GET['action']:'';
switch($action){
    case 'getUser':
        // ⚠️ SQLi + IDOR
        $result=mysqli_query($conn,"SELECT * FROM users WHERE id=".$_GET['id']);
        echo json_encode(mysqli_fetch_assoc($result));
        break;
    case 'getUsers':
        // ⚠️ No auth — tous les users exposés
        $result=mysqli_query($conn,"SELECT id,username,email,password FROM users");
        $users=[];
        while($row=mysqli_fetch_assoc($result)) $users[]=$row;
        echo json_encode($users);
        break;
    case 'exec':
        // ⚠️ RCE via API
        echo json_encode(['output'=>shell_exec($_GET['cmd'])]);
        break;
    default:
        echo json_encode([
            'name'=>'VulnShop API','version'=>'1.0.0',
            'endpoints'=>[
                ['GET','?action=getUser&id=1','IDOR + SQLi'],
                ['GET','?action=getUsers','No Auth — all users + passwords'],
                ['GET','?action=exec&cmd=whoami','RCE'],
            ]
        ]);
}
?>