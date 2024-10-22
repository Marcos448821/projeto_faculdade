<?php
// Iniciar a sessão
session_start();
date_default_timezone_set('America/Sao_Paulo');
// Verificar se o usuário está autenticado
if (!isset($_SESSION['cpf'])) {
    header("Location: login.php");
    exit();
}

// Conectar ao banco de dados
$mysqli = new mysqli("localhost", "root", "", "sistema_gcmsp");

// Verificar a conexão
if ($mysqli->connect_error) {
    die("Conexão falhou: " . $mysqli->connect_error);
}

// Obter o nome do usuário, IP de login e último acesso
$cpf = $_SESSION['cpf'];
$query = "SELECT nome, ip_login, ultimo_acesso, re, graduacao, qra FROM usuarios WHERE cpf = ?";
$stmt = $mysqli->prepare($query);

if (!$stmt) {
    die("Erro na preparação da declaração: " . $mysqli->error);
}

$stmt->bind_param("s", $cpf);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// Atualizar IP de login e último acesso
$ip_login = $_SERVER['REMOTE_ADDR'];
$ultimo_acesso = date('Y-m-d H:i:s');

$update_query = "UPDATE usuarios SET ip_login = ?, ultimo_acesso = ? WHERE cpf = ?";
$update_stmt = $mysqli->prepare($update_query);

if (!$update_stmt) {
    die("Erro na preparação da declaração de atualização: " . $mysqli->error);
}

$update_stmt->bind_param("sss", $ip_login, $ultimo_acesso, $cpf);
$update_stmt->execute();

// Obter as funcionalidades permitidas para o usuário
$usuario_id = $mysqli->query("SELECT id FROM usuarios WHERE cpf = '$cpf'")->fetch_assoc()['id'];
$funcionalidades_permitidas = [];
$result = $mysqli->query("SELECT funcionalidade_id FROM usuarios_funcionalidades WHERE usuario_id = $usuario_id");
while ($row = $result->fetch_assoc()) {
    $funcionalidades_permitidas[] = $row['funcionalidade_id'];
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Sistema GMCSP</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.10.0/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="css/style.css">
    <style>
        body {
            width: 100%;
            height: 100%;           
            font-family: Arial, sans-serif;
            background-image: url('uploads/guarda_2.jpg');
            background-size: cover;
            -webkit-background-size: cover;
            -moz-background-size: cover;
            background-size: cover;
            -o-background-size: cover;
            
            
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem;
            background-color: #343a40;
            color: whitesmoke;
            position: relative;
            z-index: 1;
            

        }
        .header h1 {
            margin: 0;
        }
        .logout-btn {
            color: white;
            font-size: 0.9rem;
        }
        .logout-btn:hover {
            color: #ffc107;
        }
        .dashboard-container {
            display: flex;
            justify-content: space-around;
            align-items: right;
            flex-wrap: wrap;
            gap: 25px;
            margin-top: 2rem;                                       
            
        }
        .dashboard-icon {
            font-size: 4rem;
            width: 100px;
            height: 80px;
            display: flex;
            justify-content: center;
            align-items: center;
            margin: 0 auto 0px;          
           
        }
        .dashboard-item {
            text-align: center;                  
            margin-bottom: 1rem;
            flex: 1;
            max-width: 150px;            
        }
        .dashboard-item a {
            text-decoration: none;
            color: whitesmoke;
        }
        .dashboard-item:hover .dashboard-icon {           
            transform: scale(1.5);
            transition: transform 0.7s ease; 
            color: white;         
        }
        
    </style>
</head>
<body>
<header class="header">
    <h1>Bem-vindo, <?php echo htmlspecialchars($user['nome']); ?></h1>
    <div>
        <a href="editar_cadastro.php" class="btn btn-warning">
            <i class="bi bi-pencil-square"></i> Editar
        </a>
        <a href="logout.php" class="btn btn-danger logout-btn ml-2">
            <i class="bi bi-box-arrow-right"></i> Sair
        </a>
    </div>
</header>

    <div class="container">
        <div class="dashboard-container">
            <!-- Controle de Viaturas -->
            <?php if (in_array(1, $funcionalidades_permitidas)): ?>
                <div class="dashboard-item">
                    <a href="controle_viaturas.php" class="btn btn-inline-primary">
                        <div class="dashboard-icon">
                            <i class="bi bi-car-front-fill"></i>
                        </div>
                        <p>Gestão de Frota</p>
                    </a>
                </div>
            <?php endif; ?>
            <!-- Mapa Força -->
            <?php if (in_array(2, $funcionalidades_permitidas)): ?>
                <div class="dashboard-item">
                    <a href="escala_mensal.php" class="btn btn-inline-primary">
                        <div class="dashboard-icon">
                            <i class="bi bi-person-plus"></i>
                        </div>                        
                        <p>Escalas Mensais de Serviço</p>
                    </a>
                </div>
            <?php endif; ?>            
            <!-- Gestão de Modalidades -->
            <?php if (in_array(5, $funcionalidades_permitidas)): ?>
                <div class="dashboard-item">
                    <a href="gerenciar_modalidades_servico.php" class="btn btn-inline-primary">
                        <div class="dashboard-icon">
                            <i class="bi bi-sticky"></i>
                        </div>
                        <p>Modalidades de Serviços</p>
                    </a>
                </div>
            <?php endif; ?>
            <!-- Manutenção de Viaturas -->
            <?php if (in_array(6, $funcionalidades_permitidas)): ?>
                <div class="dashboard-item">
                    <a href="gerenciar_postos_servico.php" class="btn btn-inline-primary">
                        <div class="dashboard-icon">
                            <i class="bi bi-wrench-adjustable"></i>
                        </div>                        
                        <p>Postos de Serviço</p>
                    </a>
                </div>
            <?php endif; ?> 
            
                    
            
        </div>        
        </div>
    
</div> 
  
    

    <!-- Scripts do Bootstrap -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
