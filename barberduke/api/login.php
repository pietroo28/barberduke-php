<?php
session_start();
require_once '../conexao.php'; // ajuste o caminho se necessário

$cpf = trim($_POST['cpf'] ?? '');

if (!$cpf) {
    $_SESSION['error'] = "CPF é obrigatório.";
    header("Location: /barberduke/templates/login.php");
    exit();
}

try {
    // Verifica se é barbeiro (admin)
    $stmt = $pdo->prepare("SELECT * FROM barbeiros WHERE cpf = ?");
    $stmt->execute([$cpf]);
    $barbeiro = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($barbeiro) {
        $_SESSION['cpf'] = $cpf;
        $_SESSION['tipo'] = 'admin';
        $_SESSION['success'] = "Login realizado com sucesso!";
        header("Location: /barberduke/templates/admin.html");
        exit();
    }

    // Verifica se é cliente
    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE cpf = ?");
    $stmt->execute([$cpf]);
    $cliente = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($cliente) {
        $_SESSION['cpf'] = $cpf;
        $_SESSION['tipo'] = 'cliente';
        $_SESSION['success'] = "Login realizado com sucesso!";
        header("Location: /barberduke/templates/agendar.html?cpf=" . urlencode($cpf));
        exit();
    }

    // CPF não encontrado
    $_SESSION['error'] = "CPF não cadastrado.";
    header("Location: /barberduke/templates/login.php");
    exit();

} catch (PDOException $e) {
    $_SESSION['error'] = "Não foi possível conectar ao banco: " . $e->getMessage();
    header("Location: /barberduke/templates/login.php");
    exit();
}
?>
