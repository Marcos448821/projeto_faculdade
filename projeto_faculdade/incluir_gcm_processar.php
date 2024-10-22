<?php
include('conexao.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $gcm_qra = $_POST['gcm_qra'];
    $horario_inicio = $_POST['horario_inicio'];
    $horario_fim = $_POST['horario_fim'];
    $equipe = $_POST['equipe'];
    
    $posto_servico_id = null;
    $viatura_id = null;
    
    $tipo_selecao = $_POST['tipo_selecao'];

    if ($tipo_selecao === 'viatura') {
        $viatura_id = $_POST['viatura'] ?? null;
        $funcao = $_POST['funcao'] ?? null;

        if (empty($viatura_id) || empty($funcao)) {
            echo "<div class='alert alert-danger mt-4'>Por favor, selecione uma viatura e sua função.</div>";
            exit;
        }
    } elseif ($tipo_selecao === 'posto') {
        $posto_servico_id = $_POST['posto_servico'] ?? null;

        if (empty($posto_servico_id)) {
            echo "<div class='alert alert-danger mt-4'>Por favor, selecione um posto de serviço.</div>";
            exit;
        }
    } else {
        echo "<div class='alert alert-danger mt-4'>Selecione uma opção de viatura ou posto de serviço.</div>";
        exit;
    }

    // Buscar o ID do GCM pelo QRA
    $gcm_query = "SELECT id FROM usuarios WHERE qra = ?";
    $stmt = $mysqli->prepare($gcm_query);
    $stmt->bind_param("s", $gcm_qra);
    $stmt->execute();
    $result = $stmt->get_result();
    $gcm_data = $result->fetch_assoc();

    if ($gcm_data) {
        $gcm_id = $gcm_data['id'];
    } else {
        echo "<div class='alert alert-danger mt-4'>GCM não encontrado.</div>";
        exit;
    }

    // Inserir na tabela escala_gcm
    $insert_query = "INSERT INTO escala_gcm (usuario_id, horario_inicio, horario_fim, equipe, posto_id, viatura_id, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())";
    $stmt = $mysqli->prepare($insert_query);
    $stmt->bind_param("sssss", $gcm_id, $horario_inicio, $horario_fim, $equipe, $posto_servico_id, $viatura_id);

    if ($stmt->execute()) {
        echo "<div class='alert alert-success mt-4'>GCM incluído com sucesso!</div>";
    } else {
        echo "<div class='alert alert-danger mt-4'>Erro ao incluir GCM.</div>";
    }
}
?>
