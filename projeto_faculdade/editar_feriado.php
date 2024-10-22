<?php
// Iniciar a sessão
session_start();

// Conectar ao banco de dados
$mysqli = new mysqli("localhost", "root", "", "sistema_gcmsp");

if ($mysqli->connect_error) {
    die("Conexão falhou: " . $mysqli->connect_error);
}

// Consultar o feriado a ser editado
if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $feriado_query = "SELECT * FROM feriados WHERE id = ?";
    $feriado_stmt = $mysqli->prepare($feriado_query);
    $feriado_stmt->bind_param("i", $id);
    $feriado_stmt->execute();
    $feriado_result = $feriado_stmt->get_result();

    if ($feriado_result->num_rows > 0) {
        $feriado = $feriado_result->fetch_assoc();
    } else {
        die("Feriado não encontrado.");
    }
} else {
    die("ID do feriado não fornecido.");
}

// Processar atualização de feriado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = $_POST['data'];
    $descricao = $_POST['descricao'];

    $update_query = "UPDATE feriados SET data = ?, descricao = ? WHERE id = ?";
    $update_stmt = $mysqli->prepare($update_query);
    $update_stmt->bind_param("ssi", $data, $descricao, $id);

    if ($update_stmt->execute()) {
        header("Location: gerenciar_feriados.php");
        exit();
    } else {
        $message = "Erro ao atualizar feriado: " . $mysqli->error;
    }
}

// Fechar a conexão com o banco de dados
$mysqli->close();
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Feriado</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }
        .container {
            max-width: 600px;
            margin: auto;
        }
        form {
            margin: 20px 0;
        }
        label {
            display: block;
            margin-bottom: 8px;
        }
        input, textarea {
            width: 100%;
            padding: 8px;
            margin-bottom: 12px;
        }
        .message {
            margin: 20px 0;
            color: green;
        }
        .message.error {
            color: red;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Editar Feriado</h1>

        <?php if (isset($message)): ?>
            <div class="message <?= isset($error) ? 'error' : '' ?>">
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>

        <form action="editar_feriado.php?id=<?= htmlspecialchars($id) ?>" method="post">
            <label for="data">Data:</label>
            <input type="date" id="data" name="data" value="<?= htmlspecialchars($feriado['data']) ?>" required>

            <label for="descricao">Descrição:</label>
            <input type="text" id="descricao" name="descricao" value="<?= htmlspecialchars($feriado['descricao']) ?>" required>

            <input type="hidden" name="update_id" value="<?= htmlspecialchars($id) ?>">
            <button type="submit">Atualizar Feriado</button>
        </form>
    </div>
</body>
</html>
