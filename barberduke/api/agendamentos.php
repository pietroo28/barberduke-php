<?php
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");

include __DIR__ . '/../conexao.php';

// Recebe CPF via GET
$cpf = isset($_GET['cpf']) ? $_GET['cpf'] : '';

if (!$cpf) {
    echo json_encode([]);
    exit;
}

try {
    // Pega o ID do usuário pelo CPF
    $stmt = $pdo->prepare("SELECT id, nome FROM usuarios WHERE cpf = :cpf");
    $stmt->execute(['cpf' => $cpf]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$usuario) {
        echo json_encode([]);
        exit;
    }

    $usuario_id = $usuario['id'];

    // Busca agendamentos futuros do usuário
    $stmt = $pdo->prepare("
        SELECT a.id, a.data, a.horario, a.status, b.nome AS barbeiro
        FROM agendamentos a
        JOIN barbeiros b ON a.barbeiro_id = b.id
        WHERE a.usuario_id = :usuario_id
          AND a.data >= CURDATE()
        ORDER BY a.data, a.horario
    ");
    $stmt->execute(['usuario_id' => $usuario_id]);
    $agendamentos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $resultado = [];

    foreach ($agendamentos as $ag) {
        // Busca procedimentos relacionados ao agendamento
        $stmt2 = $pdo->prepare("
            SELECT p.nome
            FROM agendamento_procedimentos ap
            JOIN procedimentos p ON ap.procedimento_id = p.id
            WHERE ap.agendamento_id = :agendamento_id
        ");
        $stmt2->execute(['agendamento_id' => $ag['id']]);
        $procedimentos = $stmt2->fetchAll(PDO::FETCH_COLUMN);

        $resultado[] = [
            'id' => $ag['id'],
            'data' => $ag['data'],
            'horario' => $ag['horario'],
            'barbeiro' => $ag['barbeiro'],
            'status' => $ag['status'],
            'procedimentos' => implode(", ", $procedimentos)
        ];
    }

    echo json_encode($resultado);

} catch (PDOException $e) {
    echo json_encode([]);
}
?>
