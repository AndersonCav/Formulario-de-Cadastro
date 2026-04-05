<?php
require_once __DIR__.'/../config/env.php';
require_once __DIR__.'/../config/session.php';

// Admin only
if (!isset($_SESSION['user_id']) || (int) ($_SESSION['is_admin'] ?? 0) !== 1) {
    header('Location: dashboard.php');
    exit;
}

require_once __DIR__.'/../config/database.php';
$stmt = $pdo->query('SELECT id, username, nome, sobrenome, is_admin FROM users ORDER BY nome ASC, sobrenome ASC');
$users = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Usuários | Cadastro System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link rel="stylesheet" href="./css/navbar.css">
</head>
<body>
<?php
$is_admin = true;
include __DIR__.'/../views/partials/header.php';
include __DIR__.'/../views/partials/navbar.php';
?>
<div class="container mt-4">
    <h2>Usuários Cadastrados</h2>
    <div class="table-responsive mt-3">
        <table class="table table-hover shadow">
            <thead class="table-dark">
                <tr>
                    <th>Nome Completo</th>
                    <th>Nome de Usuário</th>
                    <th>Tipo</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                <tr>
                    <td><?php echo htmlspecialchars($user['nome'] . ' ' . $user['sobrenome']); ?></td>
                    <td><?php echo htmlspecialchars($user['username']); ?></td>
                    <td><?php echo (int) $user['is_admin'] === 1 ? '<span class="badge bg-primary">Admin</span>' : '<span class="badge bg-secondary">Usuário</span>'; ?></td>
                    <td>
                        <a href="edit_user.php?id=<?php echo (int)$user['id']; ?>" class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-edit"></i> Editar
                        </a>
                        <?php if ((int)$user['id'] !== (int)$_SESSION['user_id']): ?>
                        <form method="post" action="remove_user.php" class="d-inline" onsubmit="return confirm('Tem certeza que deseja remover este usuário?');">
                            <input type="hidden" name="id" value="<?php echo (int)$user['id']; ?>">
                            <?php require_once __DIR__.'/../src/Csrf.php'; echo Csrf::field(); ?>
                            <button type="submit" class="btn btn-outline-danger btn-sm">
                                <i class="fas fa-trash-alt"></i> Remover
                            </button>
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
