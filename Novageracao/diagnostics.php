<?php
// Diagnostics endpoint: verifica conectividade com o banco e retorna JSON.
header('Content-Type: application/json; charset=utf-8');
// carregar config
$cfg = __DIR__ . '/config.php';
if (file_exists($cfg)) require_once $cfg;

$resp = ['ok' => false, 'db' => null, 'php_version' => phpversion()];

$conn = @new mysqli($host, $user, $password, $database);
if ($conn->connect_errno !== 0) {
    $resp['db'] = ['ok' => false, 'error' => $conn->connect_error];
    http_response_code(500);
    echo json_encode($resp, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    exit;
}
$resp['ok'] = true;
$resp['db'] = ['ok' => true, 'host' => $host, 'database' => $database];
$conn->close();

echo json_encode($resp, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
?>