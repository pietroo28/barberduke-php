<?php
require 'banco.php';

// Pega clientes
$result = asaasGet('/customers?limit=100');

echo "<h1>Clientes</h1>";
echo "<p>Status HTTP: {$result['status']}</p>";
echo "<pre>";
print_r($result['body']);
echo "</pre>";
