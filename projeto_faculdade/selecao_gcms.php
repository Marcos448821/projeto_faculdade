<?php
// Conectar ao banco de dados
$mysqli = new mysqli("localhost", "root", "", "sistema_gcmsp");

if ($mysqli->connect_error) {
    die("Conexão falhou: " . $mysqli->connect_error);
}

// Obter todas as Inspetorias e equipes
$inspetorias = $mysqli->query("SELECT * FROM inspetorias");
$equipes = ['A' => 'Equipe A', 'B' => 'Equipe B', 'C' => 'Equipe C', 'D' => 'Equipe D', 'ADM' => 'Equipe ADM'];

// Processar o envio do formulário
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $inspetoria_id = intval($_POST['inspetoria_id']);
    $data = $_POST['data'];
    $equipe_selecionada = $_POST['equipe'];

    // Obter GCMs da Inspetoria selecionada para a data e equipe selecionada
    $gcm_query = "
        SELECT e.usuario_id, u.qra, e.horario, e.modalidade
        FROM escalas e
        JOIN usuarios u ON e.usuario_id = u.id
        WHERE e.inspetoria_id = $inspetoria_id AND e.data = '$data' AND e.equipe = '$equipe_selecionada'
    ";
    $gcm_result = $mysqli->query($gcm_query);
}

// Obter a Inspetoria e equipe selecionadas, se disponíveis
$selected_inspetoria_id = isset($_POST['inspetoria_id']) ? intval($_POST['inspetoria_id']) : null;
$selected_equipe = isset($_POST['equipe']) ? htmlspecialchars($_POST['equipe']) : null;
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Seleção de GCMs - Sistema GCMSP</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        .form-check-item {
            display: flex;
            align-items: center;
            border: 1px solid #ccc;
            border-radius: 5px;
            padding: 5px;
            margin-bottom: 10px;
            background-color: #f9f9f9;
        }
        
        .form-check-item .form-check-label {
            margin-left: 10px;
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <h1 class="text-center">Seleção de GCMs</h1>

        <!-- Formulário para selecionar Inspetoria, Data e Equipe -->
        <form action="selecao_gcms.php" method="POST">
            <div class="form-group">
                <label for="inspetoria_id">Selecione a Inspetoria:</label>
                <select id="inspetoria_id" name="inspetoria_id" class="form-control" required>
                    <option value="">Selecione uma Inspetoria</option>
                    <?php while ($inspetoria = $inspetorias->fetch_assoc()): ?>
                        <option value="<?php echo htmlspecialchars($inspetoria['id']); ?>"
                            <?php echo $selected_inspetoria_id == $inspetoria['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($inspetoria['nome']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="data">Selecione a Data:</label>
                <input type="date" id="data" name="data" class="form-control" value="<?php echo isset($_POST['data']) ? htmlspecialchars($_POST['data']) : date('Y-m-d'); ?>" required>
            </div>
            <div class="form-group">
                <label for="equipe">Selecione a Equipe:</label>
                <select id="equipe" name="equipe" class="form-control" required>
                    <option value="">Selecione uma Equipe</option>
                    <?php foreach ($equipes as $equipe => $descricao): ?>
                        <option value="<?php echo htmlspecialchars($equipe); ?>"
                            <?php echo $selected_equipe == $equipe ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($descricao); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" class="btn btn-primary mt-3">Buscar GCMs</button>
        </form>

        <!-- Listagem dos GCMs da Inspetoria, Data e Equipe Selecionadas -->
        <?php if (isset($gcm_result)): ?>
            <h3 class="mt-5">GCMs na Escala - Equipe <?php echo htmlspecialchars($selected_equipe); ?></h3>
            <form action="processar_presenca.php" method="POST">
                <input type="hidden" name="data" value="<?php echo htmlspecialchars($_POST['data']); ?>">
                <input type="hidden" name="inspetoria_id" value="<?php echo htmlspecialchars($selected_inspetoria_id); ?>">
                <input type="hidden" name="equipe" value="<?php echo htmlspecialchars($selected_equipe); ?>">
                <?php while ($gcm = $gcm_result->fetch_assoc()): ?>
                    <div class="form-check-item">
                        <label class="form-check-label">
                            <input type="checkbox" name="presenca[]" value="<?php echo htmlspecialchars($gcm['usuario_id']); ?>">
                            QRA: <?php echo htmlspecialchars($gcm['qra']); ?> - Horário: <?php echo htmlspecialchars($gcm['horario']); ?> - Modalidade: <?php echo htmlspecialchars($gcm['modalidade']); ?>
                        </label>
                    </div>
                <?php endwhile; ?>
                <button type="submit" class="btn btn-success mt-3">Confirmar Presença</button>
            </form>
        <?php endif; ?>
    </div>

    <!-- Scripts do Bootstrap -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
