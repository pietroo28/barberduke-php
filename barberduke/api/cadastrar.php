<?php
session_start();
require_once '../conexao.php'; // ajuste o caminho se necessário

// Pega os dados do POST
$nome = trim($_POST['nome'] ?? '');
$cpf = trim($_POST['cpf'] ?? '');
$telefone = trim($_POST['telefone'] ?? '');

// Validação básica
if (!$nome || !$cpf || !$telefone) {
    $_SESSION['error'] = 'Todos os campos são obrigatórios.';
    header("Location: /barberduke/templates/login.php");
    exit();
}

// Validação de CPF (apenas números, 11 dígitos)
if (!preg_match('/^\d{11}$/', $cpf)) {
    $_SESSION['error'] = 'CPF inválido. Deve conter 11 números.';
    header("Location: /barberduke/templates/login.php");
    exit();
}

try {
    // Verifica se o CPF já existe
    $stmt = $pdo->prepare("SELECT 1 FROM usuarios WHERE cpf = ?");
    $stmt->execute([$cpf]);

    if ($stmt->fetch()) {
        $_SESSION['error'] = 'CPF já cadastrado.';
        header("Location: /barberduke/templates/login.php");
        exit();
    }

    // Insere no banco
    $stmt = $pdo->prepare("INSERT INTO usuarios (nome, cpf, telefone) VALUES (?, ?, ?)");
    $stmt->execute([$nome, $cpf, $telefone]);

    // Salva sessão
    $_SESSION['cpf'] = $cpf;
    $_SESSION['tipo'] = 'usuario';
    $_SESSION['success'] = 'Cadastro realizado com sucesso!';

    // Redireciona para agendamento
    header("Location: /barberduke/templates/agendar.html?cpf=" . urlencode($cpf));
    exit();

} catch (PDOException $e) {
    // Log do erro para debug (não exibir ao usuário)
    error_log("Erro ao cadastrar cliente: " . $e->getMessage());
    $_SESSION['error'] = 'Erro ao cadastrar cliente. Tente novamente mais tarde.';
    header("Location: /barberduke/templates/login.php");
    exit();
}
?>
