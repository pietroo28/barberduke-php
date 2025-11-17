<?php
header('Content-Type: application/json');
include '../conexao.php'; // já define $pdo

try {
    // Seleciona os bloqueios do usuário nulo (usuario_id = 3) com o ID do barbeiro
    $sql = "SELECT 
                a.barbeiro_id, 
                a.data, 
                a.horario, 
                b.nome AS barbeiro_nome
            FROM agendamentos a
            JOIN barbeiros b ON a.barbeiro_id = b.id
            WHERE a.usuario_id = 3
            ORDER BY a.data, a.horario";

    $stmt = $pdo->query($sql);
    $bloqueios = $stmt->fetchAll();

    // Retorna o JSON com todos os dados necessários
    echo json_encode($bloqueios);
} catch (Exception $e) {
    echo json_encode([
        'status' => false,
        'mensagem' => $e->getMessage()
    ]);
}
?>
