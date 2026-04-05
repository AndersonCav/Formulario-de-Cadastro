<?php

$required = ['DB_HOST', 'DB_PORT', 'DB_NAME', 'DB_USER', 'DB_PASS'];
$missing = array_filter($required, fn($key) => !array_key_exists($key, $_ENV) || $_ENV[$key] === '');

if ($missing) {
    http_response_code(500);
    $debug = ($_ENV['APP_DEBUG'] ?? 'false') === 'true';
    if ($debug) {
        die('Configuração do banco de dados incompleta. Variáveis ausentes: ' . implode(', ', $missing));
    }
    die('Erro interno de configuração. Contate o administrador.');
}

$host = $_ENV['DB_HOST'];
$port = $_ENV['DB_PORT'];
$dbname = $_ENV['DB_NAME'];
$user = $_ENV['DB_USER'];
$pass = $_ENV['DB_PASS'];

try {
    $pdo = new PDO("mysql:host={$host};port={$port};dbname={$dbname};charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    $debug = ($_ENV['APP_DEBUG'] ?? 'false') === 'true';
    if ($debug) {
        die('Erro de conexão com banco de dados: ' . htmlspecialchars($e->getMessage()));
    }
    die('Falha na conexão com o banco de dados. Contate o administrador.');
}
