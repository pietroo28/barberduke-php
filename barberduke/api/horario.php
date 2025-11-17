<?php
date_default_timezone_set('America/Sao_Paulo');
header("Content-Type: application/json; charset=UTF-8");

include __DIR__ . '/../conexao.php';

// Recebe parâmetros via GET
$barbeiro = isset($_GET['barbeiro']) ? (int)$_GET['barbeiro'] : 0;
$dia = isset($_GET['dia']) ? (int)$_GET['dia'] : null;
$id = isset($_GET['id']) ? (int)$_GET['id'] : null;

if (!$barbeiro && !$id) {
    echo json_encode([]);
    exit;
}

try {
    if ($id) {
        // Retorna apenas o horário pelo ID
        $stmt = $pdo->prepare("SELECT * FROM horarios_barbeiro WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => $id]);
        $horario = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($horario) {
            echo json_encode($horario);
        } else {
            echo json_encode([]);
        }
    } else {
        // Retorna todos os horários do barbeiro para o dia selecionado
        $stmt = $pdo->prepare("
            SELECT * FROM horarios_barbeiro
            WHERE barbeiro_id = :barbeiro
            AND dia_semana = :dia
            ORDER BY inicio_expediente
        ");
        $stmt->execute([
            'barbeiro' => $barbeiro,
            'dia' => $dia
        ]);

        $horarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($horarios);
    }
} catch (PDOException $e) {
    echo json_encode(['erro' => $e->getMessage()]);
}
