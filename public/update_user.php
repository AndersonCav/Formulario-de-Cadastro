<?php
require_once __DIR__.'/../config/env.php';
require_once __DIR__.'/../config/session.php';

if (!isset($_SESSION['user_id']) || (int) ($_SESSION['is_admin'] ?? 0) !== 1) {
    header('Location: dashboard.php');
    exit;
}

require_once __DIR__.'/../config/database.php';
require_once __DIR__.'/../src/Csrf.php';
require_once __DIR__.'/../src/Validator.php';
require_once __DIR__.'/../src/Flash.php';
require_once __DIR__.'/../src/Logger.php';

AppLogger::setLogDir(__DIR__.'/../storage/logs');

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !Csrf::verify()) {
    header('Location: view_users.php');
    exit;
}

$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
if (!$id) {
    header('Location: view_users.php');
    exit;
}

$data = $_POST;
$valid = new Validator($data);
$valid->username('username');
$valid->required('nome', 'Nome');
$valid->maxLength('nome', 'Nome', 100);
$valid->required('sobrenome', 'Sobrenome');
$valid->maxLength('sobrenome', 'Sobrenome', 100);

if ($valid->fails()) {
    Flash::set('error', $valid->firstError());
    header('Location: edit_user.php?id='.$id);
    exit;
}

$username = trim($data['username']);
$password = trim($data['password'] ?? '');
$is_admin = in_array($data['is_admin'], ['0', '1'], true) ? (int) $data['is_admin'] : 0;

if ($password !== '' && strlen($password) < 6) {
    Flash::set('error', 'A senha deve ter no mínimo 6 caracteres.');
    header('Location: edit_user.php?id='.$id);
    exit;
}

// Check username uniqueness (exclude current user)
$stmt = $pdo->prepare('SELECT id FROM users WHERE username = :username AND id != :id');
$stmt->execute([':username' => $username, ':id' => $id]);
if ($stmt->fetch()) {
    Flash::set('error', 'Nome de usuário já está em uso.');
    header('Location: edit_user.php?id='.$id);
    exit;
}

try {
    if ($password !== '') {
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare('UPDATE users SET username = :username, password = :password, is_admin = :is_admin, nome = :nome, sobrenome = :sobrenome WHERE id = :id');
        $stmt->execute([
            ':username' => $username,
            ':password' => $passwordHash,
            ':is_admin' => $is_admin,
            ':nome' => trim($data['nome']),
            ':sobrenome' => trim($data['sobrenome']),
            ':id' => $id,
        ]);
    } else {
        $stmt = $pdo->prepare('UPDATE users SET username = :username, is_admin = :is_admin, nome = :nome, sobrenome = :sobrenome WHERE id = :id');
        $stmt->execute([
            ':username' => $username,
            ':is_admin' => $is_admin,
            ':nome' => trim($data['nome']),
            ':sobrenome' => trim($data['sobrenome']),
            ':id' => $id,
        ]);
    }

    Flash::set('success', 'Usuário atualizado com sucesso.');
    Csrf::regenerate();
} catch (PDOException $e) {
    AppLogger::error('Erro ao atualizar usuário', ['id' => $id, 'error' => $e->getMessage()]);
    Flash::set('error', 'Erro ao atualizar usuário.');
}

header('Location: view_users.php');
exit;
