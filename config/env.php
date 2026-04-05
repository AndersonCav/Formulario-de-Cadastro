<?php

$envFile = dirname(__DIR__) . '/.env';

if (!file_exists($envFile)) {
    $debug = (getenv('APP_DEBUG') === 'true');
    http_response_code(500);
    if ($debug) {
        die('Arquivo .env não encontrado. Copie .env.example para .env e configure as variáveis.');
    }
    die('Erro de configuração do sistema.');
}

$lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
foreach ($lines as $line) {
    $line = trim($line);
    if ($line === '' || strpos($line, '#') === 0) {
        continue;
    }
    if (strpos($line, '=') !== false) {
        [$key, $value] = explode('=', $line, 2);
        $key = trim($key);
        $value = trim($value);
        $value = trim($value, '"\' ');
        $_ENV[$key] = $value;
        putenv("{$key}={$value}");
    }
}
