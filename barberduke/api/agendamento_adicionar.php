<?php
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");

require_once __DIR__ . '/../conexao.php'; // inclui sua conexão $pdo

try {
    $data = json_decode(file_get_contents("php://input"), true);

    if (!$data) {
        echo json_encode(["success" => false, "message" => "Nenhum dado recebido."]);
        exit;
    }

    $barbeiro_id = $data["barbeiro_id"] ?? null;
    $cliente_nome = $data["cliente"] ?? null; // nome do usuário
    $procedimento_id = $data["procedimento"] ?? null; // agora é o ID do procedimento
    $data_agendamento = $data["data"] ?? null;
    $hora_inicio = $data["hora_inicio"] ?? null;
    $duracao = $data["duracao"] ?? null; // duração em minutos
    $status = $data["status"] ?? "confirmado";

    // Verifica campos obrigatórios
    if (!$barbeiro_id || !$cliente_nome || !$procedimento_id || !$data_agendamento || !$hora_inicio || !$duracao) {
        echo json_encode(["success" => false, "message" => "Campos obrigatórios ausentes."]);
        exit;
    }

    // Inicia transação para garantir integridade dos dados
    $pdo->beginTransaction();

    // 1️⃣ Criar o usuário novo na tabela usuarios
    $stmtUsuario = $pdo->prepare("INSERT INTO usuarios (nome) VALUES (?)");
    $stmtUsuario->execute([$cliente_nome]);
    $usuario_id = $pdo->lastInsertId(); // pega o ID do usuário criado

    // 2️⃣ Calcular horário final (opcional, se quiser usar)
    $inicio = new DateTime($hora_inicio);
    $fimDate = clone $inicio;
    $fimDate->modify("+$duracao minutes");
    $hora_fim = $fimDate->format('H:i');

    // 3️⃣ Criar agendamento sem o campo procedimento
    $stmtAgendamento = $pdo->prepare("
        INSERT INTO agendamentos (usuario_id, barbeiro_id, data, horario, status, duracao)
        VALUES (?, ?, ?, ?, ?, ?)
    ");
    $stmtAgendamento->execute([
        $usuario_id,
        $barbeiro_id,
        $data_agendamento,
        $hora_inicio,
        $status,
        $duracao
    ]);
    $agendamento_id = $pdo->lastInsertId();

    // 4️⃣ Inserir relacionamento na tabela agendamento_procedimentos
    $stmtProcedimento = $pdo->prepare("
        INSERT INTO agendamento_procedimentos (agendamento_id, procedimento_id)
        VALUES (?, ?)
    ");
    $stmtProcedimento->execute([
        $agendamento_id,
        $procedimento_id
    ]);

    // Confirma transação
    $pdo->commit();

    echo json_encode(["success" => true]);

} catch (PDOException $e) {
    // Se erro, desfaz a transação
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
} catch (Throwable $t) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo json_encode(["success" => false, "message" => $t->getMessage()]);
}
?>
