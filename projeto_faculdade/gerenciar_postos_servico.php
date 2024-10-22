<?php
// Conectar ao banco de dados
$mysqli = new mysqli("localhost", "root", "", "sistema_gcmsp");

if ($mysqli->connect_error) {
    die("Conexão falhou: " . $mysqli->connect_error);
}

// Obter todos os postos de serviço
$postos_servico_result = $mysqli->query("SELECT * FROM postos_servicos");

// Verificar se a consulta retornou algum erro
if (!$postos_servico_result) {
    die("Erro na consulta: " . $mysqli->error);
}

// Verificar se o ID de exclusão foi passado
if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];

    // Excluir o posto de serviço
    $stmt = $mysqli->prepare("DELETE FROM postos_servicos WHERE id = ?");
    $stmt->bind_param('i', $delete_id);
    if ($stmt->execute()) {
        header('Location: gerenciar_postos_servico.php');
        exit;
    } else {
        echo "<div class='alert alert-danger'>Erro ao excluir posto de serviço: " . $mysqli->error . "</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar Postos de Serviço</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-image: url('uploads/digital_0.jpg');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            
        }
</style>
</head>
<body>
    <div class="container mt-5">
        <h1>Gerenciar Postos de Serviço</h1>
        <!-- Botão para abrir o modal de inclusão de posto de serviço -->
        <button type="button" class="btn btn-primary mb-3" data-toggle="modal" data-target="#incluirPostoServicoModal">Incluir Novo Posto de Serviço</button>        
        <a href="dashboard.php" class="btn btn-secondary mb-3">Voltar</a>
        <!-- Tabela de Postos de Serviço Existentes -->
        <h2 class="mt-3">Postos de Serviço Existentes</h2>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nome</th>
                    <th>Descrição</th>
                    <th>Localização</th>
                    <th>Status</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($postos_servico_result->num_rows > 0): ?>
                    <?php while ($posto_servico = $postos_servico_result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($posto_servico['id']); ?></td>
                            <td><?php echo htmlspecialchars($posto_servico['nome']); ?></td>
                            <td><?php echo htmlspecialchars($posto_servico['descricao']); ?></td>
                            <td><?php echo htmlspecialchars($posto_servico['localizacao']); ?></td>
                            <td><?php echo $posto_servico['ativo'] ? 'Ativo' : 'Inativo'; ?></td>
                            <td>
                                <a href="alterar_posto_servico.php?id=<?php echo $posto_servico['id']; ?>" class="btn btn-warning">Alterar</a>
                                <a href="gerenciar_postos_servico.php?delete_id=<?php echo $posto_servico['id']; ?>" class="btn btn-danger">Excluir</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6">Nenhum posto de serviço encontrado.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

        
        <!-- Modal para inclusão de novo posto de serviço -->
        <div class="modal fade" id="incluirPostoServicoModal" tabindex="-1" role="dialog" aria-labelledby="incluirPostoServicoModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="incluirPostoServicoModalLabel">Incluir Novo Posto de Serviço</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Fechar">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
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
                                <select id="ativo" name="ativo" class="form-control">
                                    <option value="1">Sim</option>
                                    <option value="0">Não</option>
                                </select>
                            </div>

                            
                        </form>
                    </div>
                </div>
            </div>
        </div>

        
    </div>

    <!-- Scripts do Bootstrap -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
