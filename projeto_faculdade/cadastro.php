<?php
// Conectar ao banco de dados
$mysqli = new mysqli("localhost", "root", "", "sistema_gcmsp");

if ($mysqli->connect_error) {
    die("Falha na conexão: " . $mysqli->connect_error);
}

// Verificar se o formulário foi enviado
if ($_SERVER['REQUEST_METHOD'] == 'POST') { // Corrigido aqui
    // Capturar os dados do formulário e escapar para evitar injeção de SQL
    $nome = $mysqli->real_escape_string($_POST['nome']);
    $re = $mysqli->real_escape_string($_POST['re']); // Captura o RE
    $cpf = $mysqli->real_escape_string($_POST['cpf']);
    $rg = $mysqli->real_escape_string($_POST['rg']);
    $data_nascimento = $mysqli->real_escape_string($_POST['data_nascimento']);
    $data_incorporacao = $mysqli->real_escape_string($_POST['data_incorporacao']);
    $email = $mysqli->real_escape_string($_POST['email']);
    $senha = password_hash($_POST['senha'], PASSWORD_DEFAULT); // Hashear a senha para segurança
    $qra = $mysqli->real_escape_string($_POST['qra']);
    $graduacao = $mysqli->real_escape_string($_POST['graduacao']);

    // Verificar se o CPF já está cadastrado
    $stmt = $mysqli->prepare("SELECT id FROM usuarios WHERE cpf = ?");
    $stmt->bind_param('s', $cpf);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        // CPF já está cadastrado
        $erro = "O CPF já está cadastrado!";
    } else {
        // Inserir dados na tabela 'usuarios'
        $stmt = $mysqli->prepare("
            INSERT INTO usuarios (nome, re, cpf, rg, data_nascimento, data_incorporacao, email, senha, qra, graduacao) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->bind_param('ssssssssss', $nome, $re, $cpf, $rg, $data_nascimento, $data_incorporacao, $email, $senha, $qra, $graduacao);

        if ($stmt->execute()) {
            // Cadastro realizado com sucesso
            header("Location: login.php");
            exit();
        } else {
            // Falha ao cadastrar
            $erro = "Falha ao cadastrar. Por favor, tente novamente.";
        }
    }
    
    // Fechar a conexão
    $stmt->close();
    $mysqli->close();
}
?>



<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro - Sistema GMCSP</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container mt-5">
        <h1 class="text-center">Cadastro</h1>
        <?php if (isset($erro)): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($erro); ?></div>
        <?php endif; ?>
        <form action="cadastro.php" method="POST">
            <div class="form-group">
                <label for="nome">Nome Completo:</label>
                <input type="text" id="nome" name="nome" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="re">RE:</label>
                <input type="text" id="re" name="re" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="cpf">CPF:</label>
                <input type="text" id="cpf" name="cpf" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="rg">RG:</label>
                <input type="text" id="rg" name="rg" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="data_nascimento">Data de Nascimento:</label>
                <input type="date" id="data_nascimento" name="data_nascimento" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="data_incorporacao">Data de Incorporação:</label>
                <input type="date" id="data_incorporacao" name="data_incorporacao" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="email">E-mail:</label>
                <input type="email" id="email" name="email" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="senha">Senha:</label>
                <input type="password" id="senha" name="senha" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="qra">QRA:</label>
                <input type="text" id="qra" name="qra" class="form-control">
            </div>
            
            <div class="form-group">
                <label for="graduacao">Graduação:</label>
                <select id="graduacao" name="graduacao" class="form-control">
                    <option value=""></option>
                    <option value="3ª Classe">3ª Classe</option>
                    <option value="2ª Classe">2ª Classe</option>
                    <option value="1ª Classe">1ª Classe</option>
                    <option value="Classe Distinta">Classe Distinta</option>
                    <option value="Subinspetor">Subinspetor</option>
                    <option value="Inspetor">Inspetor</option>
                    <option value="Subcomandante">Subcomandante</option>
                    <option value="Comandante">Comandante</option>
                </select>
            </div>
            
            <button type="submit" class="btn btn-primary">Cadastrar</button>
            <a href="login.php" class="btn btn-secondary">Voltar</a>
        </form>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>