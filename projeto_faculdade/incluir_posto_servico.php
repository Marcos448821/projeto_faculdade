<?php
// Conectar ao banco de dados
$mysqli = new mysqli("localhost", "root", "", "sistema_gcmsp");

if ($mysqli->connect_error) {
    die("Conexão falhou: " . $mysqli->connect_error);
}

// Verificar se os dados foram enviados
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nome = $_POST['nome'];
    $descricao = $_POST['descricao'];
    $localizacao = $_POST['localizacao'];
    $ativo = isset($_POST['ativo']) ? 1 : 0;

    // Inserir novo posto de serviço
    $stmt = $mysqli->prepare("
        INSERT INTO postos_servicos (nome, descricao, localizacao, ativo) 
        VALUES (?, ?, ?, ?)
    ");
    $stmt->bind_param('ssss', $nome, $descricao, $localizacao, $ativo);
    if ($stmt->execute()) {
        header('Location: gerenciar_postos_servico.php');
        exit;
    } else {
        echo "<div class='alert alert-danger'>Erro ao incluir posto de serviço: " . $mysqli->error . "</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Incluir Novo Posto de Serviço</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
    <div class="container mt-5">
        <h1>Incluir Novo Posto de Serviço</h1>

        <form action="incluir_posto_servico.php" method="POST">
            <div class="form-group">
                <label for="nome">Nome:</label>
                <input type="text" id="nome" name="nome" class="form-control" required>
            </div>

            <div class="form-group">
                <label for="descricao">Descrição:</label>
                <textarea id="descricao" name="descricao" class="form-control"></textarea>
            </div>

            <div class="form-group">
                <label for="localizacao">Localização:</label>
                <input type="text" id="localizacao" name="localizacao" class="form-control">
            </div>

            <div class="form-group">
                <label for="ativo">Ativo:</label>
                <input type="checkbox" id="ativo" name="ativo" checked>
            </div>

            <button type="submit" class="btn btn-primary">Incluir Posto de Serviço</button>
        </form>

        <a href="gerenciar_postos_servico.php" class="btn btn-secondary mt-4">Voltar</a>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
