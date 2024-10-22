<?php
// Conectar ao banco de dados
$mysqli = new mysqli("localhost", "root", "", "sistema_gcmsp");

if ($mysqli->connect_error) {
    die("Conexão falhou: " . $mysqli->connect_error);
}

// Desativar a exibição de erros
error_reporting(0); // Desativa todos os tipos de erros
ini_set('display_errors', 0); // Não exibe erros na página

// Verificar se o ID da escala foi passado
if (!isset($_GET['escala_id'])) {
    die("ID da escala não fornecido.");
}

$escala_id = intval($_GET['escala_id']);

// Consultar dados para a escala
$escala_stmt = $mysqli->prepare("SELECT * FROM escalas WHERE id = ?");
if (!$escala_stmt) {
    die("Erro na preparação da consulta de escala: " . $mysqli->error);
}

$escala_stmt->bind_param("i", $escala_id);
$escala_stmt->execute();
$escala_result = $escala_stmt->get_result();

if ($escala_result->num_rows > 0) {
    $escala = $escala_result->fetch_assoc();
    $inspetoria_id = $escala['inspetoria_id'];
    $mes_str = $escala['mes']; // Nome do mês
    $ano = $escala['ano'];
} else {
    die("Escala não encontrada.");
}

// Converter o nome do mês para número
$meses = [
    'Janeiro' => 1,
    'Fevereiro' => 2,
    'Março' => 3,
    'Abril' => 4,
    'Maio' => 5,
    'Junho' => 6,
    'Julho' => 7,
    'Agosto' => 8,
    'Setembro' => 9,
    'Outubro' => 10,
    'Novembro' => 11,
    'Dezembro' => 12,
];

$mes = isset($meses[$mes_str]) ? $meses[$mes_str] : 1; // Default para Janeiro se o mês não for encontrado

// Consultar dados de usuários, modalidades e equipes
$usuarios_result = $mysqli->query("SELECT id, qra, RE FROM usuarios");
$modalidades_result = $mysqli->query("SELECT id, nome FROM modalidades_servicos");
$equipes_result = $mysqli->query("SELECT id, nome FROM equipes");

// Função para determinar a equipe com base no dia
function determinarEquipe($dia, $mes, $ano, $mysqli) {
    // Verifica se é um dia útil
    try {
        $data = new DateTime("$ano-$mes-$dia");
    } catch (Exception $e) {
        // Caso o formato da data seja inválido
        die("Erro ao criar a data: " . $e->getMessage());
    }

    $dataStr = $data->format('Y-m-d'); // Formato YYYY-MM-DD

    // Consultar feriados
    $feriado_stmt = $mysqli->prepare("SELECT COUNT(*) FROM feriados WHERE data = ?");
    $feriado_stmt->bind_param("s", $dataStr);
    $feriado_stmt->execute();
    $feriado_result = $feriado_stmt->get_result();
    $isFeriado = $feriado_result->fetch_row()[0] > 0;

    if ($isFeriado) {
        return 'ADM';
    } else if ($dia % 2 == 0) {
        return 'A e B'; // Dias pares
    } else {
        return 'C e D'; // Dias ímpares
    }
}
// Consultar o mês, o ano e a inspetoria da tabela 'escalas'
$escala_query = "SELECT mes, ano, inspetoria_id FROM escalas WHERE id = ?";
$stmt = $mysqli->prepare($escala_query);
if (!$stmt) {
    die("Erro na preparação da consulta: " . $mysqli->error);
}
$stmt->bind_param("i", $escala_id);
$stmt->execute();
$escala_result = $stmt->get_result();

if ($escala_result->num_rows === 0) {
    die("Escala não encontrada.");
}

$escala = $escala_result->fetch_assoc();
$ano_atual = $escala['ano'];
$mes_atual_texto = $escala['mes'];
$inspetoria_id = $escala['inspetoria_id'];

