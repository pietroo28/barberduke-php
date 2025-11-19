<?php
// banco.php
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Sua chave de API (produçao ou sandbox)
// Inclua o $ se a chave real tiver
$apiKey = 'sua chave api';
$baseUrl = 'https://www.asaas.com/api/v3'; //endepoint para proc

/**
 * Função para fazer requisição GET à API da Asaas
 * @param string $endpoint Endpoint relativo, ex: '/customers?limit=100'
 * @return array Retorna array com 'status' e 'body' (JSON decodificado)
 */
function asaasGet($endpoint) {
    global $apiKey, $baseUrl;

    $ch = curl_init($baseUrl . $endpoint);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "access_token: $apiKey",
        "Content-Type: application/json",
        "User-Agent: BarberDukeApp/1.0"
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    if(curl_errno($ch)) {
        return ['status' => 0, 'body' => 'Erro cURL: ' . curl_error($ch)];
    }

    curl_close($ch);

    // Decodifica JSON
    $data = json_decode($response, true);

    return ['status' => $httpCode, 'body' => $data];
}

