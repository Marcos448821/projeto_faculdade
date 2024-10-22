<?php
// Inicia a sessão
session_start();

// Verifica se o usuário está autenticado
if (!isset($_SESSION['cpf'])) {
    header("Location: login.php");
    exit();
}

// Conecta ao banco de dados
$mysqli = new mysqli("localhost", "root", "", "sistema_gcmsp");

if ($mysqli->connect_error) {
    die("Conexão falhou: " . $mysqli->connect_error);
}

// Obtém o CPF do usuário da sessão
$cpf = $_SESSION['cpf'];

// Obtém os dados do usuário, incluindo 're'
$stmt = $mysqli->prepare("SELECT nome, re, cpf, rg, data_nascimento, data_incorporacao, email FROM usuarios WHERE cpf = ?");
$stmt->bind_param("s", $cpf);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// Atualiza os dados do usuário
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nome = $_POST['nome'];
    $re = $_POST['re']; // Captura o RE
    $rg = $_POST['rg'];
    $data_nascimento = $_POST['data_nascimento'];
    $data_incorporacao = $_POST['data_incorporacao'];
    $email = $_POST['email'];
    $senha = !empty($_POST['senha']) ? password_hash($_POST['senha'], PASSWORD_DEFAULT) : $user['senha'];

    // Atualiza os dados no banco de dados, incluindo 're'
    $stmt = $mysqli->prepare("UPDATE usuarios SET nome = ?, re = ?, rg = ?, data_nascimento = ?, data_incorporacao = ?, email = ?, senha = ? WHERE cpf = ?");
    $stmt->bind_param("ssssssss", $nome, $re, $rg, $data_nascimento, $data_incorporacao, $email, $senha, $cpf);
    
    if ($stmt->execute()) {
        echo "<div class='alert alert-success'>Cadastro atualizado com sucesso!</div>";
    } else {
        echo "<div class='alert alert-danger'>Erro ao atualizar cadastro.</div>";
    }
}

// Fecha a conexão
$mysqli->close();
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Cadastro</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
    <style>
        .password-container {
            position: relative;
        }
        .password-container input {
            padding-right: 2.5rem;
        }
        .password-container .toggle-password {
            position: absolute;
            right: 0.5rem;
            top: 70%;
            transform: translateY(-50%);
            cursor: pointer;
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <h1 class="text-center">Editar Cadastro</h1>
        <form action="editar_cadastro.php" method="POST">
            <div class="form-group">
                <label for="nome">Nome Completo:</label>
                <input type="text" id="nome" name="nome" class="form-control" value="<?php echo htmlspecialchars($user['nome']); ?>" required>
            </div>
            <div class="form-group">
                <label for="re">RE:</label>
                <input type="text" id="re" name="re" class="form-control" value="<?php echo htmlspecialchars($user['re']); ?>" required>
            </div>
            <div class="form-group">
                <label for="cpf">CPF:</label>
                <input type="text" id="cpf" name="cpf" class="form-control" value="<?php echo htmlspecialchars($user['cpf']); ?>" readonly>
            </div>
            <div class="form-group">
                <label for="rg">RG:</label>
                <input type="text" id="rg" name="rg" class="form-control" value="<?php echo htmlspecialchars($user['rg']); ?>" required>
            </div>
            <div class="form-group">
                <label for="data_nascimento">Data de Nascimento:</label>
                <input type="date" id="data_nascimento" name="data_nascimento" class="form-control" value="<?php echo htmlspecialchars($user['data_nascimento']); ?>" required>
            </div>
            <div class="form-group">
                <label for="data_incorporacao">Data de Incorporação:</label>
                <input type="date" id="data_incorporacao" name="data_incorporacao" class="form-control" value="<?php echo htmlspecialchars($user['data_incorporacao']); ?>" required>
            </div>
            <div class="form-group">
                <label for="email">E-mail:</label>
                <input type="email" id="email" name="email" class="form-control" value="<?php echo htmlspecialchars($user['email']); ?>" required>
            </div>
            <div class="form-group password-container">
                <label for="senha">Senha:</label>
                <input type="password" id="senha" name="senha" class="form-control">
                <i class="fas fa-eye toggle-password" id="togglePassword"></i>
            </div>
            <button type="submit" class="btn btn-primary">Atualizar</button>
            <br><br>
            <a href="dashboard.php" class="btn btn-secondary">Voltar</a>
        </form>
    </div>

    <!-- Scripts do Bootstrap e JavaScript para a funcionalidade de mostrar/ocultar a senha -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
        document.getElementById('togglePassword').addEventListener('click', function () {
            const passwordField = document.getElementById('senha');
            const type = passwordField.type === 'password' ? 'text' : 'password';
            passwordField.type = type;
            this.classList.toggle('fa-eye-slash');
        });
    </script>
</body>
</html>
