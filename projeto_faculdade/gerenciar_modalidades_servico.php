<?php
// Conectar ao banco de dados
$mysqli = new mysqli("localhost", "root", "", "sistema_gcmsp");

if ($mysqli->connect_error) {
    die("Conexão falhou: " . $mysqli->connect_error);
}

// Obter todas as modalidades de serviço
$modalidades_servico_result = $mysqli->query("SELECT * FROM modalidades_servicos");

// Verificar se a consulta retornou algum erro
if (!$modalidades_servico_result) {
    die("Erro na consulta: " . $mysqli->error);
}

// Verificar se o ID de exclusão foi passado
if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];

    // Excluir a modalidade de serviço
    $stmt = $mysqli->prepare("DELETE FROM modalidades_servicos WHERE id = ?");
    $stmt->bind_param('i', $delete_id);
    if ($stmt->execute()) {
        header('Location: gerenciar_modalidades_servico.php');
        exit;
    } else {
        echo "<div class='alert alert-danger'>Erro ao excluir modalidade de serviço: " . $mysqli->error . "</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar Modalidades de Serviço</title>
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
        <h1>Gerenciar Modalidades de Serviço</h1>
        <!-- Botão para abrir o modal de inclusão de modalidade de serviço -->
        <button type="button" class="btn btn-primary mt-3" data-toggle="modal" data-target="#incluirModalidadeServicoModal">Incluir Nova Modalidade de Serviço</button>
        <a href="dashboard.php" class="btn btn-secondary mt-3">Voltar</a>
        
               

        <!-- Tabela de Modalidades de Serviço Existentes -->
        <h2 class="mt-5">Modalidades de Serviço Existentes</h2>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nome</th>
                    <th>Descrição</th>
                    <th>Status</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($modalidades_servico_result->num_rows > 0): ?>
                    <?php while ($modalidade_servico = $modalidades_servico_result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($modalidade_servico['id']); ?></td>
                            <td><?php echo htmlspecialchars($modalidade_servico['nome']); ?></td>
                            <td><?php echo htmlspecialchars($modalidade_servico['descricao']); ?></td>
                            <td><?php echo $modalidade_servico['ativo'] ? 'Ativo' : 'Inativo'; ?></td>
                            <td>
                                <a href="editar_modalidade.php?id=<?php echo $modalidade_servico['id']; ?>" class="btn btn-warning">Alterar</a>
                                <a href="gerenciar_modalidades_servico.php?delete_id=<?php echo $modalidade_servico['id']; ?>" class="btn btn-danger">Excluir</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5">Nenhuma modalidade de serviço encontrada.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

        

        <!-- Modal para inclusão de nova modalidade de serviço -->
        <div class="modal fade" id="incluirModalidadeServicoModal" tabindex="-1" role="dialog" aria-labelledby="incluirModalidadeServicoModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="incluirModalidadeServicoModalLabel">Incluir Nova Modalidade de Serviço</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Fechar">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form action="incluir_modalidade_servico.php" method="POST">
                            <div class="form-group">
                                <label for="nome">Nome:</label>
                                <input type="text" id="nome" name="nome" class="form-control" required>
                            </div>

                            <div class="form-group">
                                <label for="descricao">Descrição:</label>
                                <textarea id="descricao" name="descricao" class="form-control"></textarea>
                            </div>

                            <div class="form-group">
                                <label for="ativo">Ativo:</label>
                                <select id="ativo" name="ativo" class="form-control">
                                    <option value="1">Sim</option>
                                    <option value="0">Não</option>
                                </select>
                            </div>
                            <button type="submit" class="btn btn-primary">Incluir Modalidade de Serviço</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <center><a href="dashboard.php" class="btn btn-secondary mt-4">Voltar</a></center>
    </div>

    <!-- Scripts do Bootstrap -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
