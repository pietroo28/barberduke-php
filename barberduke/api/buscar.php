<?php
require_once '../conexao.php';
// usa sua conexão PDO com MySQL

header('Content-Type: application/json; charset=utf-8');

try {
    $stmt = $pdo->query("SELECT id, nome, valor, duracao FROM procedimentos ORDER BY nome ASC");
    $dados = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($dados);
} catch (PDOException $e) {
    echo json_encode(['erro' => $e->getMessage()]);
}
