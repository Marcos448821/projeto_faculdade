<?php
// Inicia a sessão
session_start();

// Verifica se o usuário está autenticado
if (!isset($_SESSION['cpf'])) {
    header("Location: login.php");
    exit();
}

// Inclui a conexão com o banco de dados
include 'includes/db_connect.php';

// Verifica se o ID da viatura foi passado
if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo "ID da viatura não fornecido.";
    exit();
}

$id = intval($_GET['id']);

// Obtém os dados da viatura
$query = "SELECT * FROM viaturas WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$viatura = $result->fetch_assoc();

if (!$viatura) {
    echo "Viatura não encontrada.";
    exit();
}

// Se o formulário for enviado
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $prefixo = $_POST['prefixo'];
    $placa = $_POST['placa'];
    $modelo = $_POST['modelo'];
    $ano = $_POST['ano'];
    $km_atual = $_POST['km_atual'];
    $cia = $_POST['cia'];

    // Atualiza os dados da viatura
    $query_update = "UPDATE viaturas SET prefixo = ?, placa = ?, modelo = ?, ano = ?, km_atual = ?, cia = ? WHERE id = ?";
    $stmt = $conn->prepare($query_update);
    $stmt->bind_param("ssssis", $prefixo, $placa, $modelo, $ano, $km_atual, $cia, $id);

    if ($stmt->execute()) {
        echo "Viatura atualizada com sucesso!";
    } else {
        echo "Erro ao atualizar viatura: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Viatura - Sistema GMCSP</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
    <div class="container mt-5">
        <h2>Editar Viatura</h2>
        <form method="post">
            <div class="form-group">
                <label for="prefixo">Prefixo</label>
                <input type="text" class="form-control" id="prefixo" name="prefixo" value="<?php echo htmlspecialchars($viatura['prefixo']); ?>" required>
            </div>
            <div class="form-group">
                <label for="placa">Placa</label>
                <input type="text" class="form-control" id="placa" name="placa" value="<?php echo htmlspecialchars($viatura['placa']); ?>" required>
            </div>
            <div class="form-group">
                <label for="modelo">Modelo</label>
                <input type="text" class="form-control" id="modelo" name="modelo" value="<?php echo htmlspecialchars($viatura['modelo']); ?>" required>
            </div>
            <div class="form-group">
                <label for="ano">Ano</label>
                <input type="number" class="form-control" id="ano" name="ano" value="<?php echo htmlspecialchars($viatura['ano']); ?>" required>
            </div>
            <div class="form-group">
                <label for="km_atual">KM Atual</label>
                <input type="number" class="form-control" id="km_atual" name="km_atual" value="<?php echo htmlspecialchars($viatura['km_atual']); ?>" required>
            </div>
            <div class="form-group">
                <label for="cia">Companhia (CIA)</label>
                <select class="form-control" id="cia" name="cia" required>
                    <option value="IOF" <?php if ($viatura['cia'] == 'IOF') echo 'selected'; ?>>IOF</option>
                    <option value="IOT" <?php if ($viatura['cia'] == 'IOT') echo 'selected'; ?>>IOT</option>
                    <option value="IOPS" <?php if ($viatura['cia'] == 'IOPS') echo 'selected'; ?>>IOPS</option>
                    <option value="IE" <?php if ($viatura['cia'] == 'IE') echo 'selected'; ?>>IE</option>
                    <option value="IAET" <?php if ($viatura['cia'] == 'IAET') echo 'selected'; ?>>IAET</option>
                    <option value="IPGE" <?php if ($viatura['cia'] == 'IPGE') echo 'selected'; ?>>IPGE</option>
                    <option value="AI" <?php if ($viatura['cia'] == 'AI') echo 'selected'; ?>>AI</option>
                    <option value="IPO" <?php if ($viatura['cia'] == 'IPO') echo 'selected'; ?>>IPO</option>
                    <option value="IAL" <?php if ($viatura['cia'] == 'IAL') echo 'selected'; ?>>IAL</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Atualizar Viatura</button>
            <a href="controle_viaturas.php" class="btn btn-secondary">Voltar</a>
        </form>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
