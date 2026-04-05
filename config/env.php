<?php
/**
 * Carrega variáveis de ambiente a partir do arquivo .env.
 * Formato suportado: CHAVE=valor, CHAVE="valor com espaços", CHAVE='valor'
 * Linhas com # são comentários.
 */
$envFile = dirname(__DIR__) . '/.env';

if (!file_exists($envFile)) {
    http_response_code(500);
    $debug = (getenv('APP_DEBUG') === 'true');
    if ($debug) {
        die('Arquivo .env não encontrado. Copie .env.example para .env e configure.');
    }
    die('Erro de configuração do sistema.');
}

$lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
foreach ($lines as $line) {
    $line = trim($line);
    if ($line === '' || strpos($line, '#') === 0) {
        continue;
    }
    $eqPos = strpos($line, '=');
    if ($eqPos === false) {
        continue;
    }
    $key = trim(substr($line, 0, $eqPos));
    $value = trim(substr($line, $eqPos + 1));

    // Remove aspas externas
    if (strlen($value) >= 2) {
        $first = $value[0];
        $last = $value[strlen($value) - 1];
        if (($first === '"' && $last === '"') || ($first === "'" && $last === "'")) {
            $value = substr($value, 1, -1);
        }
    }

    $_ENV[$key] = $value;
    putenv("{$key}={$value}");
    if ($key === 'DB_PASS') {
        // DB_PASS pode ser vazio, mas ainda existe como chave
        $_ENV[$key] = $value;
    }
}
