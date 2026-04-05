<?php
require_once __DIR__.'/../config/env.php';
require_once __DIR__.'/../config/session.php';
require_once __DIR__.'/../src/helpers.php';

require_login();

require_once __DIR__.'/../config/database.php';
require_once __DIR__.'/../src/Csrf.php';
require_once __DIR__.'/../src/Validator.php';
require_once __DIR__.'/../src/Logger.php';
AppLogger::setLogDir(__DIR__.'/../storage/logs');

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !Csrf::verify()) {
    header('Location: form.php'); exit;
}

$data = $_POST;
$valid = new Validator($data);
$valid->required('nome', 'Nome');
$valid->maxLength('nome', 'Nome', 100);
$valid->required('telefone', 'Telefone');
$valid->required('celular', 'Celular');
$valid->required('email', 'E-mail');
$valid->email('email', 'E-mail');
$valid->maxLength('email', 'E-mail', 100);
$valid->required('profissao', 'Profissão');
$valid->required('numero_registro', 'Nº de Registro');
$valid->maxLength('numero_registro', 'Nº de Registro', 50);
$valid->required('conselho', 'Conselho');
$valid->maxLength('conselho', 'Conselho', 50);
$valid->required('evento', 'Evento');
$valid->maxLength('evento', 'Evento', 100);
$valid->required('cidade', 'Cidade');
$valid->maxLength('cidade', 'Cidade', 100);
$valid->required('estado', 'Estado');
$valid->inArray('estado', 'Estado', ['AC','AL','AP','AM','BA','CE','DF','ES','GO','MA','MT','MS','MG','PA','PB','PR','PE','PI','RJ','RN','RS','RO','RR','SC','SE','SP','TO']);

if ($valid->fails()) {
    $_SESSION['__form_errors'] = $valid->errors();
    $_SESSION['__form_old'] = $data;
    header('Location: form.php');
    exit;
}

$representante = trim($_SESSION['nome'] ?? '').' '.trim($_SESSION['sobrenome'] ?? '');

try {
    $pdo->prepare('INSERT INTO forms (nome, telefone, celular, email, profissao, numero_registro, conselho, evento, cidade, estado, data_hora, representante, created_by_user_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)')
        ->execute([
            trim($data['nome']), trim($data['telefone']), trim($data['celular']),
            trim($data['email']), trim($data['profissao']), trim($data['numero_registro']),
            trim($data['conselho']), trim($data['evento']), trim($data['cidade']),
            trim($data['estado']), date('Y-m-d H:i:s'), $representante, user_id()
        ]);
    Csrf::regenerate();
    header('Location: form.php?success=true');
    exit;
} catch (PDOException $e) {
    AppLogger::error('Erro ao inserir formulário', ['error' => $e->getMessage()]);
    $_SESSION['__form_errors'] = ['Erro interno ao salvar o formulário.'];
    $_SESSION['__form_old'] = $data;
    header('Location: form.php');
    exit;
}
