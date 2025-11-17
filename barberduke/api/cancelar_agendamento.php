<?php
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");

include __DIR__ . '/../conexao.php';

// Captura dados do JSON enviado
$data = json_decode(file_get_contents("php://input"), true);

$agendamento_id = $data['agendamento_id'] ?? 0;
$cpf = $data['cpf'] ?? '';

if (!$agendamento_id) {
    echo json_encode(['success' => false, 'error' => 'ID do agendamento não fornecido.']);
    exit;
}

if (!$cpf) {
    echo json_encode(['success' => false, 'error' => 'CPF não fornecido.']);
    exit;
}

try {
    // Busca o usuário pelo CPF
    $stmtUser = $pdo->prepare("SELECT id FROM usuarios WHERE cpf = ?");
    $stmtUser->execute([$cpf]);
    $usuario = $stmtUser->fetch(PDO::FETCH_ASSOC);

    if (!$usuario) {
        echo json_encode(['success' => false, 'error' => 'Usuário não encontrado.']);
        exit;
    }

    $usuario_id = $usuario['id'];

    // Confirma se o agendamento pertence ao usuário
    $stmt = $pdo->prepare("SELECT id FROM agendamentos WHERE id = ? AND usuario_id = ?");
    $stmt->execute([$agendamento_id, $usuario_id]);

    if (!$stmt->fetch()) {
        echo json_encode(['success' => false, 'error' => 'Agendamento não encontrado.']);
        exit;
    }

    // Remove procedimentos associados
    $stmt = $pdo->prepare("DELETE FROM agendamento_procedimentos WHERE agendamento_id = ?");
    $stmt->execute([$agendamento_id]);

    // Remove agendamento
    $stmt = $pdo->prepare("DELETE FROM agendamentos WHERE id = ?");
    $stmt->execute([$agendamento_id]);

    echo json_encode(['success' => true, 'message' => 'Agendamento cancelado com sucesso.']);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
