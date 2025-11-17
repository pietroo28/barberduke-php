<?php
include('../conexao.php');

$action = $_REQUEST['action'] ?? '';

if ($action === 'listar') {
    $barbeiro_id = $_GET['barbeiro_id'] ?? '';
    $data_inicial = $_GET['data_inicial'] ?? '';
    $data_final = $_GET['data_final'] ?? '';
    $tipo = $_GET['tipo'] ?? '';
    $status = $_GET['status'] ?? '';
    $forma = $_GET['forma'] ?? '';

    try {
        $query = "SELECT * FROM caixa WHERE 1=1";
        $params = [];

        if ($barbeiro_id) {
            $query .= " AND barbeiro_id = :barbeiro_id";
            $params[':barbeiro_id'] = $barbeiro_id;
        }

        if ($data_inicial && $data_final) {
            $query .= " AND DATE(data) BETWEEN :data_inicial AND :data_final";
            $params[':data_inicial'] = $data_inicial;
            $params[':data_final'] = $data_final;
        }

        if ($tipo) {
            $query .= " AND tipo = :tipo";
            $params[':tipo'] = $tipo;
        }

        if ($status) {
            $query .= " AND status = :status";
            $params[':status'] = $status;
        }

        if ($forma) {
            $query .= " AND forma = :forma";
            $params[':forma'] = $forma;
        }

        $query .= " ORDER BY data DESC";

        $stmt = $pdo->prepare($query);
        $stmt->execute($params);
        $dados = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Calcular totais
        $totalEntrada = 0;
        $totalSaida = 0;
        $totalDespesas = 0;

        foreach ($dados as $item) {
            $valor = (float)$item['valor'];
            if ($item['status'] === 'entrada') {
                $totalEntrada += $valor;
            } else if ($item['status'] === 'saida') {
                $totalSaida += $valor;
                // Se for tipo despesa (exemplo: loja, barbeiros, saida)
                if (in_array($item['tipo'], ['loja', 'barbeiros', 'saida'])) {
                    $totalDespesas += $valor;
                }
            }
        }

        $lucroLiquido = $totalEntrada - $totalDespesas;

        // Calcular média diária considerando dias úteis entre data_inicial e data_final
        $mediaDiaria = 0;
        if ($data_inicial && $data_final) {
            $periodo = new DatePeriod(
                new DateTime($data_inicial),
                new DateInterval('P1D'),
                (new DateTime($data_final))->modify('+1 day') // inclui o último dia
            );

            $diasUteis = 0;
            foreach ($periodo as $dia) {
                // Dias úteis são seg(1) a sex(5)
                if ($dia->format('N') <= 5) {
                    $diasUteis++;
                }
            }
            if ($diasUteis > 0) {
                $mediaDiaria = $totalEntrada / $diasUteis;
            }
        }

        echo json_encode([
            'movimentacoes' => $dados,
            'totalEntrada' => $totalEntrada,
            'totalSaida' => $totalSaida,
            'totalDespesas' => $totalDespesas,
            'lucroLiquido' => $lucroLiquido,
            'mediaDiaria' => round($mediaDiaria, 2),
            'diasUteis' => $diasUteis ?? 0
        ]);
        exit;

    } catch (PDOException $e) {
        echo json_encode(['erro' => $e->getMessage()]);
        exit;
    }
}

if ($action === 'adicionar') {
    $barbeiro_id = $_POST['barbeiro_id'] ?? null;
    $valor = $_POST['valor'] ?? null;
    $status = $_POST['status'] ?? null;
    $forma = $_POST['forma'] ?? null;
    $tipo = $_POST['tipo'] ?? null;

    if ($barbeiro_id && $valor && $status && $forma && $tipo) {
        try {
            $stmt = $pdo->prepare("INSERT INTO caixa (barbeiro_id, valor, status, forma, tipo, data) 
                                   VALUES (:barbeiro_id, :valor, :status, :forma, :tipo, NOW())");
            $stmt->execute([
                ':barbeiro_id' => $barbeiro_id,
                ':valor' => $valor,
                ':status' => $status,
                ':forma' => $forma,
                ':tipo' => $tipo
            ]);
            echo "Movimentação adicionada com sucesso!";
        } catch (PDOException $e) {
            echo "Erro ao adicionar: " . $e->getMessage();
        }
    } else {
        echo "Preencha todos os campos!";
    }
    exit;
}

if ($action === 'excluir') {
    $id = $_POST['id'] ?? null;

    if ($id) {
        try {
            $stmt = $pdo->prepare("DELETE FROM caixa WHERE id = :id");
            $stmt->execute([':id' => $id]);
            echo "Movimentação excluída com sucesso!";
        } catch (PDOException $e) {
            echo "Erro ao excluir: " . $e->getMessage();
        }
    } else {
        echo "ID não informado.";
    }
    exit;
}
?>
