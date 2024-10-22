<?php
// Conectar ao banco de dados
$mysqli = new mysqli("localhost", "root", "", "sistema_gcmsp");

if ($mysqli->connect_error) {
    die("Conexão falhou: " . $mysqli->connect_error);
}

// Processar o envio do formulário
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nome = $_POST['nome'];
    $descricao = $_POST['descricao'];
    $ativo = $_POST['ativo'];

    // Inserir nova modalidade de serviço
    $stmt = $mysqli->prepare("INSERT INTO modalidades_servicos (nome, descricao, ativo) VALUES (?, ?, ?)");
    $stmt->bind_param('ssi', $nome, $descricao, $ativo);
    if ($stmt->execute()) {
        header('Location: gerenciar_modalidades_servico.php');
        exit;
    } else {
        echo "<div class='alert alert-danger'>Erro ao incluir modalidade de serviço: " . $mysqli->error . "</div>";
    }
}
?>