$days = [];
for ($i = 1; $i <= 31; $i++) {
    // Corrigir se o dia é válido para o mês
    $data = new DateTime("$ano-$mes-$i");
    if ($data->format('n') == $mes) {
        $equipe = determinarEquipe($i, $mes, $ano, $mysqli);
        $days[] = ['day' => $i, 'equipe' => $equipe];
    }
}


    // Verificar se o formulário foi enviado
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $usuario_id = $_POST['usuario_id'];
    $modalidade_id = $_POST['modalidade_id'];
    $equipe_id = $_POST['equipe_id'];
    $horario_inicio = $_POST['horario_inicio'];
    $horario_fim = $_POST['horario_fim'];
    $dias_trabalho = isset($_POST['dias_trabalho']) ? json_decode($_POST['dias_trabalho'], true) : [];
    $dias_trabalho = is_array($dias_trabalho) ? implode('', $dias_trabalho) : '';

    // Verificar se o GCM já está na escala com os mesmos parâmetros
    $verificar_stmt = $mysqli->prepare("SELECT * FROM escala_gcm WHERE usuario_id = ? AND escala_id = ? AND modalidade_id = ? AND equipe_id = ? AND horario_inicio = ? AND horario_fim = ?");
    $verificar_stmt->bind_param("iiisss", $usuario_id, $escala_id, $modalidade_id, $equipe_id, $horario_inicio, $horario_fim);
    $verificar_stmt->execute();
    $verificar_result = $verificar_stmt->get_result();

    if ($verificar_result->num_rows > 0) {
        // Se já existir, não permite a duplicação
        echo "<div class='alert alert-warning' role='alert'>Este GCM já foi incluído na escala com os mesmos parâmetros.</div>";
    } else {
        // Inserir GCM na escala
        $stmt = $mysqli->prepare("INSERT INTO escala_gcm (escala_id, usuario_id, modalidade_id, equipe_id, horario_inicio, horario_fim, dias_trabalho, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())");
        if (!$stmt) {
            die("Erro na preparação da consulta de inserção: " . $mysqli->error);
        }
        $stmt->bind_param("iiissss", $escala_id, $usuario_id, $modalidade_id, $equipe_id, $horario_inicio, $horario_fim, $dias_trabalho);

        if ($stmt->execute()) {
            echo "<div class='alert alert-success' role='alert'>GCM incluído com sucesso na Escala!</div>";
        
        }

        $stmt->close();
    }


        $verificar_stmt->close();
}

