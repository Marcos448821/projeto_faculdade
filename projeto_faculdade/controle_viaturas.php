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

// Obtém a lista de viaturas
$query = "SELECT * FROM viaturas";
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Controle de Viaturas - Sistema GMCSP</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
    <div class="container mt-5">
        <h2>Controle de Viaturas</h2>
        <a href="adicionar_viatura.php" class="btn btn-primary mb-3">Adicionar Nova Viatura</a>
        <a href="dashboard.php" class="btn btn-secondary mb-3">Voltar</a>
        <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Prefixo</th>
                    <th>Placa</th>
                    <th>Modelo</th>
                    <th>Ano</th>
                    <th>KM Atual</th>
                    <th>Companhia (CIA)</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($viatura = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($viatura['id']); ?></td>
                    <td><?php echo htmlspecialchars($viatura['prefixo']); ?></td>
                    <td><?php echo htmlspecialchars($viatura['placa']); ?></td>
                    <td><?php echo htmlspecialchars($viatura['modelo']); ?></td>
                    <td><?php echo htmlspecialchars($viatura['ano']); ?></td>
                    <td><?php echo htmlspecialchars($viatura['km_atual']); ?></td>
                    <td><?php echo htmlspecialchars($viatura['cia']); ?></td>
                    <td>
                        <a href="editar_viatura.php?id=<?php echo $viatura['id']; ?>" class="btn btn-warning btn-sm">Editar</a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
        <a href="dashboard.php" class="btn btn-secondary">Voltar</a>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
