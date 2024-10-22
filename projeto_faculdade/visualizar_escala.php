<?php
// Iniciar a sessão
session_start();

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

// Função para verificar se o dia é útil
function isDiaUtil($data) {
    $dia_semana = date('N', strtotime($data));
    return ($dia_semana >= 1 && $dia_semana <= 5); // Retorna true se for dia útil (segunda a sexta)
}

// Função para verificar se o dia é feriado
function isFeriado($data, $mysqli) {
    $feriado_query = "SELECT * FROM feriados WHERE data = ?";
    $stmt = $mysqli->prepare($feriado_query);
    $stmt->bind_param("s", $data);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->num_rows > 0; // Retorna true se o dia for um feriado
}

function gerarDiasTrabalho($equipe, $dias_no_mes, $ano, $mes, $mysqli) {
    $dias_trabalho = '';
    $dia_inicial = 1; // Primeiro dia de trabalho

    // Define o ciclo de trabalho para cada equipe
    $ciclo_trabalho = [
        'A' => 2, // Equipe A trabalha em dias alternados
        'B' => 2, // Equipe B trabalha em dias alternados
        'C' => 2, // Equipe C trabalha em dias alternados
        'D' => 2, // Equipe D trabalha em dias alternados
        'ADM' => 1, // Equipe ADM trabalha todos os dias úteis
        'ESPECIAL' => 1  // Exemplo para equipe especial, pode ser ajustado
    ];
    $ciclo = $ciclo_trabalho[$equipe] ?? 1; // Padrão é 1 dia de trabalho

    for ($dia = 1; $dia <= $dias_no_mes; $dia++) {
        $data = "$ano-$mes-" . str_pad($dia, 2, '0', STR_PAD_LEFT);
        $dia_semana = date('N', strtotime($data));

        if ($equipe === 'ADM') {
            // Equipe ADM trabalha de segunda a sexta (dias úteis) e exclui feriados
            $dias_trabalho .= (isDiaUtil($data) && !isFeriado($data, $mysqli)) ? '1' : '0';
        } elseif ($equipe === 'ESPECIAL') {
            // Exemplo de lógica para equipe especial: mostrar somente os dias selecionados
            $dias_trabalho .= ($dia % 2 === 0) ? '1' : '0'; // Trabalha em dias pares
        } else {
            // Ciclo para equipes A e B e C e D
            $ciclo_dia = ($dia - $dia_inicial) % $ciclo;

            // Alterna equipes A e B e C e D em dias diferentes
            if ($equipe === 'A' || $equipe === 'B') {
                $dias_trabalho .= ($dia % 2 === 0) ? '1' : '0'; // Alterna trabalho a cada dia
            } elseif ($equipe === 'C' || $equipe === 'D') {
                $dias_trabalho .= ($dia % 2 === 1) ? '1' : '0'; // Alterna trabalho a cada dia
            }
        }
    }

    return $dias_trabalho;
}




// Mapeamento de meses por texto para número
$meses = [
    'Janeiro' => '01',
    'Fevereiro' => '02',
    'Março' => '03',
    'Abril' => '04',
    'Maio' => '05',
    'Junho' => '06',
    'Julho' => '07',
    'Agosto' => '08',
    'Setembro' => '09',
    'Outubro' => '10',
    'Novembro' => '11',
    'Dezembro' => '12'
];


// Mapeamento dos dias da semana para as iniciais em português
$iniciais_dias = [
    1 => 'S', // Segunda-feira
    2 => 'T', // Terça-feira
    3 => 'Q', // Quarta-feira
    4 => 'Q', // Quinta-feira
    5 => 'S', // Sexta-feira
    6 => 'S', // Sábado
    7 => 'D'  // Domingo
];


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

// Validar e converter o mês para número
if (array_key_exists($mes_atual_texto, $meses)) {
    $mes_atual = $meses[$mes_atual_texto];
} else {
    die("Mês inválido.");
}

// Número de dias no mês
$dias_no_mes = cal_days_in_month(CAL_GREGORIAN, (int)$mes_atual, (int)$ano_atual);

// Consultar o nome da inspetoria
$inspetoria_query = "SELECT nome FROM inspetorias WHERE id = ?";
$stmt = $mysqli->prepare($inspetoria_query);
$stmt->bind_param("i", $inspetoria_id);
$stmt->execute();
$inspetoria_result = $stmt->get_result();

if ($inspetoria_result->num_rows === 0) {
    die("Inspetoria não encontrada.");
}

$inspetoria = $inspetoria_result->fetch_assoc();
$nome_inspetoria = htmlspecialchars($inspetoria['nome']);

