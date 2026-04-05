<?php
require_once __DIR__.'/bootstrap.php';
app_bootstrap(['database', 'csrf', 'validator', 'validation_profiles', 'flash', 'logger']);
require_admin();
AppLogger::setLogDir(__DIR__.'/../storage/logs');

app_require_post_csrf('view_users.php', 'Falha de validação CSRF ao atualizar usuário');

$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
if (!$id) {
    app_redirect('view_users.php');
}

$data = $_POST;
$valid = new Validator($data);
ValidationProfiles::validateUserIdentity($valid);

if ($valid->fails()) {
    app_flash_redirect('error', $valid->firstError(), 'edit_user.php?id='.$id);
}

$normalized = InputNormalizer::userPayload($data);
$password = $normalized['password'];
if (($passwordError = ValidationProfiles::validatePassword($password, false)) !== null) {
    app_flash_redirect('error', $passwordError, 'edit_user.php?id='.$id);
}

$username = $normalized['username'];
// Check uniqueness
$stmt = $pdo->prepare('SELECT id FROM users WHERE username = :uname AND id != :id');
$stmt->execute([':uname' => $username, ':id' => $id]);
if ($stmt->fetch()) {
    app_flash_redirect('error', 'Nome de usuário já está em uso.', 'edit_user.php?id='.$id);
}

$is_admin = $normalized['is_admin'];

try {
    if ($password !== '') {
        $pdo->prepare('UPDATE users SET username = ?, password = ?, is_admin = ?, nome = ?, sobrenome = ? WHERE id = ?')
            ->execute([$username, password_hash($password, PASSWORD_DEFAULT), $is_admin, $normalized['nome'], $normalized['sobrenome'], $id]);
    } else {
        $pdo->prepare('UPDATE users SET username = ?, is_admin = ?, nome = ?, sobrenome = ? WHERE id = ?')
            ->execute([$username, $is_admin, $normalized['nome'], $normalized['sobrenome'], $id]);
    }
    app_flash_redirect('success', 'Usuário atualizado com sucesso.', 'view_users.php', true);
} catch (PDOException $e) {
    AppLogger::error('Erro ao atualizar usuário', ['id' => $id, 'error' => $e->getMessage()]);
    app_flash_redirect('error', 'Erro ao atualizar usuário.', 'view_users.php');
}
