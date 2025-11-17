<?php
require_once '../conexao.php';

header('Content-Type: application/json');

try {
    // Pega a data atual
    date_default_timezone_set('America/Sao_Paulo'); // ajuste conforme sua timezone
    $hoje = date('Y-m-d');

    // Atualiza agendamentos cuja data já passou e status != 'passado'
    $stmt = $pdo->prepare("
        UPDATE agendamentos
        SET status = 'passado'
        WHERE data < :hoje
        AND status != 'passado'
    ");

    $stmt->execute([':hoje' => $hoje]);
    $atualizados = $stmt->rowCount();

    echo json_encode([
        'success' => true,
        'mensagem' => "Atualizados {$atualizados} agendamentos anteriores a hoje para 'passado'."
    ]);

} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'mensagem' => 'Erro ao atualizar agendamentos: ' . $e->getMessage()
    ]);
}
