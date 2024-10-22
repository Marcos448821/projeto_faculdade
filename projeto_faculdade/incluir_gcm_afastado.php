<?php
// Iniciar a sessão
session_start();

// Conectar ao banco de dados
$mysqli = new mysqli("localhost", "root", "", "sistema_gcmsp");

if ($mysqli->connect_error) {
    die("Conexão falhou: " . $mysqli->connect_error);
}

// Verificar se escala_id foi fornecido
if (!isset($_GET['escala_id'])) {
    die("Escala ID não fornecido.");
}

$escala_id = intval($_GET['escala_id']); // Garantir que seja um número inteiro

// Consultar lista de GCMs
$gcm_result = $mysqli->query("SELECT id, qra FROM usuarios ORDER BY qra");

if (!$gcm_result) {
    die("Erro ao consultar GCMs: " . $mysqli->error);
}

// Consultar tipos de afastamento
$tipos_afastamento_result = $mysqli->query("SELECT id, nome FROM tipos_afastamentos");

if (!$tipos_afastamento_result) {
    die("Erro ao consultar tipos de afastamento: " . $mysqli->error);
}

// Verificar se o formulário foi enviado
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['incluir_afastamento'])) {
    // Coletar dados do formulário
    $usuario_id = $_POST['usuario_id'];
    $tipo_afastamento_id = $_POST['tipo_afastamento'];
    $data_inicio = $_POST['data_inicio'];
    $data_fim = $_POST['data_fim'];
    $observacoes = $_POST['observacoes'];

    // Validar dados
    if (empty($usuario_id) || empty($tipo_afastamento_id) || empty($data_inicio) || empty($data_fim)) {
        echo "<div class='alert alert-danger' role='alert'>Todos os campos são obrigatórios!</div>";
    } else {
        // Inserir afastamento no banco de dados
        $stmt = $mysqli->prepare("INSERT INTO afastamentos_escala (escala_id, usuario_id, tipo_afastamento_id, data_inicio, data_fim, observacoes) VALUES (?, ?, ?, ?, ?, ?)");

        if (!$stmt) {
            die("Erro ao preparar a declaração de inserção: " . $mysqli->error);
        }

        $stmt->bind_param("iissss", $escala_id, $usuario_id, $tipo_afastamento_id, $data_inicio, $data_fim, $observacoes);

        if ($stmt->execute()) {
            echo "<div class='alert alert-success' role='alert'>Afastamento registrado com sucesso!</div>";
        } else {
            echo "<div class='alert alert-danger' role='alert'>Erro ao registrar afastamento: " . $stmt->error . "</div>";
        }

        $stmt->close();
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
    <title>Incluir GCM Afastado</title>
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
        <h1 class="mb-4">Incluir GCM Afastado</h1>

        <!-- Formulário para Incluir GCM -->
        <form method="POST" action="" oninput="toggleSubmitButton()">
            <div class="form-row">
                <!-- Digitar QRA -->
                <div class="form-group col-md-5">
                    <label for="qra_input">QRA:</label>
                    <input type="text" name="qra_input" id="qra_input" class="form-control form-control-sm" placeholder="Digite o QRA" required>
                    <input type="hidden" name="usuario_id" id="usuario_id">
                    <ul id="qra_suggestions" class="list-group"></ul>
                </div>

                <!-- Tipo de Afastamento -->
                <div class="form-group col-md-4">
                    <label for="tipo_afastamento">Tipo de Afastamento:</label>
                    <select name="tipo_afastamento" id="tipo_afastamento" class="form-control" required>
                        <?php while($row = $tipos_afastamento_result->fetch_assoc()): ?>
                            <option value="<?php echo htmlspecialchars($row['id']); ?>"><?php echo htmlspecialchars($row['nome']); ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <!-- Data de Início -->
                <div class="form-group col-md-4">
                    <label for="data_inicio">Data de Início:</label>
                    <input type="date" name="data_inicio" id="data_inicio" class="form-control" required>
                </div>

                <!-- Data de Fim -->
                <div class="form-group col-md-4">
                    <label for="data_fim">Data de Fim:</label>
                    <input type="date" name="data_fim" id="data_fim" class="form-control" required>
                </div>

                <!-- Observações -->
                <div class="form-group col-md-8">
                    <label for="observacoes">Observações:</label>
                    <textarea name="observacoes" id="observacoes" class="form-control" rows="3"></textarea>
                </div>
            </div>

            <button type="submit" name="incluir_afastamento" class="btn btn-primary">Incluir Afastamento</button>
        </form>

        <a href="escala_mensal.php" class="btn btn-secondary mt-4">Voltar</a>
    </div>

    <!-- Scripts do Bootstrap -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
        // Lógica para busca de QRA com sugestões
    $('#qra_input').on('input', function() {
        var query = this.value;

        if (query.length >= 2) {
            var xhr = new XMLHttpRequest();
            xhr.open('GET', 'buscar_qra.php?q=' + query, true);
            xhr.onload = function() {
                if (this.status == 200) {
                    var response = JSON.parse(this.responseText);
                    var suggestionsContainer = $('#qra_suggestions');
                    suggestionsContainer.empty();

                    response.forEach(function(usuario) {
                        var suggestionItem = $('<li></li>')
                            .addClass('list-group-item')
                            .text(usuario.qra)
                            .data('usuarioId', usuario.id)
                            .on('click', function() {
                                $('#qra_input').val(usuario.qra);
                                $('#usuario_id').val(usuario.id);
                                suggestionsContainer.empty();
                            });
                        suggestionsContainer.append(suggestionItem);
                    });
                }
            };
            xhr.send();
        }
    });
    </script>
</body>
</html>
