<?php
date_default_timezone_set('America/Sao_Paulo');

header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");

include __DIR__ . '/../conexao.php';

// Recebe parâmetros
$barbeiro = isset($_GET['barbeiro']) ? (int) $_GET['barbeiro'] : 0;
$data = isset($_GET['data']) ? $_GET['data'] : '';
$duracao_procedimento = isset($_GET['duracao']) ? (int) $_GET['duracao'] : 30;

if (!$barbeiro || !$data) {
    echo json_encode([]);
    exit;
}

// Dia da semana (0=domingo ... 6=sábado)
$diaSemana = date('w', strtotime($data));

try {
    // ===============================
    // 1. Buscar horário do barbeiro
    // ===============================
    $stmt = $pdo->prepare("
        SELECT inicio_expediente, fim_expediente, almoco_inicio, almoco_fim, ativo
        FROM horarios_barbeiro
        WHERE barbeiro_id = :barbeiro
          AND dia_semana = :dia
        LIMIT 1
    ");
    $stmt->execute([
        'barbeiro' => $barbeiro,
        'dia' => $diaSemana
    ]);

    $horario = $stmt->fetch(PDO::FETCH_ASSOC);

    // Se não existir horário ou estiver desativado → sem horários disponíveis
    if (!$horario || $horario['ativo'] == 0) {
        echo json_encode([]);
        exit;
    }

    // Convertendo expediente
    $inicio_expediente = strtotime("$data {$horario['inicio_expediente']}");
    $fim_expediente = strtotime("$data {$horario['fim_expediente']}");

    $inicio_almoco = strtotime("$data {$horario['almoco_inicio']}");
    $fim_almoco = strtotime("$data {$horario['almoco_fim']}");

    // ===============================
    // 2. Buscar agendamentos ocupados
    // ===============================
    $stmt = $pdo->prepare("
        SELECT horario, duracao 
        FROM agendamentos
        WHERE barbeiro_id = :barbeiro
          AND data = :data
          AND status IN ('confirmado', 'bloqueado', 'ativo')
        ORDER BY horario
    ");
    $stmt->execute([
        'barbeiro' => $barbeiro,
        'data' => $data
    ]);

    $agendamentos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Converte agendamentos para intervalos
    $ocupados = [];
    foreach ($agendamentos as $ag) {
        $inicio = strtotime("$data {$ag['horario']}");
        $fim = strtotime("+{$ag['duracao']} minutes", $inicio);
        $ocupados[] = ['inicio' => $inicio, 'fim' => $fim];
    }

    // ===============================
    // 3. Listar horários disponíveis
    // ===============================
    $horarios = [];
    $agora = time();

    // Loop a cada 10 minutos
    for ($inicio_teste = $inicio_expediente;
         $inicio_teste <= $fim_expediente - ($duracao_procedimento * 60);
         $inicio_teste += 600) {

        $fim_teste = $inicio_teste + $duracao_procedimento * 60;

        // Ignora horários passados no dia atual
        if (date('Y-m-d') === $data && $fim_teste <= $agora) {
            continue;
        }

        // Ignora almoço
        if ($inicio_teste < $fim_almoco && $fim_teste > $inicio_almoco) {
            continue;
        }

        // Verifica conflito com agendamentos
        $disponivel = true;

        foreach ($ocupados as $o) {
            if ($inicio_teste < $o['fim'] && $fim_teste > $o['inicio']) {
                $disponivel = false;
                break;
            }
        }

        if ($disponivel) {
            $horarios[] = date("H:i", $inicio_teste);
        }
    }

    echo json_encode($horarios);

} catch (PDOException $e) {
    echo json_encode(['erro' => $e->getMessage()]);
}
