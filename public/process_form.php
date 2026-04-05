<?php
require_once __DIR__.'/../config/env.php';
require_once __DIR__.'/../config/session.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

require_once __DIR__.'/../config/database.php';
require_once __DIR__.'/../src/Csrf.php';
require_once __DIR__.'/../src/Validator.php';
require_once __DIR__.'/../src/Logger.php';

AppLogger::setLogDir(__DIR__.'/../storage/logs');

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !Csrf::verify()) {
    header('Location: form.php');
    exit;
}

$data = $_POST;
$valid = new Validator($data);

$valid->required('nome', 'Nome');
$valid->maxLength('nome', 'Nome', 100);
$valid->required('telefone', 'Telefone');
$valid->phone('telefone', 'Telefone');
$valid->required('celular', 'Celular');
$valid->phone('celular', 'Celular');
$valid->required('email', 'E-mail');
$valid->email('email', 'E-mail');
$valid->maxLength('email', 'E-mail', 100);
$valid->required('profissao', 'Profissão');
$valid->required('numero_registro', 'Número de Registro');
$valid->maxLength('numero_registro', 'Número de Registro', 50);
$valid->required('conselho', 'Conselho');
$valid->maxLength('conselho', 'Conselho', 50);
$valid->required('evento', 'Evento');
$valid->maxLength('evento', 'Evento', 100);
$valid->required('cidade', 'Cidade');
$valid->maxLength('cidade', 'Cidade', 100);
$valid->required('estado', 'Estado');

$estados = ['AC','AL','AP','AM','BA','CE','DF','ES','GO','MA','MT','MS','MG','PA','PB','PR','PE','PI','RJ','RN','RS','RO','RR','SC','SE','SP','TO'];
$valid->inArray('estado', 'Estado', $estados);

if ($valid->fails()) {
    $params = http_build_query([
        'errors' => json_encode($valid->errors()),
        'old' => json_encode($data),
    ]);
    header("Location: form.php?{$params}");
    exit;
}

// Derivar representante e timestamp do servidor, NÃO do cliente
$representante = ($_SESSION['nome'] ?? '') . ' ' . ($_SESSION['sobrenome'] ?? '');
$data_hora = date('Y-m-d H:i:s');
$user_id = (int) $_SESSION['user_id'];

$sql = 'INSERT INTO forms (nome, telefone, celular, email, profissao, numero_registro, conselho, evento, cidade, estado, data_hora, representante, created_by_user_id)
        VALUES (:nome, :telefone, :celular, :email, :profissao, :numero_registro, :conselho, :evento, :cidade, :estado, :data_hora, :representante, :user_id)';

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':nome' => trim($data['nome']),
        ':telefone' => trim($data['telefone']),
        ':celular' => trim($data['celular']),
        ':email' => trim($data['email']),
        ':profissao' => trim($data['profissao']),
        ':numero_registro' => trim($data['numero_registro']),
        ':conselho' => trim($data['conselho']),
        ':evento' => trim($data['evento']),
        ':cidade' => trim($data['cidade']),
        ':estado' => trim($data['estado']),
        ':data_hora' => $data_hora,
        ':representante' => trim($representante),
        ':user_id' => $user_id,
    ]);

    Csrf::regenerate();
    header('Location: form.php?success=true');
    exit;
} catch (PDOException $e) {
    AppLogger::error('Erro ao inserir formulário', ['error' => $e->getMessage()]);
    header('Location: form.php?errors='.json_encode(['Erro interno ao salvar. Tente novamente.']));
    exit;
}
