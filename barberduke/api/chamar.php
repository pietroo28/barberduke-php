<?php
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");

// Corrige o caminho do include
include __DIR__ . '/../conexao.php';

$cpf = isset($_GET['cpf']) ? trim($_GET['cpf']) : '';

if (empty($cpf)) {
    echo json_encode(['nome' => null, 'erro' => 'CPF não informado']);
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT nome FROM usuarios WHERE cpf = :cpf LIMIT 1");
    $stmt->execute(['cpf' => $cpf]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($usuario) {
        echo json_encode(['nome' => $usuario['nome']]);
    } else {
        echo json_encode(['nome' => null, 'erro' => 'Usuário não encontrado']);
    }

} catch (Exception $e) {
    echo json_encode(['nome' => null, 'erro' => $e->getMessage()]);
}
