<?php
header('Content-Type: application/json');
include '../conexao.php'; // já define $pdo

$input = json_decode(file_get_contents('php://input'), true);

$barbeiro_id = $input['barbeiro_id'] ?? null;
$data = $input['data'] ?? null;
$hora_inicio = $input['hora_inicio'] ?? null;
$hora_fim = $input['hora_fim'] ?? null;

if (!$barbeiro_id || !$data || !$hora_inicio || !$hora_fim) {
    echo json_encode(['status' => false, 'mensagem' => 'Todos os campos são obrigatórios']);
    exit;
}

try {
    $hora_atual = strtotime($hora_inicio);
    $hora_final = strtotime($hora_fim);

    if ($hora_final <= $hora_atual) {
        throw new Exception("Horário final deve ser maior que o horário inicial.");
    }

    // Calcula duração em minutos
    $duracao = ($hora_final - $hora_atual) / 60; 

    $pdo->beginTransaction();

    // Verifica se já existe bloqueio no intervalo
    $check = $pdo->prepare("
        SELECT COUNT(*) FROM agendamentos
        WHERE barbeiro_id = ?
        AND data = ?
        AND horario BETWEEN ? AND ?
    ");
    $check->execute([$barbeiro_id, $data, $hora_inicio, $hora_fim]);
    $exists = $check->fetchColumn();

    if ($exists > 0) {
        throw new Exception("Já existe um agendamento ou bloqueio nesse intervalo.");
    }

    // ✅ Aqui é o ponto crucial — muda o status para 'bloqueado'
    $stmt = $pdo->prepare("
        INSERT INTO agendamentos (usuario_id, barbeiro_id, data, horario, duracao, status)
        VALUES (3, ?, ?, ?, ?, 'bloqueado')
    ");
    $stmt->execute([$barbeiro_id, $data, $hora_inicio, $duracao]);

    $pdo->commit();

    echo json_encode([
        'status' => true,
        'mensagem' => 'Bloqueio adicionado com sucesso!',
        'duracao' => $duracao . ' minutos'
    ]);

} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(['status' => false, 'mensagem' => $e->getMessage()]);
}
