<?php
require_once '../conexao.php';
header('Content-Type: application/json');

// Lê corpo JSON da requisição (caso não venha via form-data)
$input = json_decode(file_get_contents('php://input'), true);
$id = $_POST['id'] ?? $input['id'] ?? null;
$novo_status = $_POST['status'] ?? $input['status'] ?? null;

$status_validos = ['ativo', 'confirmado', 'cancelado', 'passado'];

if (!$id || !in_array($novo_status, $status_validos)) {
    echo json_encode(['success' => false, 'message' => 'Parâmetros inválidos.']);
    exit;
}

try {
    $stmt = $pdo->prepare("UPDATE agendamentos SET status = :status WHERE id = :id");
    $stmt->execute([':status' => $novo_status, ':id' => $id]);

    echo json_encode(['success' => true, 'message' => 'Status atualizado com sucesso!']);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Erro ao atualizar: ' . $e->getMessage()]);
}
