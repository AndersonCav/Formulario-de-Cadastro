<?php
require_once __DIR__.'/../config/env.php';
require_once __DIR__.'/../config/session.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

$is_admin = (int) ($_SESSION['is_admin'] ?? 0) === 1;
require_once __DIR__.'/../config/database.php';

// Admin vê todos, user vê apenas os próprios
if ($is_admin) {
    $stmt = $pdo->query('SELECT id, nome, telefone, celular, email, representante, profissao, numero_registro, conselho, evento, cidade, estado, data_hora FROM forms ORDER BY data_hora ASC');
} else {
    $stmt = $pdo->prepare('SELECT id, nome, telefone, celular, email, representante, profissao, numero_registro, conselho, evento, cidade, estado, data_hora FROM forms WHERE created_by_user_id = :user_id ORDER BY data_hora ASC');
    $stmt->execute([':user_id' => (int) $_SESSION['user_id']]);
}
$forms = $stmt->fetchAll();
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
    <style>
        table { box-shadow: 0 2px 4px rgba(0,0,0,0.1); border-radius: 8px; overflow: hidden; }
        table thead { background-color: #3ea5af; color: #fff; }
        .dataTables_filter { display: none; }
    </style>
</head>
<body>
<?php include __DIR__.'/../views/partials/navbar.php'; ?>
<div class="container mt-4">
    <h2>Cadastros Realizados</h2>
    <div class="table-responsive mt-3">
        <table id="cadastrosTable" class="table table-striped table-hover">
            <thead>
                <tr>
                    <th>Nome</th>
                    <th>Telefone</th>
                    <th>Celular</th>
                    <th>E-mail</th>
                    <th>Profissão</th>
                    <th>Nº Registro</th>
                    <th>Conselho</th>
                    <th>Evento</th>
                    <th>Cidade</th>
                    <th>Estado</th>
                    <th>Data</th>
                    <?php if ($is_admin): ?>
                    <th>Editar</th>
                    <th>Remover</th>
                    <?php endif; ?>
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
                    <td><?= htmlspecialchars($row['conselho']) ?></td>
                    <td><?= htmlspecialchars($row['evento']) ?></td>
                    <td><?= htmlspecialchars($row['cidade']) ?></td>
                    <td><?= htmlspecialchars($row['estado']) ?></td>
                    <td><?= date('d/m/Y', strtotime($row['data_hora'])) ?></td>
                    <?php if ($is_admin): ?>
                    <td><a href="edit_form.php?id=<?= (int)$row['id'] ?>" class="btn btn-outline-primary btn-sm"><i class="fas fa-edit"></i></a></td>
                    <td>
                        <form method="post" action="remove_form.php" class="d-inline" onsubmit="return confirm('Tem certeza que deseja remover este cadastro?')">
                            <input type="hidden" name="id" value="<?= (int)$row['id'] ?>">
                            <?php require_once __DIR__.'/../config/env.php'; require_once __DIR__.'/../src/Csrf.php'; echo Csrf::field(); ?>
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
</div>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
<script>
    $(document).ready(function(){
        $('#cadastrosTable').DataTable({
            "paging": true, "lengthChange": false, "pageLength": 10,
            "searching": true, "ordering": true, "info": true,
            "autoWidth": false, "responsive": true,
            "order": [[10, 'asc']],
            "language": {
                "paginate": {"first":"Primeiro","last":"Último","next":"Próximo","previous":"Anterior"},
                "info": "Mostrando _START_ a _END_ de _TOTAL_ registros",
                "infoEmpty": "Mostrando 0 a 0 de 0 registros",
                "emptyTable": "Nenhum cadastro encontrado",
                "search": "Pesquisar:",
                "infoFiltered": "(filtrado de _MAX_ registros totais)"
            },
            "columnDefs": [{
                "targets": 10,
                "render": function(data, type, row){
                    if(type === 'sort' || type === 'type') return data.split('/').reverse().join('');
                    return data;
                }
            }]
        });
    });
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
<?php include __DIR__.'/../views/partials/footer.php'; ?>
