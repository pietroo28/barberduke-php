<?php
$host = "localhost";
$usuario = "u782979727_hstygi";       
$senha = "0dx_WEIS-_";             
$banco = "u782979727_hstygi"; 

try {
    $pdo = new PDO("mysql:host=$host;dbname=$banco;charset=utf8mb4", $usuario, $senha);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    // echo "Conexão local realizada com sucesso!"; // opcional mostrar
} catch (PDOException $e) {
    die("Erro na conexão: " . $e->getMessage());
}
?>
