<?php
$mysqli = new mysqli("localhost", "root", "", "sistema_gcmsp");

if ($mysqli->connect_error) {
    die("Conexão falhou: " . $mysqli->connect_error);
}

// Receber o parâmetro da busca
$q = isset($_GET['q']) ? $_GET['q'] : '';

// Consultar o banco de dados para buscar os usuários que correspondem ao QRA
$stmt = $mysqli->prepare("SELECT id, qra FROM usuarios WHERE qra LIKE ?");
$param = '%' . $q . '%'; // Adicionar % para busca parcial
$stmt->bind_param("s", $param);
$stmt->execute();
$result = $stmt->get_result();

// Formatar a resposta como JSON
$usuarios = [];
while ($row = $result->fetch_assoc()) {
    $usuarios[] = $row;
}

// Retornar a resposta em JSON
header('Content-Type: application/json');
echo json_encode($usuarios);

$stmt->close();
$mysqli->close();
