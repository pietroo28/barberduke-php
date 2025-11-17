<?php
header('Content-Type: application/json');

// Inclui seu arquivo de conexão PDO
include '../conexao.php'; // Ajuste o caminho se necessário

// Lê o JSON enviado pelo JS
$input = json_decode(file_get_contents('php://input'), true);

// Verifica se o ID foi enviado
if (!isset($input['id']) || empty($input['id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'ID não informado'
    ]);
    exit;
}

$id = $input['id'];

try {
    // Prepara e executa o DELETE
    $stmt = $pdo->prepare("DELETE FROM agendamentos WHERE id = ?");
    $stmt->execute([$id]);

    // Verifica se algum registro foi deletado
    if ($stmt->rowCount() > 0) {
        echo json_encode([
            'success' => true,
            'message' => 'Agendamento excluído com sucesso!'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Agendamento não encontrado.'
        ]);
    }
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao excluir: ' . $e->getMessage()
    ]);
}
?>
