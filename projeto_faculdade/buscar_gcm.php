<?php
// Conectar ao banco de dados
$mysqli = new mysqli("localhost", "root", "", "sistema_gcmsp");

if ($mysqli->connect_error) {
    die("Conexão falhou: " . $mysqli->connect_error);
}

$query = isset($_POST['query']) ? $mysqli->real_escape_string($_POST['query']) : '';

if (!empty($query)) {
    $sql = "SELECT qra FROM usuarios WHERE qra LIKE ?";
    $stmt = $mysqli->prepare($sql);
    $search_query = "%{$query}%";
    $stmt->bind_param("s", $search_query);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            echo "<div class='list-group-item suggestion-item' data-qra='" . htmlspecialchars($row['qra']) . "'>" . htmlspecialchars($row['qra']) . "</div>";
        }
    } else {
        echo "<div class='list-group-item'>Nenhum GCM encontrado.</div>";
    }
} else {
    echo "<div class='list-group-item'>Por favor, insira um QRA para buscar.</div>";
}

$mysqli->close();
?>
