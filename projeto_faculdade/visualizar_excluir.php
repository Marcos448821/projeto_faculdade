<?php
// Conectar ao banco de dados
$mysqli = new mysqli("localhost", "root", "", "sistema_gcmsp");

if ($mysqli->connect_error) {
    die("Conexão falhou: " . $mysqli->connect_error);
}

// Verificar se o ID da escala foi passado
if (!isset($_GET['escala_id'])) {
    die("ID da escala não fornecido.");
}

$escala_id = intval($_GET['escala_id']);

// Consultar os GCMs já incluídos na escala
$gcm_result = $mysqli->query("
    SELECT escala_gcm.id, usuarios.qra, equipes.nome AS equipe, modalidades_servicos.nome AS modalidade, escala_gcm.horario_inicio, escala_gcm.horario_fim
    FROM escala_gcm
    JOIN usuarios ON escala_gcm.usuario_id = usuarios.id
    JOIN modalidades_servicos ON escala_gcm.modalidade_id = modalidades_servicos.id
    JOIN equipes ON escala_gcm.equipe_id = equipes.id
    WHERE escala_gcm.escala_id = $escala_id
");

// Mostrar mensagens de sucesso ou erro
session_start();
if (isset($_SESSION['success_message'])) {
    echo "<div class='alert alert-success'>" . $_SESSION['success_message'] . "</div>";
    unset($_SESSION['success_message']);
} elseif (isset($_SESSION['error_message'])) {
    echo "<div class='alert alert-danger'>" . $_SESSION['error_message'] . "</div>";
    unset($_SESSION['error_message']);
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Escala Mensal</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
    <div class="container mt-5">
        <h1 class="mb-4">Escala Mensal - GCMs Incluídos</h1>

        <?php if ($gcm_result->num_rows > 0): ?>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>QRA</th>
                        <th>Equipe</th>
                        <th>Modalidade</th>
                        <th>Horário</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($gcm = $gcm_result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($gcm['qra']); ?></td>
                            <td><?php echo htmlspecialchars($gcm['equipe']); ?></td>
                            <td><?php echo htmlspecialchars($gcm['modalidade']); ?></td>
                            <td><?php echo htmlspecialchars($gcm['horario_inicio']) . ' - ' . htmlspecialchars($gcm['horario_fim']); ?></td>
                            <td>
                                <!-- Link para excluir -->
                                <a href="excluir_gcm.php?id=<?php echo $gcm['id']; ?>&escala_id=<?php echo $escala_id; ?>" class="btn btn-danger btn-sm">Excluir</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>Nenhum GCM foi incluído nesta escala ainda.</p>
        <?php endif; ?>
    </div>
</body>
</html>

<?php
// Fechar a conexão
$mysqli->close();
?>
