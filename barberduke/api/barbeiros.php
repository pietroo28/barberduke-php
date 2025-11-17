<?php
// Configura o header para retornar JSON
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *"); // permite acesso de qualquer origem

// Inclui a conexão PDO
include __DIR__ . '/../conexao.php'; // ajusta o caminho se necessário

try {
    // Prepara e executa a consulta para buscar barbeiros ativos
    $stmt = $pdo->query("SELECT id, nome FROM barbeiros WHERE status = 'ativo' ORDER BY nome ASC");

    // Busca todos os resultados como array associativo
    $barbeiros = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Retorna o JSON
    echo json_encode($barbeiros);

} catch (PDOException $e) {
    // Retorna JSON com mensagem de erro caso ocorra
    echo json_encode([
        "erro" => "Falha ao buscar barbeiros",
        "mensagem" => $e->getMessage()
    ]);
}
