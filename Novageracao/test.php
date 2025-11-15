<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$host = "localhost";
$user = "root";
$password = "";
$database = "novageracao_db";

$conn = new mysqli($host, $user, $password, $database);
if ($conn->connect_error) {
    die("Erro de conexão: " . $conn->connect_error);
}

// Test query
$result = $conn->query("SELECT id, nome, player_id, funcao, descricao, trofeus, brawlers FROM jogadores WHERE ativo = 1 ORDER BY posicao ASC LIMIT 3");

echo "<h2>Jogadores encontrados: " . $result->num_rows . "</h2>";
echo "<pre>";
while ($row = $result->fetch_assoc()) {
    print_r($row);
}
echo "</pre>";

$conn->close();
?>