// Fechar a conexão
$mysqli->close();
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Incluir GCM na Escala</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-image: url('uploads/digital_0.jpg');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            
        }
        .form-control {
            font-size: 0.875rem;
        }
        .form-group {
            margin-bottom: 1rem;
        }
        .form-control-sm {
            font-size: 1.25rem;
        }
        .btn {
            font-size: 0.875rem;
        }
        #qra_suggestions {
            position: absolute;
            z-index: 1000;
            width: 100%;
            background-color: white;
            border: 1px solid #ced4da;
            max-height: 150px;
            overflow-y: auto;
        }
        .day {
            display: inline-block;
            width: 2rem;
            height: 2rem;
            text-align: center;
            line-height: 2rem;
            margin: 0.2rem;
            cursor: pointer;
            background-color: white; /* Fundo branco por padrão */
        }
        .day.equipe-azul {
            background-color: #007bff;
            color: white;
        }
        .day.selected {
            background-color: #007bff;
            color: white;
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        
        <h1 class="mb-4">Incluir GCM na Escala</h1>
        
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

                <!-- Selecionar Modalidade de Serviço -->
                <div class="form-group col-md-5">
                    <label for="modalidade_id">Modalidade de Serviço:</label>
                    <select name="modalidade_id" id="modalidade_id" class="form-control form-control-sm" required>
                        <option value="">Selecione uma modalidade</option>
                        <?php while($row = $modalidades_result->fetch_assoc()): ?>
                            <option value="<?php echo htmlspecialchars($row['id']); ?>"><?php echo htmlspecialchars($row['nome']); ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
            </div>

            <div class="form-row">
                <!-- Selecionar Equipe -->
                <div class="form-group col-md-3">
                    <label for="equipe_id">Equipe:</label>
                    <select name="equipe_id" id="equipe_id" class="form-control form-control-sm" required>
                        <option value="">Selecione uma equipe</option>
                        <?php while($row = $equipes_result->fetch_assoc()): ?>
                            <option value="<?php echo htmlspecialchars($row['id']); ?>"><?php echo htmlspecialchars($row['nome']); ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <!-- Selecionar Horário de Início -->
                <div class="form-group col-md-3">
                    <label for="horario_inicio">Horário de Início:</label>
                    <input type="time" name="horario_inicio" id="horario_inicio" class="form-control form-control-sm" required>
                </div>

                <!-- Selecionar Horário de Fim -->
                <div class="form-group col-md-3">
                    <label for="horario_fim">Horário de Fim:</label>
                    <input type="time" name="horario_fim" id="horario_fim" class="form-control form-control-sm" required>
                </div>
            </div>

            <div class="form-group">
                <label>Dias de Trabalho:</label><br>
                <!-- Supondo que os dias são gerados dinamicamente -->
                <?php foreach ($days as $day): ?>
                    <div class="day" data-day="<?php echo htmlspecialchars($day['day']); ?>">
                        <?php echo htmlspecialchars($day['day']); ?>
                    </div>
                <?php endforeach; ?>
            </div>

            <input type="hidden" name="dias_trabalho" id="dias_trabalho" value="[]">

            <button type="submit" class="btn btn-primary">Incluir GCM na Escala</button>
            <a href="escala_mensal.php" class="btn btn-secondary">Voltar</a>
        </form>
    </div>

    <!-- jQuery e Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>

    <script>
    $(document).ready(function () {
    var diasTrabalho = Array(31).fill(0); // Inicializar com 0 (todos os dias são folga por padrão)

    function isAnoBissexto(ano) {
        return (ano % 4 === 0 && ano % 100 !== 0) || (ano % 400 === 0);
    }

    function obterDiasNoMes(mes, ano) {
        if (mes === 2) { // Fevereiro
            return isAnoBissexto(ano) ? 29 : 28;
        } else if (mes === 4 || mes === 6 || mes === 9 || mes === 11) { // Abril, Junho, Setembro, Novembro
            return 30;
        } else { // Janeiro, Março, Maio, Julho, Agosto, Outubro, Dezembro
            return 31;
        }
    }

    function exibirDiasDoMes(mes, ano) {
        var diasNoMes = obterDiasNoMes(mes, ano);
        var diasContainer = $('#dias_container');
        diasContainer.empty(); // Limpar dias anteriores

        for (var dia = 1; dia <= diasNoMes; dia++) {
            var dayDiv = $('<div></div>')
                .addClass('day')
                .data('day', dia)
                .text(dia);
            diasContainer.append(dayDiv);
        }
    }

    function obterDiaDoAno(dia, mes, ano) {
        var data = new Date(ano, mes - 1, dia);
        var inicioAno = new Date(ano, 0, 1);
        var diff = data - inicioAno;
        var diaDoAno = Math.ceil(diff / (1000 * 60 * 60 * 24)) + 1;

        // Ajustar para anos bissextos
        if (isAnoBissexto(ano) && mes > 2) {
            diaDoAno += 1;
        }

        return diaDoAno; // Retorna o dia do ano ajustado para ano bissexto
    }

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

    // Lógica para colorir e selecionar os dias conforme a equipe selecionada
    $('#equipe_id').change(function () {
        var equipe = $('#equipe_id option:selected').text();
        diasTrabalho.fill(0); // Resetar o array de dias de trabalho
        $('#dias_trabalho').val(JSON.stringify(diasTrabalho)); // Resetar o campo oculto

        $('.day').removeClass('equipe-azul selected'); // Limpar seleções anteriores

        $('.day').each(function () {
            var day = $(this).data('day');
            var mes = <?php echo $mes; ?>;
            var ano = <?php echo $ano; ?>;
            var diaDoAno = obterDiaDoAno(day, mes, ano);

            if ((equipe === 'C' && diaDoAno % 2 === 0) || (equipe === 'A' && diaDoAno % 2 !== 0)) {
                $(this).addClass('equipe-azul selected');
                diasTrabalho[day - 1] = 1; // Marcar dia de trabalho
            } else if ((equipe === 'D' && diaDoAno % 2 === 0) || (equipe === 'B' && diaDoAno % 2 !== 0)) {
                $(this).addClass('equipe-azul selected');
                diasTrabalho[day - 1] = 1; // Marcar dia de trabalho
            } else if (equipe === 'ADM') {
                var isDiaUtil = function (dia, mes, ano) {
                    var data = new Date(ano, mes - 1, dia);
                    var diaSemana = data.getDay(); // 0 (domingo) a 6 (sábado)
                    return diaSemana !== 0 && diaSemana !== 6; // Retorna true se for dia útil
                };

                if (isDiaUtil(day, mes, ano)) {
                    $(this).addClass('equipe-azul selected');
                    diasTrabalho[day - 1] = 1; // Marcar dia de trabalho
                }
            }
        });

        $('#dias_trabalho').val(JSON.stringify(diasTrabalho)); // Atualizar o campo oculto
    });

    // Adicionar funcionalidade de clique para seleção/deseleção de dias
    $('.day').on('click', function () {
        var day = $(this).data('day');
        var index = day - 1;

        if ($(this).hasClass('selected')) {
            // Se já está selecionado, desmarcar e remover da lista
            $(this).removeClass('selected equipe-azul');
            diasTrabalho[index] = 0;
        } else {
            // Se não está selecionado, marcar e adicionar à lista
            $(this).addClass('selected equipe-azul');
            diasTrabalho[index] = 1;
        }

        $('#dias_trabalho').val(JSON.stringify(diasTrabalho)); // Atualizar o campo oculto
    });

    // Inicializar exibição dos dias do mês
    var mes = <?php echo $mes; ?>;
    var ano = <?php echo $ano; ?>;
    exibirDiasDoMes(mes, ano); // Exibir os dias do mês atual ao carregar a página
});

</script>
</body>
</html>
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

$escala_id = intval($_GET['escala_id']); // Garantir que seja um número inteiro

// Consultar dados para a escala
$escala_stmt = $mysqli->prepare("SELECT * FROM escalas WHERE id = ?");
if (!$escala_stmt) {
    die("Erro na preparação da consulta de escala: " . $mysqli->error);
}

$escala_stmt->bind_param("i", $escala_id);
$escala_stmt->execute();
$escala_result = $escala_stmt->get_result();

if ($escala_result->num_rows > 0) {
    $escala = $escala_result->fetch_assoc();
    $inspetoria_id = $escala['inspetoria_id'];
    $mes_str = $escala['mes'];
    $ano = $escala['ano'];
} else {
    die("Escala não encontrada.");
}

// Converter o nome do mês para número
$meses = [
    'Janeiro' => 1,
    'Fevereiro' => 2,
    'Março' => 3,
    'Abril' => 4,
    'Maio' => 5,
    'Junho' => 6,
    'Julho' => 7,
    'Agosto' => 8,
    'Setembro' => 9,
    'Outubro' => 10,
    'Novembro' => 11,
    'Dezembro' => 12,
];

$mes = isset($meses[$mes_str]) ? $meses[$mes_str] : 1; // Default para Janeiro se o mês não for encontrado

// Verificar se o formulário foi enviado
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $usuario_id = $_POST['usuario_id'];
    $modalidade_id = $_POST['modalidade_id'];
    $equipe_id = $_POST['equipe_id'];
    $horario_inicio = $_POST['horario_inicio'];
    $horario_fim = $_POST['horario_fim'];
    $dias_trabalho = isset($_POST['dias_trabalho']) ? json_decode($_POST['dias_trabalho'], true) : [];
    $dias_trabalho = is_array($dias_trabalho) ? implode('', $dias_trabalho) : '';

    // Verificar se o GCM já está na escala com os mesmos parâmetros
    $verificar_stmt = $mysqli->prepare("SELECT * FROM escala_gcm WHERE usuario_id = ? AND escala_id = ? AND modalidade_id = ? AND equipe_id = ? AND horario_inicio = ? AND horario_fim = ?");
    $verificar_stmt->bind_param("iiisss", $usuario_id, $escala_id, $modalidade_id, $equipe_id, $horario_inicio, $horario_fim);
    $verificar_stmt->execute();
    $verificar_result = $verificar_stmt->get_result();

    if ($verificar_result->num_rows > 0) {
        // Se já existir, não permite a duplicação
        echo "<div class='alert alert-warning' role='alert'>Este GCM já foi incluído na escala com os mesmos parâmetros.</div>";
    } else {
        // Inserir GCM na escala
        $stmt = $mysqli->prepare("INSERT INTO escala_gcm (escala_id, usuario_id, modalidade_id, equipe_id, horario_inicio, horario_fim, dias_trabalho, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())");
        if (!$stmt) {
            die("Erro na preparação da consulta de inserção: " . $mysqli->error);
        }
        $stmt->bind_param("iiissss", $escala_id, $usuario_id, $modalidade_id, $equipe_id, $horario_inicio, $horario_fim, $dias_trabalho);

        if ($stmt->execute()) {
            echo "<div class='alert alert-success' role='alert'>GCM incluído com sucesso na Escala!</div>";
        
        }

        $stmt->close();
    }


        $verificar_stmt->close();
}



