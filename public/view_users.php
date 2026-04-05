<?php
require_once __DIR__.'/bootstrap.php';
app_bootstrap(['database', 'csrf', 'flash']);

require_admin();

$stmt = $pdo->query('SELECT id, username, nome, sobrenome, is_admin FROM users ORDER BY nome ASC, sobrenome ASC');
$users = $stmt->fetchAll();

$pageTitle = 'Usuários | Cadastro System';
include __DIR__.'/../views/partials/header.php';
include __DIR__.'/../views/partials/navbar.php';
?>
<div class="container mt-4">
    <h2>Usuários Cadastrados</h2>
    <?php Flash::renderIfPresent(); ?>
    <div class="table-responsive mt-3">
        <table class="table table-hover shadow">
            <thead class="table-dark">
                <tr><th>Nome Completo</th><th>Nome de Usuário</th><th>Tipo</th><th>Ações</th></tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                <tr>
                    <td><?= htmlspecialchars($user['nome'].' '.$user['sobrenome']) ?></td>
                    <td><?= htmlspecialchars($user['username']) ?></td>
                    <td><?= (int)$user['is_admin'] === 1 ? '<span class="badge bg-primary">Admin</span>' : '<span class="badge bg-secondary">Usuário</span>' ?></td>
                    <td>
                        <a href="edit_user.php?id=<?= (int)$user['id'] ?>" class="btn btn-outline-primary btn-sm"><i class="fas fa-edit"></i> Editar</a>
                        <?php if ((int)$user['id'] !== user_id()): ?>
                        <form method="post" action="remove_user.php" class="d-inline" onsubmit="return confirm('Remover este usuário?');">
                            <input type="hidden" name="id" value="<?= (int)$user['id'] ?>">
                            <?php echo Csrf::field(); ?>
                            <button type="submit" class="btn btn-outline-danger btn-sm"><i class="fas fa-trash-alt"></i> Remover</button>
                        </form>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php include __DIR__.'/../views/partials/footer.php'; ?>
