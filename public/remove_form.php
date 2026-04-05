<?php
require_once __DIR__.'/bootstrap.php';
app_bootstrap(['database', 'csrf', 'flash', 'logger']);
require_admin();
AppLogger::setLogDir(__DIR__.'/../storage/logs');

app_require_post_csrf('view_forms.php', 'Falha de validação CSRF ao remover formulário');

$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
if (!$id) {
    app_redirect('view_forms.php');
}

try {
    $stmt = $pdo->prepare('DELETE FROM forms WHERE id = ?');
    $stmt->execute([$id]);
    app_flash_redirect('success', $stmt->rowCount() > 0 ? 'Registro removido.' : 'Registro não encontrado.', 'view_forms.php', true);
} catch (PDOException $e) {
    AppLogger::error('Erro ao remover formulário', ['id' => $id, 'error' => $e->getMessage()]);
    app_flash_redirect('error', 'Erro ao remover registro.', 'view_forms.php');
}
