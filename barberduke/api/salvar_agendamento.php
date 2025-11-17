<?php
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");

include __DIR__ . '/../conexao.php';

$data = json_decode(file_get_contents("php://input"), true);

// Captura o CPF da URL
$cpf = $_GET['cpf'] ?? '';

$barbeiro = $data['barbeiro'] ?? 0;
$data_agenda = $data['data'] ?? '';
$horario = $data['horario'] ?? '';
$procedimentos = $data['procedimentos'] ?? [];

if (!$barbeiro || !$data_agenda || !$horario || empty($procedimentos)) {
    echo json_encode(['success' => false, 'error' => 'Campos obrigatórios faltando.']);
    exit;
}

try {
    if (!$cpf) {
        echo json_encode(['success' => false, 'error' => 'CPF não informado na URL.']);
        exit;
    }

    // Busca o usuário pelo CPF
    $stmtUser = $pdo->prepare("SELECT id FROM usuarios WHERE cpf = ?");
    $stmtUser->execute([$cpf]);
    $usuario = $stmtUser->fetch(PDO::FETCH_ASSOC);

    if (!$usuario) {
        echo json_encode(['success' => false, 'error' => 'Usuário não encontrado para o CPF informado.']);
        exit;
    }

    $usuario_id = $usuario['id'];

    // Verifica se o usuário já tem 2 agendamentos confirmados no futuro
    $stmtCheck = $pdo->prepare("SELECT COUNT(*) FROM agendamentos WHERE usuario_id = ? AND status = 'confirmado' AND data >= CURDATE()");
    $stmtCheck->execute([$usuario_id]);
    $qtdAgendamentos = $stmtCheck->fetchColumn();

    if ($qtdAgendamentos >= 2) {
        echo json_encode(['success' => false, 'error' => 'Você já possui 2 agendamentos confirmados.']);
        exit;
    }

    // Calcular duração total dos procedimentos
    $inQuery = str_repeat('?,', count($procedimentos) - 1) . '?';
    $stmt = $pdo->prepare("SELECT SUM(duracao) as total_duracao FROM procedimentos WHERE id IN ($inQuery)");
    $stmt->execute($procedimentos);
    $total_duracao = $stmt->fetchColumn();

    // Salvar agendamento
    $stmt = $pdo->prepare("INSERT INTO agendamentos (usuario_id, barbeiro_id, data, horario, duracao, status) VALUES (?, ?, ?, ?, ?, ?)");
    $status = 'confirmado';
    $stmt->execute([$usuario_id, $barbeiro, $data_agenda, $horario, $total_duracao, $status]);

    // Salvar procedimentos associados
    $agendamento_id = $pdo->lastInsertId();
    $stmtProc = $pdo->prepare("INSERT INTO agendamento_procedimentos (agendamento_id, procedimento_id) VALUES (?, ?)");
    foreach ($procedimentos as $p) {
        $stmtProc->execute([$agendamento_id, $p]);
    }

    echo json_encode(['success' => true, 'message' => 'Agendamento realizado com sucesso.']);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
