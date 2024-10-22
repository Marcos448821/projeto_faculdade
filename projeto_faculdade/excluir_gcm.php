<?php
// Iniciar a sessão
session_start();

// Conectar ao banco de dados
$mysqli = new mysqli("localhost", "root", "", "sistema_gcmsp");

if ($mysqli->connect_error) {
    die("Conexão falhou: " . $mysqli->connect_error);
}

// Verificar se o ID do GCM e o ID da escala foram passados
if (!isset($_GET['id']) || !isset($_GET['escala_id'])) {
    die("ID inválido.");
}

$escala_gcm_id = intval($_GET['id']); // Converte para inteiro por segurança
$escala_id = intval($_GET['escala_id']);

// Excluir o GCM da escala
$stmt = $mysqli->prepare("DELETE FROM escala_gcm WHERE id = ?");
$stmt->bind_param("i", $escala_gcm_id);

if ($stmt->execute()) {
    $_SESSION['success_message'] = "GCM excluído com sucesso!";
} else {
    $_SESSION['error_message'] = "Erro ao excluir o GCM.";
}

$stmt->close();

// Redirecionar de volta à visualização da escala
header("Location: visualizar_escala.php?escala_id=$escala_id");
exit();

// Fechar a conexão
$mysqli->close();
?>