// Consultar os GCMs associados a esta escala e ordenar com ADM primeiro e depois por equipe
$gcm_query = "SELECT escala_gcm.id AS escala_gcm_id, usuarios.qra AS gcm_nome, modalidades_servicos.nome AS modalidade, 
                     equipes.nome AS equipe, escala_gcm.horario_inicio, escala_gcm.horario_fim, escala_gcm.dias_trabalho
              FROM escala_gcm
              JOIN usuarios ON escala_gcm.usuario_id = usuarios.id
              JOIN modalidades_servicos ON escala_gcm.modalidade_id = modalidades_servicos.id
              JOIN equipes ON escala_gcm.equipe_id = equipes.id
              WHERE escala_gcm.escala_id = ?
              ORDER BY CASE WHEN equipes.nome = 'ADM' THEN 0 ELSE 1 END, equipes.nome, usuarios.qra";

$stmt = $mysqli->prepare($gcm_query);
if (!$stmt) {
    die("Erro na preparação da consulta: " . $mysqli->error);
}
$stmt->bind_param("i", $escala_id);
$stmt->execute();
$result = $stmt->get_result();

$current_equipe = null;
$contagem_equipes = [
    'A' => 0,
    'B' => 0,
    'C' => 0,
    'D' => 0,
    'ADM' => 0,
    
];

echo "<img src='uploads/brasao.png' alt='Imagem' style='max-width:50px;'>";
echo "<img src='uploads/BandeiraParnaíba.png' alt='Imagem' style='max-width:70px; float: right;'>";
echo "<center><h2>Escala Mensal - $mes_atual_texto/$ano_atual</h2></center>";
echo "<center><h3>Inspetoria: <span id='inspetoria'>$nome_inspetoria</span></h3></center>";

if ($result->num_rows > 0) {
    while ($gcm = $result->fetch_assoc()) {
        

        if ($current_equipe !== $gcm['equipe']) {
            if ($current_equipe !== null) {
                echo "</tbody></table>"; // Fecha a tabela anterior
            }
            $current_equipe = $gcm['equipe'];
            //echo "<center><h4>Equipe " . htmlspecialchars($gcm['equipe']) . "</h4></center>";
            echo "<table>
                    <thead>
                        <tr>";
                            
            // Linha com iniciais dos dias da semana
            echo "<tr>";
            echo "<th colspan='3'><h3>Equipe " . htmlspecialchars($gcm['equipe']) . " - " . htmlspecialchars($nome_inspetoria) . "</h3></th>"; // Células de início e término
            for ($i = 1; $i <= $dias_no_mes; $i++) {
                $data = "$ano_atual-$mes_atual-" . str_pad($i, 2, '0', STR_PAD_LEFT);
                $dia_semana = date('N', strtotime($data));
                $inicial_dia = $iniciais_dias[$dia_semana] ?? ''; // Usa a inicial do dia da semana

                // Verificar se é feriado ou fim de semana para colorir em vermelho
                if (isFeriado($data, $mysqli) || !isDiaUtil($data)) {
                    echo "<th style='color: red;'>$inicial_dia</th>";
                } else {
                    echo "<th>$inicial_dia</th>";
                }
            }
            echo "</tr>";
            echo "</tr>";
            echo "<tr>
                    <th>Nome</th>
                    <th>Posto</th>
                    <th>Periodo</th>";
                    //<th>Fim</th>";
                    
            for ($i = 1; $i <= $dias_no_mes; $i++) {
                echo "<th>$i</th>";
            }
            echo "</tr>
                  </thead>
                  <tbody>";
        }

        $contagem_equipes[$current_equipe]++; // Incrementar a contagem para a equipe atual

        // Exibe as informações do GCM
            echo "<tr>
            <td>" . htmlspecialchars($gcm['gcm_nome']) . "</td>
            <td>" . htmlspecialchars($gcm['modalidade']) . "</td>
            <td>" . date('H:i', strtotime($gcm['horario_inicio'])) . " - " . date('H:i', strtotime($gcm['horario_fim'])) . "</td>";


            // Verifica a quantidade de dias no mês
            for ($i = 1; $i <= $dias_no_mes; $i++) {
                $index = $i - 1; // Índice para acessar os caracteres da string
                $dia_trabalho = (strlen($gcm['dias_trabalho']) > $index) ? $gcm['dias_trabalho'][$index] : '0'; // Valor padrão '0'
                $class = ($dia_trabalho === '1') ? 'dia-trabalho' : 'dia-folga'; // Define a classe CSS
                
                // Exibe o valor "1" ou "0" dentro da célula da tabela
                echo "<td class='$class'>" . "</td>";
            }

            echo "</tr>";


    }
    echo "</tbody></table>";
}

