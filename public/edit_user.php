<?php
require_once __DIR__.'/bootstrap.php';
app_bootstrap(['database', 'flash', 'csrf']);

require_admin();

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) { header('Location: view_users.php'); exit; }

$stmt = $pdo->prepare('SELECT id, username, nome, sobrenome, is_admin FROM users WHERE id = :id');
$stmt->execute([':id' => $id]);
$user = $stmt->fetch();
if (!$user) { header('Location: view_users.php'); exit; }
?>
<?php
$pageTitle = 'Editar Usuário | Cadastro System';
include __DIR__.'/../views/partials/header.php';
include __DIR__.'/../views/partials/navbar.php';
?>
<div class="container mt-4">
    <h2>Editar Usuário</h2>
    <?php Flash::renderIfPresent(); ?>
    <form action="update_user.php" method="post" class="mt-3">
        <input type="hidden" name="id" value="<?php echo (int)$user['id']; ?>">
        <?php echo Csrf::field(); ?>
        <div class="mb-3">
            <label for="username" class="form-label">Nome de Usuário</label>
            <input type="text" class="form-control" id="username" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required>
        </div>
        <div class="mb-3">
            <label for="nome" class="form-label">Nome</label>
            <input type="text" class="form-control" id="nome" name="nome" value="<?php echo htmlspecialchars($user['nome'] ?? ''); ?>" required>
        </div>
        <div class="mb-3">
            <label for="sobrenome" class="form-label">Sobrenome</label>
            <input type="text" class="form-control" id="sobrenome" name="sobrenome" value="<?php echo htmlspecialchars($user['sobrenome'] ?? ''); ?>" required>
        </div>
        <div class="mb-3">
            <label for="password" class="form-label">Senha</label>
            <input type="password" class="form-control" id="password" name="password" minlength="6">
            <small class="form-text text-muted">Deixe em branco para manter a senha atual.</small>
        </div>
        <div class="mb-3">
            <label for="is_admin" class="form-label">Tipo de Usuário</label>
            <select class="form-select" id="is_admin" name="is_admin">
                <option value="0" <?php echo $user['is_admin'] == 0 ? 'selected' : ''; ?>>Usuário</option>
                <option value="1" <?php echo $user['is_admin'] == 1 ? 'selected' : ''; ?>>Administrador</option>
            </select>
        </div>
        <button type="submit" class="btn btn-primary">Atualizar</button>
        <a href="view_users.php" class="btn btn-secondary">Cancelar</a>
    </form>
</div>
<?php include __DIR__.'/../views/partials/footer.php'; ?>
