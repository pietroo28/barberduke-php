<?php
require '../banco.php';
header("Content-Type: application/json; charset=UTF-8");

$data = json_decode(file_get_contents("php://input"), true);
$acao = $data['acao'] ?? null;

if ($acao === 'cancelar') {
    $id = $data['id'];
    $sql = $pdo->prepare("UPDATE agendamentos SET status='Cancelado', cor='#ff0000' WHERE id=?");
    $sql->execute([$id]);
    echo json_encode(["mensagem" => "Agendamento cancelado com sucesso."]);
}

elseif ($acao === 'concluir') {
    $id = $data['id'];
    $sql = $pdo->prepare("UPDATE agendamentos SET status='Concluído', cor='#00cc66' WHERE id=?");
    $sql->execute([$id]);
    echo json_encode(["mensagem" => "Agendamento concluído."]);
}

elseif ($acao === 'bloquear') {
    $barbeiro_id = $data['barbeiro_id'];
    $data_b = $data['data'];
    $hora_inicio = $data['hora_inicio'];
    $hora_fim = $data['hora_fim'];
    $sql = $pdo->prepare("INSERT INTO agendamentos (barbeiro_id, cliente_id, data, horario_inicio, horario_fim, status, cor) VALUES (?, 3, ?, ?, ?, 'Bloqueado', '#555')");
    $sql->execute([$barbeiro_id, $data_b, $hora_inicio, $hora_fim]);
    echo json_encode(["mensagem" => "Horário bloqueado com sucesso."]);
}
?>
