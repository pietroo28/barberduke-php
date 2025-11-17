<?php
header('Content-Type: application/json');
include '../conexao.php'; // já define $pdo

$input = json_decode(file_get_contents('php://input'), true);
$barbeiro_id = $input['barbeiro_id'] ?? null;
$data = $input['data'] ?? null;
$horario = $input['horario'] ?? null;

if (!$barbeiro_id || !$data || !$horario) {
    echo json_encode(['status'=>false,'mensagem'=>'Barbeiro, data e horário inicial são obrigatórios']);
    exit;
}

try {
    $stmt = $pdo->prepare("DELETE FROM agendamentos WHERE usuario_id = 3 AND barbeiro_id = ? AND data = ? AND horario = ?");
    $stmt->execute([$barbeiro_id, $data, $horario]);

    echo json_encode(['status'=>true,'mensagem'=>'Bloqueio removido com sucesso']);
} catch (Exception $e) {
    echo json_encode(['status'=>false,'mensagem'=>$e->getMessage()]);
}
?>
