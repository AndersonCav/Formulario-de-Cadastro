<?php
require_once __DIR__.'/../config/env.php';
require_once __DIR__.'/../config/session.php';
require_once __DIR__.'/../src/helpers.php';

require_login();

$is_admin = is_admin();
require_once __DIR__.'/../config/database.php';
require_once __DIR__.'/../src/Flash.php';

$page = max(1, (int) ($_GET['page'] ?? 1));
$perPage = 20;
$offset = ($page - 1) * $perPage;

if ($is_admin) {
    $countStmt = $pdo->query('SELECT COUNT(*) FROM forms');
    $total = $countStmt->fetchColumn();
} else {
    $c = $pdo->prepare('SELECT COUNT(*) FROM forms WHERE created_by_user_id = :uid');
    $c->execute([':uid' => user_id()]);
    $total = $c->fetchColumn();
}

$stmt = $pdo->prepare(
    $is_admin
        ? 'SELECT id, nome, telefone, celular, email, profissao, numero_registro, conselho, evento, cidade, estado, data_hora FROM forms ORDER BY data_hora ASC LIMIT :limit OFFSET :offset'
        : 'SELECT id, nome, telefone, celular, email, profissao, numero_registro, conselho, evento, cidade, estado, data_hora FROM forms WHERE created_by_user_id = :uid ORDER BY data_hora ASC LIMIT :limit OFFSET :offset'
);
if (!$is_admin) $stmt->bindValue(':uid', user_id(), PDO::PARAM_INT);
$stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$forms = $stmt->fetchAll();
$totalPages = max(1, (int) ceil($total / $perPage));
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastros | Cadastro System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="./css/navbar.css">
</head>
<body>
<?php include __DIR__.'/../views/partials/navbar.php'; ?>
<div class="container mt-4">
    <h2>Cadastros Realizados</h2>
    <?php Flash::renderIfPresent(); ?>
    <div class="table-responsive mt-3">
        <table id="cadastrosTable" class="table table-striped table-hover">
            <thead class="table-dark">
                <tr>
                    <th>Nome</th><th>Telefone</th><th>Celular</th><th>E-mail</th><th>Profissão</th>
                    <th>Nº Registro</th><th>Conselho</th><th>Evento</th><th>Cidade</th><th>Estado</th><th>Data</th>
                    <?php if ($is_admin): ?><th>Editar</th><th>Remover</th><?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($forms as $row): ?>
                <tr>
                    <td><?= htmlspecialchars($row['nome']) ?></td>
                    <td><?= htmlspecialchars($row['telefone']) ?></td>
                    <td><?= htmlspecialchars($row['celular']) ?></td>
                    <td><?= htmlspecialchars($row['email']) ?></td>
                    <td><?= htmlspecialchars($row['profissao']) ?></td>
                    <td><?= htmlspecialchars($row['numero_registro']) ?></td>
                    <td><?= htmlspecialchars($row['conselho'] ?? '') ?></td>
                    <td><?= htmlspecialchars($row['evento'] ?? '') ?></td>
                    <td><?= htmlspecialchars($row['cidade']) ?></td>
                    <td><?= htmlspecialchars($row['estado']) ?></td>
                    <td><?= date('d/m/Y', strtotime($row['data_hora'])) ?></td>
                    <?php if ($is_admin): ?>
                    <td><a href="edit_form.php?id=<?= (int)$row['id'] ?>" class="btn btn-outline-primary btn-sm"><i class="fas fa-edit"></i></a></td>
                    <td>
                        <form method="post" action="remove_form.php" class="d-inline" onsubmit="return confirm('Remover este cadastro?')">
                            <input type="hidden" name="id" value="<?= (int)$row['id'] ?>">
                            <?php require_once __DIR__.'/../src/Csrf.php'; echo Csrf::field(); ?>
                            <button type="submit" class="btn btn-outline-danger btn-sm"><i class="fas fa-trash-alt"></i></button>
                        </form>
                    </td>
                    <?php endif; ?>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($forms)): ?>
                <tr><td colspan="<?= $is_admin ? 13 : 11 ?>" class="text-center">Nenhum cadastro encontrado.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <?php if ($totalPages > 1): ?>
    <nav class="mt-3">
        <ul class="pagination justify-content-center">
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
            <li class="page-item <?= $i === $page ? 'active' : '' ?>"><a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a></li>
            <?php endfor; ?>
        </ul>
    </nav>
    <?php endif; ?>
</div>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
<script>
    $('#cadastrosTable').DataTable({
        "paging":false,"searching":true,"ordering":true,"info":false,
        "autoWidth":false,"responsive":true,"order":[[10,'asc']],
        "language":{
            "paginate":{"first":"Primeiro","last":"Último","next":"Próximo","previous":"Anterior"},
            "search":"Pesquisar:","infoFiltered":"(filtrado de _MAX_ registros totais)"
        },
        "columnDefs":[{
            "targets":10,
            "render":function(d,t){if(t==='sort'||t==='type')return d.split('/').reverse().join('');return d;}
        }]
    });
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