// Consultar GCMs já incluídos na escala
$gcm_result = $mysqli->query("
    SELECT escala_gcm.id, usuarios.qra, equipes.nome AS equipe, modalidades_servicos.nome AS modalidade, escala_gcm.horario_inicio, escala_gcm.horario_fim
    FROM escala_gcm
    JOIN usuarios ON escala_gcm.usuario_id = usuarios.id
    JOIN modalidades_servicos ON escala_gcm.modalidade_id = modalidades_servicos.id
    JOIN equipes ON escala_gcm.equipe_id = equipes.id
    WHERE escala_gcm.escala_id = $escala_id
");

// Fechar a conexão
$mysqli->close();
?>

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

// Verificar se o GCM foi excluído
if (isset($_POST['excluir_gcm_id'])) {
    $gcm_id = intval($_POST['excluir_gcm_id']);
    $delete_stmt = $mysqli->prepare("DELETE FROM escala_gcm WHERE id = ?");
    $delete_stmt->bind_param("i", $gcm_id);

    if ($delete_stmt->execute()) {
        echo "<div class='alert alert-success' role='alert'>GCM excluído com sucesso!</div>";
    
    }

    $delete_stmt->close();
}

// Consultar GCMs já incluídos na escala
$gcm_result = $mysqli->query("
    SELECT escala_gcm.id, usuarios.qra, equipes.nome AS equipe, modalidades_servicos.nome AS modalidade, escala_gcm.horario_inicio, escala_gcm.horario_fim
    FROM escala_gcm
    JOIN usuarios ON escala_gcm.usuario_id = usuarios.id
    JOIN modalidades_servicos ON escala_gcm.modalidade_id = modalidades_servicos.id
    JOIN equipes ON escala_gcm.equipe_id = equipes.id
    WHERE escala_gcm.escala_id = $escala_id
");

// Fechar a conexão
$mysqli->close();
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GCM's já incluídos na Escala</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
    <div class="container mt-5">
        <h1 class="mb-4">Edição de Escala</h1>

        <!-- Exibir GCMs já incluídos na escala -->
        <h2 class="mt-4">GCMs Incluídos na Escala</h2>
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
                                <button class="btn btn-danger btn-sm" onclick="confirmarExclusao(<?php echo $gcm['id']; ?>)">Excluir</button>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>Nenhum GCM foi incluído nesta escala ainda.</p>
        <?php endif; ?>
    </div>

    <center><a href="escala_mensal.php" class="btn btn-secondary">Voltar</a></center>

    <!-- Modal de Confirmação de Exclusão -->
    <div class="modal fade" id="modalConfirmacao" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Confirmar Exclusão</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    Tem certeza que deseja excluir este GCM da escala?
                </div>
                <div class="modal-footer">
                    <form method="POST" id="formExclusao">
                        <input type="hidden" name="excluir_gcm_id" id="gcmIdParaExcluir" value="">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-danger">Excluir</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- jQuery e Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>

    <script>
        function confirmarExclusao(gcmId) {
            // Define o ID do GCM que será excluído
            $('#gcmIdParaExcluir').val(gcmId);

            // Exibe o modal de confirmação
            $('#modalConfirmacao').modal('show');
        }
    </script>
</body>
</html>
