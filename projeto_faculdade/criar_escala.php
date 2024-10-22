<?php
// Conectar ao banco de dados
$mysqli = new mysqli("localhost", "root", "", "sistema_gcmsp");

if ($mysqli->connect_error) {
    die("Conexão falhou: " . $mysqli->connect_error);
}

// Consultar inspetorias
$inspetorias_result = $mysqli->query("SELECT nome FROM inspetorias");

// Consultar escalas existentes
$escalas_result = $mysqli->query("SELECT * FROM escalas ORDER BY ano DESC, FIELD(mes, 'Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho', 'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro') DESC");

// Se o formulário for enviado
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['create_escala'])) {
    $inspetoria = $_POST['inspetoria'];
    $mes = $_POST['mes'];
    $ano = $_POST['ano'];

    // Validar e formatar mês
    $mes = ucfirst(strtolower(trim($mes))); // Capitalizar o mês corretamente

    // Inserir nova escala no banco de dados
    $stmt = $mysqli->prepare("INSERT INTO escalas (inspetoria, mes, ano, created_at) VALUES (?, ?, ?, NOW())");
    $stmt->bind_param("sss", $inspetoria, $mes, $ano);

    if ($stmt->execute()) {
        echo "<div class='alert alert-success' role='alert'>Escala criada com sucesso!</div>";
    } else {
        echo "<div class='alert alert-danger' role='alert'>Erro ao criar escala: " . $stmt->error . "</div>";
    }

    // Fechar a conexão
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Criar Escala Mensal</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
    <div class="container mt-5">
        <h1 class="mb-4">Criar Escala Mensal</h1>

        <!-- Formulário para Criar Nova Escala -->
        <form method="POST" action="">
            <div class="form-row">
                <!-- Selecionar Inspetoria -->
                <div class="form-group col-md-6">
                    <label for="inspetoria">Inspetoria:</label>
                    <select name="inspetoria" id="inspetoria" class="form-control" required>
                        <?php while($row = $inspetorias_result->fetch_assoc()): ?>
                            <option value="<?php echo htmlspecialchars($row['nome']); ?>"><?php echo htmlspecialchars($row['nome']); ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <!-- Selecionar Mês -->
                <div class="form-group col-md-3">
                    <label for="mes">Mês:</label>
                    <input type="text" name="mes" id="mes" class="form-control" placeholder="Exemplo: Janeiro" required>
                </div>

                <!-- Selecionar Ano -->
                <div class="form-group col-md-3">
                    <label for="ano">Ano:</label>
                    <input type="number" name="ano" id="ano" class="form-control" min="2023" required>
                </div>
            </div>

            <button type="submit" name="create_escala" class="btn btn-primary">Criar Escala</button>
        </form>
        
        <!-- Tabela de Escalas Criadas -->
        <h2 class="mt-5">Escalas Criadas</h2>
        <table class="table table-bordered mt-3">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Inspetoria</th>
                    <th>Mês</th>
                    <th>Ano</th>
                    <th>Data de Criação</th>
                    <th>Ação</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($escalas_result->num_rows > 0): ?>
                    <?php while ($escala = $escalas_result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($escala['id']); ?></td>
                            <td><?php echo htmlspecialchars($escala['inspetoria']); ?></td>
                            <td><?php echo htmlspecialchars($escala['mes']); ?></td>
                            <td><?php echo htmlspecialchars($escala['ano']); ?></td>
                            <td><?php echo htmlspecialchars($escala['created_at']); ?></td>
                            <td>
                                <a href="incluir_gcm.php?escala_id=<?php echo $escala['id']; ?>" class="btn btn-success">Incluir GCMs</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6">Nenhuma escala criada ainda.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <a href="dashboard.php" class="btn btn-secondary mt-4">Voltar</a>
    </div>

    <!-- Scripts do Bootstrap -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
