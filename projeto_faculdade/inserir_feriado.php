<?php
// Iniciar a sessão
session_start();

// Conectar ao banco de dados
$mysqli = new mysqli("localhost", "root", "", "sistema_gcmsp");

if ($mysqli->connect_error) {
    die("Conexão falhou: " . $mysqli->connect_error);
}

// Processar o formulário de inserção
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['update_id'])) {
    $data = $_POST['data'];
    $descricao = $_POST['descricao'];

    // Verificar se a data já existe
    $check_query = "SELECT * FROM feriados WHERE data = ?";
    $check_stmt = $mysqli->prepare($check_query);
    $check_stmt->bind_param("s", $data);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();

    if ($check_result->num_rows > 0) {
        $message = "Feriado já existe para esta data.";
    } else {
        // Inserir novo feriado
        $insert_query = "INSERT INTO feriados (data, descricao) VALUES (?, ?)";
        $insert_stmt = $mysqli->prepare($insert_query);
        $insert_stmt->bind_param("ss", $data, $descricao);

        if ($insert_stmt->execute()) {
            $message = "Feriado inserido com sucesso.";
        } else {
            $message = "Erro ao inserir feriado: " . $mysqli->error;
        }
    }
}

// Processar exclusão de feriado
if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);

    $delete_query = "DELETE FROM feriados WHERE id = ?";
    $delete_stmt = $mysqli->prepare($delete_query);
    $delete_stmt->bind_param("i", $delete_id);

    if ($delete_stmt->execute()) {
        $message = "Feriado excluído com sucesso.";
    } else {
        $message = "Erro ao excluir feriado: " . $mysqli->error;
    }
}

// Consultar todos os feriados
$feriados_query = "SELECT * FROM feriados ORDER BY data";
$feriados_result = $mysqli->query($feriados_query);

// Fechar a conexão com o banco de dados
$mysqli->close();
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inserir e Gerenciar Feriados</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }
        .container {
            max-width: 900px;
            margin: auto;
            background: #fff;
            padding: 20px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
        }
        h1 {
            margin-top: 0;
            color: #333;
        }
        form {
            margin: 20px 0;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 8px;
            background: #fafafa;
        }
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
        }
        input, textarea {
            width: calc(100% - 20px);
            padding: 10px;
            margin-bottom: 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        button {
            background-color: #28a745;
            color: #fff;
            border: none;
            padding: 10px 20px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }
        button:hover {
            background-color: #218838;
        }
        .message {
            margin: 20px 0;
            padding: 10px;
            border-radius: 4px;
        }
        .message.success {
            color: #155724;
            background-color: #d4edda;
            border: 1px solid #c3e6cb;
        }
        .message.error {
            color: #721c24;
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: left;
        }
        th {
            background-color: #f8f9fa;
        }
        .actions a {
            color: #007bff;
            text-decoration: none;
        }
        .actions a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Inserir e Gerenciar Feriados</h1>

        <?php if (isset($message)): ?>
            <div class="message <?= isset($error) ? 'error' : 'success' ?>">
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>

        <form action="inserir_feriado.php" method="post">
            <label for="data">Data:</label>
            <input type="date" id="data" name="data" required>

            <label for="descricao">Descrição:</label>
            <input type="text" id="descricao" name="descricao" required>

            <button type="submit">Inserir Feriado</button>
        </form>

        <h2>Feriados Cadastrados</h2>
        <?php if ($feriados_result->num_rows > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Data</th>
                        <th>Descrição</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($feriado = $feriados_result->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($feriado['id']) ?></td>
                            <td><?= htmlspecialchars($feriado['data']) ?></td>
                            <td><?= htmlspecialchars($feriado['descricao']) ?></td>
                            <td class="actions">
                                <a href="editar_feriado.php?id=<?= htmlspecialchars($feriado['id']) ?>">Editar</a> | 
                                <a href="?delete_id=<?= htmlspecialchars($feriado['id']) ?>" onclick="return confirm('Tem certeza que deseja excluir este feriado?');">Excluir</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>Nenhum feriado encontrado.</p>
        <?php endif; ?>
    </div>
</body>
</html>
