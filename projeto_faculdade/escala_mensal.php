<?php
// Iniciar a sessão
session_start();

// Conectar ao banco de dados
$mysqli = new mysqli("localhost", "root", "", "sistema_gcmsp");

if ($mysqli->connect_error) {
    die("Conexão falhou: " . $mysqli->connect_error);
}

// Consultar inspetorias
$inspetorias_result = $mysqli->query("SELECT id, nome FROM inspetorias");

if (!$inspetorias_result) {
    die("Erro ao consultar inspetorias: " . $mysqli->error);
}

// Consultar escalas existentes
$escalas_query = "SELECT escalas.id, inspetorias.nome AS inspetoria, escalas.mes, escalas.ano, escalas.created_at, usuarios.nome AS usuario
                  FROM escalas
                  JOIN inspetorias ON escalas.inspetoria_id = inspetorias.id
                  JOIN usuarios ON escalas.usuario_id = usuarios.id
                  ORDER BY escalas.ano DESC, FIELD(escalas.mes, 'Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho', 'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro') DESC";

$escalas_result = $mysqli->query($escalas_query);

if (!$escalas_result) {
    die("Erro ao consultar escalas: " . $mysqli->error);
}

// Verificar se o formulário foi enviado
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['create_escala'])) {
    // Coletar dados do formulário
    $inspetoria_id = $_POST['inspetoria'];
    $mes = ucfirst(strtolower(trim($_POST['mes']))); // Capitalizar o mês corretamente
    $ano = $_POST['ano'];

    // Obter o ID do usuário da sessão
    $usuario_id = isset($_SESSION['usuario_id']) ? $_SESSION['usuario_id'] : null;

    if ($usuario_id === null) {
        echo "<div class='alert alert-danger' role='alert'>Usuário não autenticado.</div>";
    } else {
        // Validar dados
        if (empty($inspetoria_id) || empty($mes) || empty($ano)) {
            echo "<div class='alert alert-danger' role='alert'>Todos os campos são obrigatórios!</div>";
        } else {
            // Verificar se a escala já foi criada
            $check_stmt = $mysqli->prepare("SELECT * FROM escalas WHERE inspetoria_id = ? AND mes = ? AND ano = ?");
            
            if (!$check_stmt) {
                die("Erro ao preparar a declaração de verificação: " . $mysqli->error);
            }
            
            $check_stmt->bind_param("iss", $inspetoria_id, $mes, $ano);
            $check_stmt->execute();
            $result = $check_stmt->get_result();

            if ($result->num_rows > 0) {
                echo "<div class='alert alert-info' role='alert'>Escala já criada para esta Inspetoria, Mês e Ano.</div>";
            } else {
                // Inserir nova escala no banco de dados
                $stmt = $mysqli->prepare("INSERT INTO escalas (inspetoria_id, mes, ano, usuario_id, created_at) VALUES (?, ?, ?, ?, NOW())");
                
                if (!$stmt) {
                    die("Erro ao preparar a declaração de inserção: " . $mysqli->error);
                }

                $stmt->bind_param("isis", $inspetoria_id, $mes, $ano, $usuario_id);

                if ($stmt->execute()) {
                    // Redirecionar para a mesma página para atualizar a lista de escalas
                    header("Location: " . $_SERVER['PHP_SELF']);
                    exit();
                } else {
                    echo "<div class='alert alert-danger' role='alert'>Erro ao criar escala: " . $stmt->error . "</div>";
                }

                $stmt->close();
            }

            $check_stmt->close();
        }
    }
}

// Fechar a conexão
$mysqli->close();
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Criar Escala Mensal</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<style>
        body {
            font-family: Arial, sans-serif;
            background-image: url('uploads/digital_0.jpg');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            
        }
</style>
<body>
    <div class="container mt-5">
        <h1 class="mb-4">Criar Escala Mensal</h1>

        <!-- Formulário para Criar Nova Escala -->
        <form method="POST" action="">
            <div class="form-row">
                <!-- Selecionar Inspetoria -->
                <div class="form-group col-md-3">
                    <h2><label for="inspetoria">Inspetoria:</label></h2>
                    <select name="inspetoria" id="inspetoria" class="form-control" required>
                        <?php while($row = $inspetorias_result->fetch_assoc()): ?>
                            <option value="<?php echo htmlspecialchars($row['id']); ?>"><?php echo htmlspecialchars($row['nome']); ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <!-- Selecionar Mês -->
                <div class="form-group col-md-3">
                    <h2><label for="mes">Mês:</label></h2>
                    <input type="text" name="mes" id="mes" class="form-control" placeholder="Digite o mês" required>
                </div>

                <!-- Selecionar Ano -->
                <div class="form-group col-md-3">
                    <h2><label for="ano">Ano:</label></h2>
                    <input type="nunber" name="ano" id="ano" class="form-control" placeholder="Digite o Ano" required>
                </div>

            </div>

            <button type="submit" name="create_escala" class="btn btn-primary">Criar Escala</button>
        </form>
        
        <!-- Tabela de Escalas Criadas -->
        <h2 class="mt-5">Escalas Criadas</h2>
        <table class="table table-bordered mt-3">
            <thead>
                <tr>
                    <th>Número</th>
                    <th>Inspetoria</th>
                    <th>Mês</th>
                    <th>Ano</th>
                    <th>Data e hora da Criação</th>                    
                    <th><center>Ações</center></th>
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
                            <center><a href="incluir_gcm.php?escala_id=<?php echo $escala['id']; ?>" class="btn btn-success">Incluir</a>                            
                            <a href="incluir_gcm_afastado.php?escala_id=<?php echo $escala['id']; ?>" class="btn btn-warning">Afastados</a>
                            <a href="visualizar_escala.php?escala_id=<?php echo $escala['id']; ?>" class="btn btn-info">Visualizar</a></center>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7">Nenhuma escala criada ainda.</td>
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
