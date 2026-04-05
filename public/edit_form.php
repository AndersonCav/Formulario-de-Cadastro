<?php
require_once __DIR__.'/../config/env.php';
require_once __DIR__.'/../config/session.php';
require_once __DIR__.'/../src/helpers.php';
require_once __DIR__.'/../src/Csrf.php';
require_once __DIR__.'/../config/database.php';

require_admin();

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) {
    header('Location: view_forms.php');
    exit;
}

$stmt = $pdo->prepare('SELECT * FROM forms WHERE id = :id');
$stmt->execute([':id' => $id]);
$form = $stmt->fetch();

if (!$form) {
    header('Location: view_forms.php');
    exit;
}

$profissoes = [
    'Médico','Dentista','Veterinário','Esteticista','Psicólogo',
    'Farmacêutico','Biomédico','Nutricionista','Fisioterapeuta',
    'Terapeuta','Enfermeiro','Educador Físico','Farmacêutico Estético'
];
$estados = [
    'AC'=>'Acre','AL'=>'Alagoas','AP'=>'Amapá','AM'=>'Amazonas',
    'BA'=>'Bahia','CE'=>'Ceará','DF'=>'Distrito Federal','ES'=>'Espírito Santo',
    'GO'=>'Goiás','MA'=>'Maranhão','MT'=>'Mato Grosso','MS'=>'Mato Grosso do Sul',
    'MG'=>'Minas Gerais','PA'=>'Pará','PB'=>'Paraíba','PR'=>'Paraná',
    'PE'=>'Pernambuco','PI'=>'Piauí','RJ'=>'Rio de Janeiro','RN'=>'Rio Grande do Norte',
    'RS'=>'Rio Grande do Sul','RO'=>'Rondônia','RR'=>'Roraima','SC'=>'Santa Catarina',
    'SE'=>'Sergipe','SP'=>'São Paulo','TO'=>'Tocantins'
];
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Cadastro | Cadastro System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link rel="stylesheet" href="./css/navbar.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>
</head>
<body>
<?php include __DIR__.'/../views/partials/navbar.php'; ?>
<div class="container mt-5">
    <h2>Editar Cadastro</h2>
    <form action="update_form.php" method="post" class="row g-3 mt-2">
        <input type="hidden" name="id" value="<?= (int)$form['id'] ?>">
        <?php echo Csrf::field(); ?>
        <div class="col-md-6">
            <label for="nome" class="form-label">Nome</label>
            <input type="text" name="nome" id="nome" class="form-control" value="<?= htmlspecialchars($form['nome']) ?>" required>
        </div>
        <div class="col-md-6">
            <label for="telefone" class="form-label">Telefone</label>
            <input type="text" name="telefone" id="telefone" class="form-control" value="<?= htmlspecialchars($form['telefone']) ?>">
        </div>
        <div class="col-md-6">
            <label for="celular" class="form-label">Celular</label>
            <input type="text" name="celular" id="celular" class="form-control" value="<?= htmlspecialchars($form['celular']) ?>">
        </div>
        <div class="col-md-6">
            <label for="email" class="form-label">E-mail</label>
            <input type="email" name="email" id="email" class="form-control" value="<?= htmlspecialchars($form['email'] ?? '') ?>">
        </div>
        <div class="col-md-6">
            <label for="profissao" class="form-label">Profissão</label>
            <input type="text" name="profissao" id="profissao" class="form-control" value="<?= htmlspecialchars($form['profissao']) ?>">
        </div>
        <div class="col-md-6">
            <label for="numero_registro" class="form-label">Nº de Registro</label>
            <input type="text" name="numero_registro" id="numero_registro" class="form-control" value="<?= htmlspecialchars($form['numero_registro']) ?>">
        </div>
        <div class="col-md-6">
            <label for="conselho" class="form-label">Conselho</label>
            <input type="text" name="conselho" id="conselho" class="form-control" value="<?= htmlspecialchars($form['conselho'] ?? '') ?>">
        </div>
        <div class="col-md-6">
            <label for="evento" class="form-label">Evento</label>
            <input type="text" name="evento" id="evento" class="form-control" value="<?= htmlspecialchars($form['evento'] ?? '') ?>">
        </div>
        <div class="col-md-6">
            <label for="cidade" class="form-label">Cidade</label>
            <input type="text" name="cidade" id="cidade" class="form-control" value="<?= htmlspecialchars($form['cidade']) ?>" required>
        </div>
        <div class="col-md-6">
            <label for="estado" class="form-label">Estado</label>
            <select name="estado" id="estado" class="form-select" required>
                <option value="">Selecione</option>
                <?php foreach ($estados as $sigla => $nomeEstado):
                    $sel = $form['estado'] === $sigla ? 'selected' : '';
                ?>
                    <option value="<?= htmlspecialchars($sigla); ?>" <?= $sel; ?>><?= htmlspecialchars($nomeEstado); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-12">
            <button type="submit" class="btn btn-primary">Atualizar</button>
            <a href="view_forms.php" class="btn btn-secondary">Cancelar</a>
        </div>
    </form>
</div>
<script>
    $(document).ready(function(){
        $('#telefone').mask('(00) 0000-0000');
        $('#celular').mask('(00) 00000-0000');
    });
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
