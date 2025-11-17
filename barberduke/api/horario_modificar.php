<?php
date_default_timezone_set('America/Sao_Paulo');
header("Content-Type: application/json; charset=UTF-8");
include __DIR__ . '/../conexao.php';

$input = json_decode(file_get_contents('php://input'), true);

$barbeiro = $input['barbeiro'] ?? null;
$dia = $input['dia'] ?? null;
$inicio = $input['inicio_expediente'] ?? null;
$fim = $input['fim_expediente'] ?? null;
$almoco_inicio = $input['almoco_inicio'] ?? null;
$almoco_fim = $input['almoco_fim'] ?? null;
$ativo = isset($input['ativo']) ? (int)$input['ativo'] : 1;

if (!$barbeiro || !$dia || !$inicio || !$fim || !$almoco_inicio || !$almoco_fim) {
    echo json_encode(['erro' => 'Parâmetros incompletos']);
    exit;
}

try {
    // Verifica se já existe horário para esse barbeiro e dia
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM horarios_barbeiro WHERE barbeiro_id = :barbeiro AND dia_semana = :dia");
    $stmt->execute(['barbeiro' => $barbeiro, 'dia' => $dia]);
    $exists = $stmt->fetchColumn();

    if ($exists) {
        // Atualiza
        $stmt = $pdo->prepare("
            UPDATE horarios_barbeiro
            SET inicio_expediente = :inicio, fim_expediente = :fim,
                almoco_inicio = :almoco_inicio, almoco_fim = :almoco_fim,
                ativo = :ativo
            WHERE barbeiro_id = :barbeiro AND dia_semana = :dia
        ");
    } else {
        // Insere
        $stmt = $pdo->prepare("
            INSERT INTO horarios_barbeiro 
            (barbeiro_id, dia_semana, inicio_expediente, fim_expediente, almoco_inicio, almoco_fim, ativo)
            VALUES (:barbeiro, :dia, :inicio, :fim, :almoco_inicio, :almoco_fim, :ativo)
        ");
    }

    $stmt->execute([
        'barbeiro' => $barbeiro,
        'dia' => $dia,
        'inicio' => $inicio,
        'fim' => $fim,
        'almoco_inicio' => $almoco_inicio,
        'almoco_fim' => $almoco_fim,
        'ativo' => $ativo
    ]);

    echo json_encode(['sucesso' => true]);
} catch (PDOException $e) {
    echo json_encode(['erro' => $e->getMessage()]);
}
