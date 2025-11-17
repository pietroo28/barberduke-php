<?php
require '../banco.php';

// Pega o CPF da URL
$cpf = $_GET['cpf'] ?? '';

if (!$cpf) {
    echo "CPF não informado!";
    exit;
}

// Consulta clientes no Asaas filtrando pelo CPF
$result = asaasGet("/customers?cpfCnpj={$cpf}");

if ($result['status'] !== 200) {
    echo "Erro ao consultar Asaas: HTTP {$result['status']}";
    exit;
}

$clientes = $result['body']['data'] ?? [];

?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Assinatura do Cliente</title>
    <link rel="stylesheet" href="/barberduke/static/assinatura.css">
</head>
<body>
<main>
<?php
// Se não achou cliente → exibe tela estilizada
if (empty($clientes)):
?>
    <div class="sem-assinatura">
        <h1>Cliente não encontrado 😕</h1>
        <p>Parece que você ainda não possui um plano ativo conosco.</p>
        <a href="/barberduke/templates/planos.html" class="btn-plano">Vamos fazer um plano?</a>
    </div>

<?php
else:
    // Pega o cliente
    $cliente = $clientes[0];

    // Consulta assinaturas do cliente
    $customerId = $cliente['id'];
    $assinaturas = asaasGet("/subscriptions?customer={$customerId}");

    if ($assinaturas['status'] !== 200) {
        echo "Erro ao consultar assinaturas: HTTP {$assinaturas['status']}";
        exit;
    }

    $assinaturas = $assinaturas['body']['data'] ?? [];
?>
    <h1>Assinatura de <?php echo htmlspecialchars($cliente['name']); ?></h1>

    <?php if (empty($assinaturas)): ?>
        <div class="sem-assinatura">
            <h2>Você ainda não possui uma assinatura ativa!</h2>
            <a href="/barberduke/templates/planos.html" class="btn-plano">Vamos fazer um plano?</a>
        </div>
    <?php else: ?>
        <?php foreach ($assinaturas as $a): ?>
            <?php
                $subscriptionId = $a['id'];
                $pagamentos = asaasGet("/payments?subscription={$subscriptionId}&status=ACTIVE");
                $pagamentos = $pagamentos['body']['data'] ?? [];
                $ultimoPagamento = '-';
                $proximaCobranca = '-';

                if (!empty($pagamentos)) {
                    usort($pagamentos, fn($p1, $p2) => strtotime($p2['dateCreated']) - strtotime($p1['dateCreated']));
                    foreach ($pagamentos as $p) {
                        if ($p['status'] === 'CONFIRMED') {
                            $ultimoPagamento = date('d/m/Y', strtotime($p['dateCreated']));
                            break;
                        }
                    }
                    $pendingPayments = array_filter($pagamentos, fn($p) => $p['status'] === 'PENDING');
                    if (!empty($pendingPayments)) {
                        usort($pendingPayments, fn($p1, $p2) => strtotime($p1['dueDate']) - strtotime($p2['dueDate']));
                        $proximaCobranca = date('d/m/Y', strtotime($pendingPayments[0]['dueDate']));
                    }
                }
            ?>

            <div class="assinatura-box">
                <h2>Assinatura</h2>
                <p><strong>Status:</strong> <?php echo htmlspecialchars($a['status']); ?></p>
                <p><strong>Forma de pagamento:</strong> <?php echo htmlspecialchars($a['billingType']); ?></p>
                <p><strong>Valor:</strong> R$ <?php echo number_format($a['value'], 2, ',', '.'); ?></p>
                <p><strong>Cobranças futuras:</strong> <?php echo $a['nextDueDate'] ? date('d/m/Y', strtotime($a['nextDueDate'])) : '-'; ?></p>
                <p><strong>Último pagamento:</strong> <?php echo $ultimoPagamento; ?></p>
                <p><strong>Próxima cobrança:</strong> <?php echo $proximaCobranca; ?></p>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
<?php endif; ?>
</main>
</body>
</html>
