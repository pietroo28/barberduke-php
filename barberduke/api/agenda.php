<?php
header('Content-Type: application/json');
require_once '../conexao.php'; // Ajuste o caminho se necessário

$barbeiro_id = $_GET['barbeiro_id'] ?? null;
$data = $_GET['data'] ?? null;

if (!$barbeiro_id || !$data) {
    echo json_encode([]);
    exit;
}

// Buscar agendamentos e bloqueios do barbeiro na data selecionada
$sql = "
    SELECT 
        a.id,
        a.horario,
        a.duracao,
        a.status,
        u.nome AS nome_cliente,
        p.nome AS nome_procedimento
    FROM agendamentos a
    LEFT JOIN usuarios u ON u.id = a.usuario_id
    LEFT JOIN agendamento_procedimentos ap ON ap.agendamento_id = a.id
    LEFT JOIN procedimentos p ON p.id = ap.procedimento_id
    WHERE a.barbeiro_id = :barbeiro_id
      AND a.data = :data
    ORDER BY a.horario
";

$stmt = $pdo->prepare($sql);
$stmt->execute([
    ':barbeiro_id' => $barbeiro_id,
    ':data' => $data
]);

$agendamentos = $stmt->fetchAll(PDO::FETCH_ASSOC);

$result = [];

foreach ($agendamentos as $ag) {
    $horario_inicio = $ag['horario'];
    $duracao = $ag['duracao'] ?? 30; // fallback 30 minutos
    $horario_fim = date('H:i:s', strtotime("+$duracao minutes", strtotime($horario_inicio)));

    // 🔹 Se for bloqueio, exibe de outra forma:
    if ($ag['status'] === 'bloqueado') {
        $titulo = '⛔ Horário Bloqueado';
        $cor = '#6c757d'; // cinza
    } else {
        $titulo = "{$ag['nome_cliente']} - {$ag['nome_procedimento']}";
        $cor = '#4e73df';
    }

    $result[] = [
        'id' => $ag['id'],
        'cliente' => $ag['nome_cliente'] ?? '',
        'procedimento' => $ag['nome_procedimento'] ?? '',
        'data' => $data,
        'horario_inicio' => $horario_inicio,
        'horario_fim' => $horario_fim,
        'status' => $ag['status'],
        'titulo' => $titulo,
        'cor' => $cor
    ];
}

echo json_encode($result);
