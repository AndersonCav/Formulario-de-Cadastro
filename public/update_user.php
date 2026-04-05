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
    header('Location: view_users.php'); exit;
}

$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
if (!$id) { header('Location: view_users.php'); exit; }

$data = $_POST;
$valid = new Validator($data);
$valid->username('username');
$valid->required('nome', 'Nome');
$valid->maxLength('nome', 'Nome', 100);
$valid->required('sobrenome', 'Sobrenome');
$valid->maxLength('sobrenome', 'Sobrenome', 100);

if ($valid->fails()) {
    Flash::set('error', $valid->firstError());
    header('Location: edit_user.php?id='.$id); exit;
}

$password = trim($data['password'] ?? '');
if ($password !== '' && strlen($password) < 6) {
    Flash::set('error', 'A senha deve ter no mínimo 6 caracteres.');
    header('Location: edit_user.php?id='.$id); exit;
}

$username = trim($data['username']);
// Check uniqueness
$stmt = $pdo->prepare('SELECT id FROM users WHERE username = :uname AND id != :id');
$stmt->execute([':uname' => $username, ':id' => $id]);
if ($stmt->fetch()) {
    Flash::set('error', 'Nome de usuário já está em uso.');
    header('Location: edit_user.php?id='.$id); exit;
}

$is_admin = in_array($data['is_admin'], ['0', '1'], true) ? (int) $data['is_admin'] : 0;

try {
    if ($password !== '') {
        $pdo->prepare('UPDATE users SET username = ?, password = ?, is_admin = ?, nome = ?, sobrenome = ? WHERE id = ?')
            ->execute([$username, password_hash($password, PASSWORD_DEFAULT), $is_admin, trim($data['nome']), trim($data['sobrenome']), $id]);
    } else {
        $pdo->prepare('UPDATE users SET username = ?, is_admin = ?, nome = ?, sobrenome = ? WHERE id = ?')
            ->execute([$username, $is_admin, trim($data['nome']), trim($data['sobrenome']), $id]);
    }
    Flash::set('success', 'Usuário atualizado com sucesso.');
    Csrf::regenerate();
} catch (PDOException $e) {
    AppLogger::error('Erro ao atualizar usuário', ['id' => $id, 'error' => $e->getMessage()]);
    Flash::set('error', 'Erro ao atualizar usuário.');
}
header('Location: view_users.php');
exit;
