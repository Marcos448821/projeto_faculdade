<?php
// Conectar ao banco de dados
$mysqli = new mysqli("localhost", "root", "", "sistema_gcmsp");

if ($mysqli->connect_error) {
    die("Conexão falhou: " . $mysqli->connect_error);
}

// Obter todos os usuários e funcionalidades
$usuarios = $mysqli->query("SELECT * FROM usuarios");
$funcionalidades = $mysqli->query("SELECT * FROM funcionalidades");

// Obter funcionalidades permitidas por usuário
$usuario_funcionalidades = [];
$id_usuario = null;
if (isset($_POST['id_usuario']) || isset($_POST['carregar_funcionalidades'])) {
    $id_usuario = isset($_POST['id_usuario']) ? intval($_POST['id_usuario']) : intval($_POST['carregar_funcionalidades']);
    if ($id_usuario) {
        $result = $mysqli->query("SELECT funcionalidade_id FROM usuarios_funcionalidades WHERE usuario_id = $id_usuario");
        while ($row = $result->fetch_assoc()) {
            $usuario_funcionalidades[$id_usuario][] = $row['funcionalidade_id'];
        }
    }
}

// Processar o envio do formulário
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['id_usuario'])) {
    $id_usuario = intval($_POST['id_usuario']);
    $funcionalidades_ids = isset($_POST['funcionalidades']) ? $_POST['funcionalidades'] : [];

    // Atualizar funcionalidades permitidas para o usuário
    $mysqli->query("DELETE FROM usuarios_funcionalidades WHERE usuario_id = $id_usuario");
    if (!empty($funcionalidades_ids)) {
        foreach ($funcionalidades_ids as $funcionalidade_id) {
            $stmt = $mysqli->prepare("INSERT INTO usuarios_funcionalidades (usuario_id, funcionalidade_id) VALUES (?, ?)");
            $stmt->bind_param('ii', $id_usuario, $funcionalidade_id);
            $stmt->execute();
        }
        echo "<div class='alert alert-success'>Funcionalidades atualizadas com sucesso!</div>";
    } else {
        echo "<div class='alert alert-warning'>Nenhuma funcionalidade selecionada.</div>";
    }

    // Atualizar o array de funcionalidades após a atualização
    $result = $mysqli->query("SELECT funcionalidade_id FROM usuarios_funcionalidades WHERE usuario_id = $id_usuario");
    $usuario_funcionalidades[$id_usuario] = [];
    while ($row = $result->fetch_assoc()) {
        $usuario_funcionalidades[$id_usuario][] = $row['funcionalidade_id'];
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestão de Usuários - Sistema GCMSP</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
    <style>
        .switch {
            position: relative;
            display: inline-block;
            width: 30px; /* Largura reduzida */
            height: 16px; /* Altura reduzida */
        }

        .switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }

        .slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #ccc;
            transition: .4s;
            border-radius: 16px; /* Ajustado para a altura reduzida */
        }

        .slider:before {
            position: absolute;
            content: "";
            height: 14px; /* Ajustado para a altura reduzida */
            width: 14px; /* Ajustado para a largura reduzida */
            border-radius: 50%;
            left: 2px;
            bottom: 1px;
            background-color: white;
            transition: .4s;
        }

        input:checked + .slider {
            background-color: #2196F3;
        }

        input:checked + .slider:before {
            transform: translateX(14px); /* Ajustado para a largura reduzida */
        }

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
            font-size: 14px; /* Ajustado para melhor visualização */
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <h1 class="text-center">Gestão de Usuários</h1>
        <form action="gestao_usuarios.php" method="POST">
            <div class="form-group">
                <label for="id_usuario">Selecione o Usuário:</label>
                <select id="id_usuario" name="id_usuario" class="form-control" required>
                    <option value="">Selecione um usuário</option>
                    <?php while ($usuario = $usuarios->fetch_assoc()): ?>
                        <option value="<?php echo htmlspecialchars($usuario['id']); ?>" 
                            <?php echo isset($id_usuario) && $id_usuario == $usuario['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($usuario['nome']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
                <button type="submit" name="carregar_funcionalidades" class="btn btn-info mt-2">Carregar Funcionalidades</button>
            </div>

            <h3>Funcionalidades Disponíveis</h3>
            <?php
            // Resetar o ponteiro para ler funcionalidades novamente
            $funcionalidades->data_seek(0);
            while ($func = $funcionalidades->fetch_assoc()): ?>
                <div class="form-check-item">
                    <label class="switch">
                        <input type="checkbox" name="funcionalidades[]" value="<?php echo htmlspecialchars($func['id']); ?>"
                            <?php if (isset($usuario_funcionalidades[$id_usuario]) && in_array($func['id'], $usuario_funcionalidades[$id_usuario])) echo 'checked'; ?>>
                        <span class="slider"></span>
                    </label>
                    <label class="form-check-label">
                        <i class="fa <?php echo htmlspecialchars($func['icone']); ?>" aria-hidden="true"></i>
                        <?php echo htmlspecialchars($func['nome']); ?>
                    </label>
                </div>
            <?php endwhile; ?>

            <button type="submit" class="btn btn-primary mt-3">Atualizar Funcionalidades</button>
        </form>

        <!-- Botão Voltar -->
        <a href="dashboard.php" class="btn btn-secondary mt-4">Voltar</a>
    </div>

    <!-- Scripts do Bootstrap -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
