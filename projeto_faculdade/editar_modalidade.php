<?php
// Conectar ao banco de dados
$mysqli = new mysqli("localhost", "root", "", "sistema_gcmsp");

if ($mysqli->connect_error) {
    die("Conexão falhou: " . $mysqli->connect_error);
}

// Verificar se o ID da modalidade foi passado
if (!isset($_GET['id'])) {
    die("ID da modalidade não fornecido.");
}

$id = $_GET['id'];

// Obter os dados da modalidade
$stmt = $mysqli->prepare("SELECT * FROM modalidades_servicos WHERE id = ?");
$stmt->bind_param('i', $id);
$stmt->execute();
$result = $stmt->get_result();
$modalidade = $result->fetch_assoc();

if (!$modalidade) {
    die("Modalidade não encontrada.");
}

// Processar o envio do formulário
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nome = $_POST['nome'];
    $descricao = $_POST['descricao'];
    $ativo = $_POST['ativo'];

    // Atualizar modalidade de serviço
    $stmt = $mysqli->prepare("UPDATE modalidades_servicos SET nome = ?, descricao = ?, ativo = ? WHERE id = ?");
    $stmt->bind_param('ssii', $nome, $descricao, $ativo, $id);
    if ($stmt->execute()) {
        header('Location: gerenciar_modalidades_servico.php');
        exit;
    } else {
        echo "<div class='alert alert-danger'>Erro ao atualizar modalidade de serviço: " . $mysqli->error . "</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Modalidade de Serviço</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
    <div class="container mt-5">
        <h1>Editar Modalidade de Serviço</h1>
        <form action="editar_modalidade.php?id=<?php echo $id; ?>" method="POST">
            <div class="form-group">
                <label for="nome">Nome:</label>
                <input type="text" id="nome" name="nome" class="form-control" value="<?php echo htmlspecialchars($modalidade['nome']); ?>" required>
            </div>

            <div class="form-group">
                <label for="descricao">Descrição:</label>
                <textarea id="descricao" name="descricao" class="form-control"><?php echo htmlspecialchars($modalidade['descricao']); ?></textarea>
            </div>

            <div class="form-group">
                <label for="ativo">Ativo:</label>
                <select id="ativo" name="ativo" class="form-control">
                    <option value="1" <?php echo $modalidade['ativo'] ? 'selected' : ''; ?>>Sim</option>
                    <option value="0" <?php echo !$modalidade['ativo'] ? 'selected' : ''; ?>>Não</option>
                </select>
            </div>

            <button type="submit" class="btn btn-primary">Atualizar Modalidade</button>
        </form>

        <a href="gerenciar_modalidades_servico.php" class="btn btn-secondary mt-4">Voltar</a>
    </div>

    <!-- Scripts do Bootstrap -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
