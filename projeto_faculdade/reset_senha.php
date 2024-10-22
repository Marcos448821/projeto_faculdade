<?php
// Conectar ao banco de dados
$mysqli = new mysqli("localhost", "root", "", "sistema_gcmsp");

if ($mysqli->connect_error) {
    die("Conexão falhou: " . $mysqli->connect_error);
}

// Verificar se o formulário foi enviado
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $cpf = $_POST['cpf'];

    // Verificar se o CPF existe no banco de dados
    $stmt = $mysqli->prepare("SELECT * FROM usuarios WHERE cpf = ?");
    $stmt->bind_param("s", $cpf);
    $stmt->execute();
    $result = $stmt->get_result();
    $usuario = $result->fetch_assoc();

    if ($usuario) {
        // Gerar um token para o reset de senha (para simplicidade, não está implementado aqui)
        $token = bin2hex(random_bytes(16));
        // Armazenar o token no banco de dados (precisaria criar uma tabela para isso)

        // Enviar email para o usuário com o link para resetar a senha (não implementado aqui)
        // mail($usuario['email'], 'Redefinir Senha', 'Seu token: ' . $token);

        echo "<div class='alert alert-success'>Instruções para redefinir a senha foram enviadas para seu email.</div>";
    } else {
        echo "<div class='alert alert-danger'>CPF não encontrado.</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resetar Senha</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container mt-5">
        <h1 class="text-center">Esqueci minha senha</h1>
        <form action="reset_senha.php" method="POST">
            <div class="form-group">
                <label for="cpf">CPF:</label>
                <input type="text" id="cpf" name="cpf" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary">Enviar instruções</button>
            <br><br>
            <a href="login.php" class="btn btn-secondary">Voltar para Login</a>
        </form>
    </div>

    <!-- Scripts do Bootstrap -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
