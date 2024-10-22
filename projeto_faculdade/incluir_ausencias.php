<?php
session_start();

// Verificar se o usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}

// Verificar se o mapa_forca_id está definido na sessão
if (isset($_SESSION['mapa_forca_id'])) {
    $mapa_forca_id = $_SESSION['mapa_forca_id'];
} else {
    // Mensagem de erro e redirecionamento caso o mapa_forca_id não esteja definido
    echo "<div class='alert alert-danger'>Erro: Mapa Força não foi selecionado. Volte e selecione o Mapa Força.</div>";
    exit();
}

// Conectar ao banco de dados
$mysqli = new mysqli("localhost", "root", "", "sistema_gcmsp");

if ($mysqli->connect_error) {
    die("Conexão falhou: " . $mysqli->connect_error);
}

// Verificar se o formulário foi enviado
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_ausencia'])) {
    $usuario_id = $_POST['usuario_id'];
    $tipo_afastamento = $_POST['tipo_afastamento'];
    $data_inicio = $_POST['data_inicio'];
    $data_fim = $_POST['data_fim'];
    $observacoes = $_POST['observacoes'];

    if (empty($usuario_id) || empty($tipo_afastamento) || empty($data_inicio) || empty($data_fim)) {
        echo "<div class='alert alert-danger' role='alert'>Todos os campos são obrigatórios!</div>";
    } else {
        // Inserir a ausência
        $stmt = $mysqli->prepare("INSERT INTO afastamentos (mapa_forca_id, usuario_id, tipo_afastamento, data_inicio, data_fim, observacoes) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("iissss", $mapa_forca_id, $usuario_id, $tipo_afastamento, $data_inicio, $data_fim, $observacoes);

        if ($stmt->execute()) {
            echo "<div class='alert alert-success' role='alert'>Ausência registrada com sucesso!</div>";
        } else {
            echo "<div class='alert alert-danger' role='alert'>Erro ao registrar ausência: " . $stmt->error . "</div>";
        }

        $stmt->close();
    }
}




$mysqli->close();
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
<style>
         body {
            font-family: Arial, sans-serif;
            background-image: url('uploads/digital_0.jpg');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            
        }
       
    </style>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar Ausências</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</head>
<body>
    <div class="container mt-5">
        <h1 class="mb-4">Registrar Ausências</h1>

        <!-- Formulário para Registrar Ausências -->
        <form method="POST" action="">
            <div class="form-row">
                <!-- Selecionar Usuário com sugestão de QRA -->
                <div class="form-group col-md-4">
                    <label for="usuario">GCM:</label>
                    <input type="text" name="qra" id="qra_input" class="form-control" placeholder="Digite o QRA" required>
                    <input type="hidden" name="usuario_id" id="usuario_id">
                    <ul id="qra_suggestions" class="list-group"></ul>
                </div>

                <!-- Tipo de Afastamento -->
                <div class="form-group col-md-6">
                    <label for="tipo_afastamento">Tipo de Afastamento:</label>
                    <select name="tipo_afastamento" id="tipo_afastamento" class="form-control" required>
                        <option value="Férias">Férias</option>
                        <option value="Licença Prêmio">Licença Prêmio</option>
                        <option value="Afastamento Médico">Afastamento Médico</option>
                        <option value="Outros">Outros</option>
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
                <div class="form-group col-md-12">
                    <label for="observacoes">Observações:</label>
                    <textarea name="observacoes" id="observacoes" class="form-control"></textarea>
                </div>
            </div>

            <button type="submit" name="add_ausencia" class="btn btn-primary">Adicionar Ausência</button>
        </form>

        <a href="criar_mapa_forca.php" class="btn btn-secondary mt-4">Voltar</a>
    </div>

    <script>
    // Lógica para busca de QRA com sugestões
    $('#qra_input').on('input', function() {
        var query = this.value;

        if (query.length >= 2) {
            var xhr = new XMLHttpRequest();
            xhr.open('GET', 'buscar_qra.php?q=' + encodeURIComponent(query), true);
            xhr.onload = function() {
                if (this.status == 200) {
                    var response = JSON.parse(this.responseText);
                    var suggestionsContainer = $('#qra_suggestions');
                    suggestionsContainer.empty(); // Limpa as sugestões anteriores

                    if (response.length > 0) {
                        response.forEach(function(usuario) {
                            var suggestionItem = $('<li></li>')
                                .addClass('list-group-item')
                                .text(usuario.qra)
                                .data('usuarioId', usuario.id)
                                .on('click', function() {
                                    $('#qra_input').val(usuario.qra);
                                    $('#usuario_id').val(usuario.id); // Define o ID do usuário
                                    suggestionsContainer.empty(); // Limpa as sugestões após a seleção
                                });
                            suggestionsContainer.append(suggestionItem); // Adiciona o item à lista
                        });
                    } else {
                        suggestionsContainer.append('<li class="list-group-item">Nenhum resultado encontrado</li>');
                    }
                }
            };
            xhr.send();
        } else {
            $('#qra_suggestions').empty(); // Limpar sugestões quando a busca for muito curta
        }
    });
    </script>
</body>
</html>
