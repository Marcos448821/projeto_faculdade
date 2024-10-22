<?php
session_start();

// Verificar se o usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}

// Conectar ao banco de dados
$mysqli = new mysqli("localhost", "root", "", "sistema_gcmsp");

if ($mysqli->connect_error) {
    die("Conexão falhou: " . $mysqli->connect_error);
}

// Verificar e obter dados do formulário
$mapa_forca_id = isset($_POST['mapa_forca_id']) ? intval($_POST['mapa_forca_id']) : 0;
$equipe_id = isset($_POST['equipe_id']) ? intval($_POST['equipe_id']) : 0;
$gcm_id = isset($_POST['gcm_id']) ? intval($_POST['gcm_id']) : 0;

if ($mapa_forca_id <= 0 || $equipe_id <= 0 || $gcm_id <= 0) {
    die("Dados inválidos.");
}

// Inserir o GCM na tabela mapa_forca_gcm
$insert_query = "INSERT INTO mapa_forca_gcm (mapa_forca_id, gcm_id, equipe_id) VALUES (?, ?, ?)";
$stmt = $mysqli->prepare($insert_query);
$stmt->bind_param("iii", $mapa_forca_id, $gcm_id, $equipe_id);

if ($stmt->execute()) {
    header("Location: sucesso.php"); // Redirecionar para uma página de sucesso ou confirmação
    exit();
} else {
    die("Erro ao inserir GCM: " . $stmt->error);
}
