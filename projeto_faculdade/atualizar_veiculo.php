<?php
session_start();
date_default_timezone_set('America/Sao_Paulo');

if (!isset($_SESSION['cpf'])) {
    http_response_code(401); // Não autorizado
    echo json_encode(['success' => false, 'message' => 'Usuário não autorizado.']);
    exit();
}

$mysqli = new mysqli("localhost", "root", "", "sistema_gcmsp");

if ($mysqli->connect_error) {
    die(json_encode(['success' => false, 'message' => 'Conexão falhou: ' . $mysqli->connect_error]));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true); // Captura os dados JSON

    $id = $data['id'] ?? null; // Usa null se não estiver definido
    $status = $data['status'] ?? null;

    if ($id === null || $status === null) {
        echo json_encode(['success' => false, 'message' => 'ID ou Status não fornecidos.']);
        exit();
    }

    if (!is_numeric($id)) {
        echo json_encode(['success' => false, 'message' => 'ID deve ser um número.']);
        exit();
    }

    // Atualiza o status do veículo
    if ($stmt = $mysqli->prepare("UPDATE consultas_veiculo SET status = ? WHERE id = ?")) {
        $stmt->bind_param("si", $status, $id);
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Status do veículo atualizado com sucesso.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Erro ao atualizar: ' . $stmt->error]);
        }
        $stmt->close();
    } else {
        echo json_encode(['success' => false, 'message' => 'Erro na preparação da consulta: ' . $mysqli->error]);
    }
}

$mysqli->close();
?>
