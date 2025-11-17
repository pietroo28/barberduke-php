<?php
header("Content-Type: application/json; charset=UTF-8");
require_once '../conexao.php'; // Conexão PDO

$method = $_SERVER['REQUEST_METHOD'];

if ($method !== 'GET') {
    http_response_code(405);
    echo json_encode(["error" => "Método não permitido"]);
    exit;
}

// Receber filtros via query params
$barbeiroId = isset($_GET['barbeiro_id']) ? intval($_GET['barbeiro_id']) : null;
$status = isset($_GET['status']) ? $_GET['status'] : null;
$dataInicial = isset($_GET['data_inicial']) ? $_GET['data_inicial'] : null;
$dataFinal = isset($_GET['data_final']) ? $_GET['data_final'] : null;

try {
    // Query base: clientes + agendamentos + barbeiros + procedimentos
    $query = "
        SELECT 
            u.id AS cliente_id,
            u.nome AS cliente_nome,
            u.cpf,
            u.telefone,
            a.id AS agendamento_id,
            a.data AS agendamento_data,
            a.horario AS agendamento_horario,
            a.status AS agendamento_status,
            b.nome AS barbeiro_nome,
            p.nome AS procedimento_nome
        FROM usuarios u
        INNER JOIN agendamentos a ON a.usuario_id = u.id
        INNER JOIN barbeiros b ON b.id = a.barbeiro_id
        LEFT JOIN agendamento_procedimentos ap ON ap.agendamento_id = a.id
        LEFT JOIN procedimentos p ON p.id = ap.procedimento_id
        WHERE 1=1
    ";

    $params = [];

    if ($barbeiroId) {
        $query .= " AND a.barbeiro_id = :barbeiro_id";
        $params[':barbeiro_id'] = $barbeiroId;
    }

    if ($status && $status !== "Todos") {
        $query .= " AND a.status = :status";
        $params[':status'] = $status;
    }

    if ($dataInicial) {
        $query .= " AND a.data >= :data_inicial";
        $params[':data_inicial'] = $dataInicial;
    }

    if ($dataFinal) {
        $query .= " AND a.data <= :data_final";
        $params[':data_final'] = $dataFinal;
    }

    $query .= " ORDER BY a.data ASC, a.horario ASC";

    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Agrupar agendamentos por cliente
    $clientes = [];
    foreach ($resultados as $row) {
        $clienteId = $row['cliente_id'];
        if (!isset($clientes[$clienteId])) {
            $clientes[$clienteId] = [
                'id' => $clienteId,
                'nome' => $row['cliente_nome'],
                'cpf' => $row['cpf'],
                'telefone' => $row['telefone'],
                'agendamentos' => []
            ];
        }

        $clientes[$clienteId]['agendamentos'][] = [
            'id' => $row['agendamento_id'],
            'data' => $row['agendamento_data'],
            'horario' => $row['agendamento_horario'],
            'status' => $row['agendamento_status'],
            'barbeiro' => $row['barbeiro_nome'],
            'procedimento' => $row['procedimento_nome'] ?? '—'
        ];
    }

    // Reindexar array
    $clientes = array_values($clientes);

    echo json_encode($clientes);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["error" => "Erro ao buscar clientes: " . $e->getMessage()]);
}
