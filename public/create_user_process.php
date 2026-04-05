<?php
require_once __DIR__.'/bootstrap.php';
app_bootstrap(['database', 'csrf', 'validator', 'validation_profiles', 'flash', 'logger']);
require_admin();
AppLogger::setLogDir(__DIR__.'/../storage/logs');

app_require_post_csrf('create_user.php', 'Falha de validação CSRF ao criar usuário');

$data = $_POST;
$valid = new Validator($data);
ValidationProfiles::validateUserIdentity($valid);

if ($valid->fails()) {
    app_flash_redirect('error', $valid->firstError(), 'create_user.php');
}

$normalized = InputNormalizer::userPayload($data);
$username = $normalized['username'];
$password = $normalized['password'];
if (($passwordError = ValidationProfiles::validatePassword($password, true)) !== null) {
    app_flash_redirect('error', $passwordError, 'create_user.php');
}

$stmt = $pdo->prepare('SELECT id FROM users WHERE username = :username');
$stmt->execute([':username' => $username]);
if ($stmt->fetch()) {
    app_flash_redirect('error', 'Nome de usuário já está em uso.', 'create_user.php');
}

$is_admin = $normalized['is_admin'];

try {
    $pdo->prepare('INSERT INTO users (username, password, is_admin, nome, sobrenome) VALUES (?, ?, ?, ?, ?)')
        ->execute([$username, password_hash($password, PASSWORD_DEFAULT), $is_admin, $normalized['nome'], $normalized['sobrenome']]);
    app_flash_redirect('success', 'Usuário criado com sucesso.', 'create_user.php', true);
} catch (PDOException $e) {
    AppLogger::error('Erro ao criar usuário', ['error' => $e->getMessage()]);
    app_flash_redirect('error', 'Erro ao criar usuário.', 'create_user.php');
}