// Consultar os GCMs afastados associados a esta escala
$afastados_query = "SELECT usuarios.qra AS gcm_nome, tipos_afastamentos.nome AS tipo_afastamento, 
                           afastamentos_escala.data_inicio, afastamentos_escala.data_fim
                    FROM afastamentos_escala
                    JOIN usuarios ON afastamentos_escala.usuario_id = usuarios.id
                    JOIN tipos_afastamentos ON afastamentos_escala.tipo_afastamento_id = tipos_afastamentos.id
                    WHERE afastamentos_escala.escala_id = ?";

$stmt = $mysqli->prepare($afastados_query);

    if (!$stmt) {
        die("Erro na preparação da consulta de afastados: " . $mysqli->error);
    }

    $stmt->bind_param("i", $escala_id);
    $stmt->execute();
    $afastados_result = $stmt->get_result();

    if (!$afastados_result) {
        die("Erro na execução da consulta de afastados: " . $stmt->error);
    }

    if ($afastados_result->num_rows > 0) {
        echo "<center><h4>Outros</h4></center>";
        echo "<table>
                <thead>
                    <tr>
                        <th>Nome</th>
                        <th>Tipo de Afastamento</th>
                        <th>Data Início</th>
                        <th>Data Fim</th>
                    </tr>
                </thead>
                <tbody>";
            
    while ($afastado = $afastados_result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($afastado['gcm_nome']) . "</td>";
        echo "<td>" . htmlspecialchars($afastado['tipo_afastamento']) . "</td>";
        echo "<td>" . htmlspecialchars(date('d/m/Y', strtotime($afastado['data_inicio']))) . "</td>";
        echo "<td>" . htmlspecialchars(date('d/m/Y', strtotime($afastado['data_fim']))) . "</td>";
        echo "</tr>";
    }

        echo "</tbody></table>";
    } else {
        echo "<br><center><h1></h1></center>";
    }

// Tabela com a contagem final após afastamentos
        echo "<center><h4>GCMs por Equipe</h4></center>";
        echo "<table border='1' style='margin-top: 20px;'>
                <thead>
                    <tr>
                        <th>Equipe</th>
                        <th>Total de GCMs</th>
                    </tr>
                </thead>
                <tbody>";
    foreach ($contagem_equipes as $equipe => $total) {
        echo "<tr>
                <td>" . htmlspecialchars($equipe) . "</td>
                <td>" . htmlspecialchars($total) . "</td>
            </tr>";
    }
        echo "</tbody></table>";
?>





<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Escala Mensal</title>
    <footer> <br><br>Subcomandante GCM:_______________________________________________<br><br><br>
            Comandante GCM:____________________________________________________
</footer>
    <style>
        
        
        /* Estilos para a tabela de impressão */
        table {
            
            width: 100%;
            font-size: 15px; /* Diminuir a fonte das tabelas */
        }
        th {
            border: 1px solid #A9A9A9;
            padding: 2px;
            text-align: center;
            font-size: 15px; /* Diminuir a fonte das células */
        }

        td {
            border: 1px solid #A9A9A9;
            padding: 2px;
            text-align: center;
            font-size: 15px; /* Diminuir a fonte das células */
        }

        /* Estilos gerais */
        body {
            font-family: Arial, sans-serif;
            font-size: 12px; /* Diminuir um pouco o tamanho da fonte para se ajustar melhor ao A4 */
        }

        /* Estilos da tabela */
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 9px 0;
        }

        th, td {
            border: 1px solid #DCDCDC;
            padding: 4px; /* Ajuste de padding para caber melhor em A4 */
            text-align: center;
        }

        th {
            background-color: #D3D3D3;
            font-weight: bold;
        }

        td.preto {
            background-color: #363636;
        }
        .dia-trabalho {
            background-color: black;
            color: white;
        }

        .dia-folga {
            background-color: white;
            color: black;
        }



        /* Ajustes para a impressão */
        @media print {
            @page {
                size: A4; /* Define o tamanho da página como A4 */
                margin: 12mm; /* Define a margem da página para impressão */
            }

            table {
                page-break-inside: auto; /* Permitir quebras dentro da tabela */
            }

            tr {
                page-break-after: auto;   /* Permitir quebras depois de uma linha, se necessário */
            }

            thead {
                display: table-header-group; /* Repetir cabeçalho da tabela em novas páginas */                
            }

            tfoot {
                display: table-footer-group; /* Repetir rodapé da tabela em novas páginas */
            }

            /* Ocultar elementos que não devem ser impressos */
            .no-print {
                display: none;
            }
        }
    </style>
</head>
<body>
    <br><center><button class="no-print" onclick="window.history.back()">Voltar</button></center>
    <br><center><button class="no-print" onclick="window.print()">Imprimir</button></center>
</body>
</html>