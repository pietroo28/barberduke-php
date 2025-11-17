<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

require_once("../conexao.php");

$metodo = $_SERVER["REQUEST_METHOD"];

// Requisições OPTIONS (pré-flight) — usadas pelo fetch() antes de POST/PUT/DELETE
if ($metodo === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// GET — Listar procedimentos
if ($metodo === 'GET') {
    try {
        $stmt = $pdo->query("SELECT * FROM procedimentos ORDER BY id ASC");
        $procedimentos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($procedimentos);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(["erro" => "Erro ao consultar procedimentos: " . $e->getMessage()]);
    }
}

// POST — Adicionar novo procedimento
elseif ($metodo === 'POST') {
    $dados = json_decode(file_get_contents("php://input"), true);

    if (!isset($dados['nome'], $dados['duracao'], $dados['valor'])) {
        http_response_code(400);
        echo json_encode(["erro" => "Campos obrigatórios faltando"]);
        exit;
    }

    try {
        $stmt = $pdo->prepare("INSERT INTO procedimentos (nome, duracao, valor) VALUES (:nome, :duracao, :valor)");
        $stmt->execute([
            ':nome' => $dados['nome'],
            ':duracao' => $dados['duracao'],
            ':valor' => $dados['valor']
        ]);
        echo json_encode(["sucesso" => "Procedimento adicionado", "id" => $pdo->lastInsertId()]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(["erro" => "Erro ao adicionar procedimento: " . $e->getMessage()]);
    }
}

// PUT — Editar procedimento existente
elseif ($metodo === 'PUT') {
    $dados = json_decode(file_get_contents("php://input"), true);

    if (!isset($dados['id'], $dados['nome'], $dados['duracao'], $dados['valor'])) {
        http_response_code(400);
        echo json_encode(["erro" => "Campos obrigatórios faltando"]);
        exit;
    }

    try {
        $stmt = $pdo->prepare("UPDATE procedimentos SET nome = :nome, duracao = :duracao, valor = :valor WHERE id = :id");
        $stmt->execute([
            ':id' => $dados['id'],
            ':nome' => $dados['nome'],
            ':duracao' => $dados['duracao'],
            ':valor' => $dados['valor']
        ]);
        echo json_encode(["sucesso" => "Procedimento atualizado"]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(["erro" => "Erro ao atualizar procedimento: " . $e->getMessage()]);
    }
}

// DELETE — Excluir procedimento
elseif ($metodo === 'DELETE') {
    $dados = json_decode(file_get_contents("php://input"), true);

    if (!isset($dados['id'])) {
        http_response_code(400);
        echo json_encode(["erro" => "ID não informado"]);
        exit;
    }

    try {
        $stmt = $pdo->prepare("DELETE FROM procedimentos WHERE id = :id");
        $stmt->execute([':id' => $dados['id']]);
        echo json_encode(["sucesso" => "Procedimento excluído"]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(["erro" => "Erro ao excluir procedimento: " . $e->getMessage()]);
    }
}

// Método não suportado
else {
    http_response_code(405);
    echo json_encode(["erro" => "Método não permitido"]);
}
