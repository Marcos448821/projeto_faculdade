<?php
// Iniciar a sessão
session_start();

// Verificar se o GCM ID foi passado
if (!isset($_GET['gcm_id'])) {
    die("ID do GCM não fornecido.");
}

$gcm_id = intval($_GET['gcm_id']);

// Conectar ao banco de dados
$mysqli = new mysqli("localhost", "root", "", "sistema_gcmsp");

if ($mysqli->connect_error) {
    die("Conexão falhou: " . $mysqli->connect_error);
}

// Obter os dados do GCM para edição
$stmt = $mysqli->prepare("SELECT * FROM escala_gcm WHERE id = ?");
$stmt->bind_param("i", $gcm_id);
$stmt->execute();
$result = $stmt->get_result();
$gcm = $result->fetch_assoc();

if (!$gcm) {
    die("GCM não encontrado.");
}

// Obter listas de equipes e modalidades
$equipes_result = $mysqli->query("SELECT id, nome FROM equipes");
$modalidades_result = $mysqli->query("SELECT id, nome FROM modalidades_servicos");

// Verificar se o formulário foi enviado
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $modalidade_id = $_POST['modalidade_id'];
    $equipe_id = $_POST['equipe_id'];
    $horario_inicio = $_POST['horario_inicio'];
    $horario_fim = $_POST['horario_fim'];
    $dias_trabalho = isset($_POST['dias_trabalho']) ? json_decode($_POST['dias_trabalho'], true) : [];
    $dias_trabalho = is_array($dias_trabalho) ? implode('', $dias_trabalho) : '';

    // Atualizar os dados do GCM
    $update_stmt = $mysqli->prepare("
        UPDATE escala_gcm 
        SET modalidade_id = ?, equipe_id = ?, horario_inicio = ?, horario_fim = ?, dias_trabalho = ?
        WHERE id = ?
    ");
    $update_stmt->bind_param("iisssi", $modalidade_id, $equipe_id, $horario_inicio, $horario_fim, $dias_trabalho, $gcm_id);

    if ($update_stmt->execute()) {
        echo "<div class='alert alert-success'>Dados do GCM atualizados com sucesso!</div>";
    } else {
        echo "<div class='alert alert-danger'>Erro ao atualizar os dados: " . $update_stmt->error . "</div>";
    }

    $update_stmt->close();
}

// Fechar a conexão
$mysqli->close();
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar GCM - Sistema GCM</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        .form-control {
            font-size: 0.875rem;
        }
        .day {
            display: inline-block;
            width: 2rem;
            height: 2rem;
            text-align: center;
            line-height: 2rem;
            margin: 0.2rem;
            cursor: pointer;
            background-color: white;
        }
        .day.selected {
            background-color: #007bff;
            color: white;
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <h1>Editar GCM</h1>
        <form method="POST" action="">

            <!-- Selecionar Modalidade de Serviço -->
            <div class="form-group">
                <label for="modalidade_id">Modalidade de Serviço:</label>
                <select name="modalidade_id" id="modalidade_id" class="form-control" required>
                    <?php while ($row = $modalidades_result->fetch_assoc()): ?>
                        <option value="<?php echo htmlspecialchars($row['id']); ?>" 
                            <?php echo ($row['id'] == $gcm['modalidade_id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($row['nome']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <!-- Selecionar Equipe -->
            <div class="form-group">
                <label for="equipe_id">Equipe:</label>
                <select name="equipe_id" id="equipe_id" class="form-control" required>
                    <?php while ($row = $equipes_result->fetch_assoc()): ?>
                        <option value="<?php echo htmlspecialchars($row['id']); ?>" 
                            <?php echo ($row['id'] == $gcm['equipe_id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($row['nome']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <!-- Selecionar Horário de Início -->
            <div class="form-group">
                <label for="horario_inicio">Horário de Início:</label>
                <input type="time" name="horario_inicio" id="horario_inicio" class="form-control" value="<?php echo htmlspecialchars($gcm['horario_inicio']); ?>" required>
            </div>

            <!-- Selecionar Horário de Fim -->
            <div class="form-group">
                <label for="horario_fim">Horário de Fim:</label>
                <input type="time" name="horario_fim" id="horario_fim" class="form-control" value="<?php echo htmlspecialchars($gcm['horario_fim']); ?>" required>
            </div>

            <!-- Selecionar Dias de Trabalho -->
            <div class="form-group">
                <label>Dias de Trabalho:</label><br>
                <?php
                // Obter dias de trabalho do GCM e convertê-los em um array
                $dias_trabalho_array = str_split($gcm['dias_trabalho']);
                for ($i = 1; $i <= 31; $i++): 
                    $selected = (isset($dias_trabalho_array[$i - 1]) && $dias_trabalho_array[$i - 1] === '1') ? 'selected' : '';
                ?>
                    <div class="day <?php echo $selected; ?>" data-day="<?php echo $i; ?>">
                        <?php echo $i; ?>
                    </div>
                <?php endfor; ?>
            </div>

            <input type="hidden" name="dias_trabalho" id="dias_trabalho" value="<?php echo htmlspecialchars($gcm['dias_trabalho']); ?>">

            <button type="submit" class="btn btn-primary">Salvar Alterações</button>
            <a href="escala_mensal.php?escala_id=<?php echo $gcm['escala_id']; ?>" class="btn btn-secondary">Voltar</a>
        </form>
    </div>

    <!-- jQuery e Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>

    <script>
        $(document).ready(function () {
            var diasTrabalho = <?php echo json_encode($dias_trabalho_array); ?>;

            $('.day').on('click', function () {
                var day = $(this).data('day');
                var index = day - 1;

                if ($(this).hasClass('selected')) {
                    // Desmarcar o dia
                    $(this).removeClass('selected');
                    diasTrabalho[index] = '0';
                } else {
                    // Marcar o dia
                    $(this).addClass('selected');
                    diasTrabalho[index] = '1';
                }

                // Atualizar o campo oculto
                $('#dias_trabalho').val(diasTrabalho.join(''));
            });
        });
    </script>
</body>
</html>
