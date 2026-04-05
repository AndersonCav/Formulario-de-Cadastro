<?php
require_once __DIR__.'/../config/env.php';
require_once __DIR__.'/../config/session.php';
require_once __DIR__.'/../src/helpers.php';
require_admin();

require_once __DIR__.'/../config/database.php';
require_once __DIR__.'/../src/Csrf.php';
require_once __DIR__.'/../src/Validator.php';
require_once __DIR__.'/../src/Flash.php';
require_once __DIR__.'/../src/Logger.php';
AppLogger::setLogDir(__DIR__.'/../storage/logs');

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !Csrf::verify()) {
    header('Location: create_user.php'); exit;
}

$data = $_POST;
$valid = new Validator($data);
$valid->required('nome', 'Nome');
$valid->maxLength('nome', 'Nome', 100);
$valid->required('sobrenome', 'Sobrenome');
$valid->maxLength('sobrenome', 'Sobrenome', 100);
$valid->username('username');
$valid->required('password', 'Senha');

if ($valid->fails()) {
    Flash::set('error', $valid->firstError());
    header('Location: create_user.php'); exit;
}

$username = trim($data['username']);
$password = trim($data['password']);
if (strlen($password) < 6) {
    Flash::set('error', 'A senha deve ter no mínimo 6 caracteres.');
    header('Location: create_user.php'); exit;
}

$stmt = $pdo->prepare('SELECT id FROM users WHERE username = :username');
$stmt->execute([':username' => $username]);
if ($stmt->fetch()) {
    Flash::set('error', 'Nome de usuário já está em uso.');
    header('Location: create_user.php'); exit;
}

$is_admin = in_array($data['is_admin'], ['0', '1'], true) ? (int) $data['is_admin'] : 0;

try {
    $pdo->prepare('INSERT INTO users (username, password, is_admin, nome, sobrenome) VALUES (?, ?, ?, ?, ?)')
        ->execute([$username, password_hash($password, PASSWORD_DEFAULT), $is_admin, trim($data['nome']), trim($data['sobrenome'])]);
    Csrf::regenerate();
    Flash::set('success', 'Usuário criado com sucesso.');
} catch (PDOException $e) {
    AppLogger::error('Erro ao criar usuário', ['error' => $e->getMessage()]);
    Flash::set('error', 'Erro ao criar usuário.');
}
header('Location: create_user.php');
exit;
