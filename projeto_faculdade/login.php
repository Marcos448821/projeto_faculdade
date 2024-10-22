<?php
session_start(); // Iniciar a sessão

// Função para obter o IP do usuário
function getUserIP() {
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        return $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        return $_SERVER['HTTP_X_FORWARDED_FOR'];
    } else {
        return $_SERVER['REMOTE_ADDR'];
    }
}

// Conectar ao banco de dados
$mysqli = new mysqli("localhost", "root", "", "sistema_gcmsp");

if ($mysqli->connect_error) {
    die("Conexão falhou: " . $mysqli->connect_error);
}

// Verificar se o formulário foi enviado
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $cpf = preg_replace('/[^0-9]/', '', $_POST['cpf']); // Limpa o CPF
    $senha = $_POST['senha'];
    $ip = getUserIP(); // Captura o IP do usuário

    // Consultar o usuário
    $stmt = $mysqli->prepare("SELECT * FROM usuarios WHERE cpf = ?");
    $stmt->bind_param("s", $cpf);
    $stmt->execute();
    $result = $stmt->get_result();
    $usuario = $result->fetch_assoc();

    // Verificar a senha
    if ($usuario && password_verify($senha, $usuario['senha'])) {
        $_SESSION['cpf'] = $cpf;
        $_SESSION['usuario_id'] = $usuario['id']; // Armazena o ID do usuário na sessão
        $_SESSION['usuario_nome'] = $usuario['nome']; // Armazena o nome do usuário na sessão

        // Atualiza o IP do usuário na tabela
        $update_stmt = $mysqli->prepare("UPDATE usuarios SET ip_login = ? WHERE cpf = ?");
        $update_stmt->bind_param("ss", $ip, $cpf);
        $update_stmt->execute();

        header("Location: dashboard.php");
        exit();
    } else {
        $erro = "CPF ou senha inválidos.";
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
    <title>Login - Sistema GMCSP</title>
    
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            background-image: url('uploads/digital_4.jpg');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
        }

        .login-container {
            background-color: rgba(255, 255, 255, 0.8); /* Fundo branco com transparência */
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0px 4px 12px rgba(0, 0, 0, 0.1);
            max-width: 350px;
            width: 100%;
            
            
        }

        .password-container {
            position: relative;
        }

        .password-container input {
            padding-right: 2.5rem;
        }

        .password-container .toggle-password {
            position: absolute;
            right: 0.5rem;
            top: 75%;
            transform: translateY(-50%);
            cursor: pointer;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .alert {
            margin-top: 1rem;
        }

        .btn {
            width: 100%;
        }

        h1 {
            text-align: center;
            margin-bottom: 1.5rem;
        }
    </style>
</head>
<body>
    <div class="login-container">
    <center><img src="uploads/BandeiraParnaíba.png" alt="Imagem do Usuário" style="max-height: 90px; width: auto; margin-bottom: 1rem;)"><c/center>        
    <h3>Guarda Civil Municipal</h3>
    <center><p> Santana de Parnaíba - SP </p></center>
        <?php if (isset($erro)): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($erro); ?></div>
        <?php endif; ?>
        <form action="login.php" method="POST" id="loginForm">
            <div class="form-group">
            <strong><label for="cpf">CPF:</label></strong>
                <input type="text" id="cpf" name="cpf" class="form-control" placeholder="000.000.000-00" required>
            </div>
            <div class="form-group password-container">
            <strong><label for="senha">Senha:</label></strong>
                <input type="password" id="senha" name="senha" class="form-control" placeholder="Digite sua senha" required>
                <i class="fas fa-eye toggle-password" id="togglePassword"></i>
            </div>
            <button type="submit" class="btn btn-info"><b>Entrar<b></button>
            <br><br>
            
            <a href="reset_senha.php" class="btn btn-link">Esqueci minha senha</a>
            
            
        </form>
    </div>

    <!-- Scripts do Bootstrap e JavaScript para a funcionalidade de mostrar/ocultar a senha -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    
    <!-- Máscara para o CPF -->
    <script>
        document.getElementById('cpf').addEventListener('input', function (e) {
            let v = e.target.value.replace(/\D/g, "");
            v = v.replace(/(\d{3})(\d)/, "$1.$2");
            v = v.replace(/(\d{3})(\d)/, "$1.$2");
            v = v.replace(/(\d{3})(\d{1,2})$/, "$1-$2");
            e.target.value = v;
        });

        // Alternar visibilidade da senha
        document.getElementById('togglePassword').addEventListener('click', function () {
            const passwordField = document.getElementById('senha');
            const type = passwordField.type === 'password' ? 'text' : 'password';
            passwordField.type = type;
            this.classList.toggle('fa-eye-slash');
        });
    </script>
</body>
</html>
