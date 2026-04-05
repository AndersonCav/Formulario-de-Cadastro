<?php
require_once __DIR__.'/bootstrap.php';
app_bootstrap(['database', 'csrf', 'flash', 'logger']);
require_admin();
AppLogger::setLogDir(__DIR__.'/../storage/logs');

app_require_post_csrf('view_users.php', 'Falha de validação CSRF ao remover usuário');

$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
if (!$id) {
    app_redirect('view_users.php');
}

if ($id === user_id()) {
    app_flash_redirect('error', 'Você não pode remover a si mesmo.', 'view_users.php');
}

try {
    $stmt = $pdo->prepare('DELETE FROM users WHERE id = ?');
    $stmt->execute([$id]);
    app_flash_redirect('success', 'Usuário removido com sucesso.', 'view_users.php', true);
} catch (PDOException $e) {
    AppLogger::error('Erro ao remover usuário', ['id' => $id, 'error' => $e->getMessage()]);
    app_flash_redirect('error', 'Erro ao remover usuário.', 'view_users.php');
}
