<?php
header("Content-Type: application/json; charset=UTF-8");
require_once '../conexao.php'; // conexão via PDO

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {

    // ============================================================
    // LISTAR BARBEIROS (GET)
    // ============================================================
    case 'GET':
        try {
            $stmt = $pdo->query("SELECT id, nome, cpf, status FROM barbeiros ORDER BY id ASC");
            $barbeiros = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode($barbeiros);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(["success" => false, "error" => "Erro ao buscar barbeiros: " . $e->getMessage()]);
        }
        break;

    // ============================================================
    // ADICIONAR OU EDITAR BARBEIRO (POST)
    // ============================================================
    case 'POST':
        $data = json_decode(file_get_contents("php://input"), true);

        if (!$data) {
            http_response_code(400);
            echo json_encode(["success" => false, "error" => "Nenhum dado recebido"]);
            exit;
        }

        // Campos vindos do JS
        $id     = $data['id'] ?? null;
        $nome   = trim($data['nome'] ?? '');
        $cpf    = trim($data['cpf'] ?? '');
        $status = trim($data['status'] ?? 'ativo');

        if (empty($nome) || empty($cpf)) {
            http_response_code(400);
            echo json_encode(["success" => false, "error" => "Nome e CPF são obrigatórios"]);
            exit;
        }

        try {
            // ------------------------------------------------------------
            // CASO 1: EDITAR (se ID vier preenchido)
            // ------------------------------------------------------------
            if (!empty($id)) {
                $stmt = $pdo->prepare("UPDATE barbeiros SET nome = :nome, cpf = :cpf, status = :status WHERE id = :id");
                $stmt->execute([
                    ':nome' => $nome,
                    ':cpf' => $cpf,
                    ':status' => $status,
                    ':id' => $id
                ]);
                echo json_encode(["success" => true, "message" => "Barbeiro atualizado com sucesso"]);
            } 
            // ------------------------------------------------------------
            // CASO 2: ADICIONAR (se ID for nulo)
            // ------------------------------------------------------------
            else {
                $stmt = $pdo->prepare("INSERT INTO barbeiros (nome, cpf, status) VALUES (:nome, :cpf, :status)");
                $stmt->execute([
                    ':nome' => $nome,
                    ':cpf' => $cpf,
                    ':status' => $status
                ]);
                echo json_encode(["success" => true, "message" => "Barbeiro adicionado com sucesso"]);
            }

        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(["success" => false, "error" => "Erro no banco de dados: " . $e->getMessage()]);
        }

        break;

    // ============================================================
    // MÉTODO NÃO SUPORTADO
    // ============================================================
    default:
        http_response_code(405);
        echo json_encode(["success" => false, "error" => "Método não permitido"]);
        break;
}
