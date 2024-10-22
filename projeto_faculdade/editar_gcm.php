<?php
// Iniciar a sessão
session_start();

// Conectar ao banco de dados
$mysqli = new mysqli("localhost", "root", "", "sistema_gcmsp");

if ($mysqli->connect_error) {
    die("Conexão falhou: " . $mysqli->connect_error);
}

// Verificar se o ID foi passado
if (!isset($_GET['id'])) {
    die("ID inválido.");
}

$escala_gcm_id = $_GET['id'];

// Consultar o registro atual
$query = "SELECT * FROM escala_gcm WHERE id = ?";
$stmt = $mysqli->prepare($query);
$stmt->bind_param("i", $escala_gcm_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("GCM não encontrado.");
}

$gcm = $result->fetch_assoc();

// Verificar se o formulário foi enviado
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Coletar dados do formulário
    $horario_inicio = $_POST['horario_inicio'];
    $horario_fim = $_POST['horario_fim'];
    $modalidade_id = $_POST['modalidade_id'];
    $posto_id = $_POST['posto_id'];
    $equipe_id = $_POST['equipe_id'];

    // Atualizar o registro
    $update_stmt = $mysqli->prepare("UPDATE escala_gcm SET horario_inicio = ?, horario_fim = ?, modalidade_id = ?, posto_id = ?, equipe_id = ? WHERE id = ?");
    $update_stmt->bind_param("ssiiii", $horario_inicio, $horario_fim, $modalidade_id, $posto_id, $equipe_id, $escala_gcm_id);

    if ($update_stmt->execute()) {
        echo "<div class='alert alert-success' role='alert'>GCM atualizado com sucesso!</div>";
    } else {
        echo "<div class='alert alert-danger' role='alert'>Erro ao atualizar GCM: " . $update_stmt->error . "</div>";
    }

    $update_stmt->close();

    // Redirecionar de volta à visualização da escala
    header("Location: visualizar_escala.php?escala_id=" . $gcm['escala_id']);
    exit();
}

// Fechar a conexão
$mysqli->close();
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar GCM</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
    <div class="container mt-5">
        <h1 class="mb-4">Editar GCM</h1>
        
        <!-- Formulário de Edição -->
        <form method="POST" action="">
            <div class="form-group">
                <label for="horario_inicio">Horário de Início:</label>
                <input type="time" name="horario_inicio" class="form-control" value="<?= htmlspecialchars($gcm['horario_inicio']); ?>" required>
            </div>
            <div class="form-group">
                <label for="horario_fim">Horário de Fim:</label>
                <input type="time" name="horario_fim" class="form-control" value="<?= htmlspecialchars($gcm['horario_fim']); ?>" required>
            </div>
            <!-- Inclua campos para modalidade, posto e equipe -->
            <!-- Exemplo de campo para modalidade -->
            <div class="form-group">
                <label for="modalidade_id">Modalidade:</label>
                <select name="modalidade_id" class="form-control" required>
                    <!-- Aqui você deverá adicionar opções de modalidades dinâmicas do banco -->
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Salvar Alterações</button>
            <a href="visualizar_escala.php?escala_id=<?= htmlspecialchars($escala_id); ?>" class="btn btn-secondary">Cancelar</a>
        </form>
    </div>
</body>
</html>
