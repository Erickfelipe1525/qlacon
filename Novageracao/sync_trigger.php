<?php
// Endpoint server-side para disparar a sincronização sem expor a chave ao cliente.
session_start();
require_once __DIR__ . '/config_sync.php';

// Only allow POST from same origin
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$token = $_POST['token'] ?? '';
if (empty($token) || !isset($_SESSION['sync_token']) || $token !== $_SESSION['sync_token']) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Invalid token']);
    exit;
}

$php = 'C:\\xampp\\php\\php.exe';
$script = escapeshellarg(__DIR__ . '/sync_profiles.php');
$secret = escapeshellarg($SYNC_SECRET);
//$cmd = "$php $script $secret 2>&1";
$cmd = "$php $script $secret";
// Execute and capture output
exec($cmd, $output, $ret);
$out = implode("\n", $output);
header('Content-Type: application/json; charset=utf-8');
echo json_encode(['success' => $ret === 0, 'exit_code' => $ret, 'output' => $out]);

?>