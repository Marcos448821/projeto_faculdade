<?php
$sucesso = isset($_GET['sucesso']) && $_GET['sucesso'] == 1;
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmação - GCM Incluído</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
    <div class="container mt-5">
        <?php if ($sucesso): ?>
            <div class="alert alert-success" role="alert">
                GCM incluído na escala com sucesso!
            </div>
        <?php else: ?>
            <div class="alert alert-danger" role="alert">
                Ocorreu um erro ao incluir o GCM na escala.
            </div>
        <?php endif; ?>

        <a href="criar_escala.php" class="btn btn-primary">Voltar para Escalas</a>
    </div>

    <!-- Scripts do Bootstrap -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
