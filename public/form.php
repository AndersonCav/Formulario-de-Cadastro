<?php
require_once __DIR__.'/../config/env.php';
require_once __DIR__.'/../config/session.php';
require_once __DIR__.'/../src/helpers.php';

require_login();

// Pega dados do POST falho via sessão em vez de GET (evita URL estourar)
$session_errors = $_SESSION['__form_errors'] ?? [];
$session_old    = $_SESSION['__form_old'] ?? [];
unset($_SESSION['__form_errors'], $_SESSION['__form_old']);

$errors = !empty($session_errors) ? $session_errors : (isset($_GET['errors']) ? json_decode($_GET['errors'], true) : []);
$success = isset($_GET['success']) && $_GET['success'] === 'true';
$old     = !empty($session_old) ? $session_old : (isset($_GET['old']) ? json_decode($_GET['old'], true) : []);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Formulário | Cadastro System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link rel="stylesheet" href="./css/navbar.css">
    <style>
        .card { border-radius: 15px; box-shadow: 0 4px 8px rgba(0,0,0,0.1); }
        .card-header { background-color: #51adb4; color: white; border-radius: 15px 15px 0 0; }
        .btn-primary { background-color: #51adb4; border: none; }
        .btn-primary:hover { background-color: #418a8e; }
    </style>
</head>
<body>
<?php include __DIR__.'/../views/partials/navbar.php'; ?>
<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header text-center"><h2 class="mb-0">Preencha o Formulário</h2></div>
                <div class="card-body">
                    <?php if ($success): ?>
                        <div class="alert alert-success">Formulário enviado com sucesso!</div>
                    <?php endif; ?>
                    <?php if (!empty($errors) && is_array($errors)): ?>
                        <div class="alert alert-danger"><ul class="mb-0"><?php foreach ($errors as $err): ?><li><?= htmlspecialchars($err) ?></li><?php endforeach; ?></ul></div>
                    <?php endif; ?>
                    <form method="post" action="process_form.php">
                        <?php require_once __DIR__.'/../src/Csrf.php'; echo Csrf::field(); ?>
                        <div class="mb-3">
                            <label for="nome" class="form-label">Nome</label>
                            <input type="text" class="form-control" id="nome" name="nome" required value="<?= htmlspecialchars($old['nome'] ?? '') ?>">
                        </div>
                        <div class="mb-3">
                            <label for="telefone" class="form-label">Telefone</label>
                            <input type="text" class="form-control" id="telefone" name="telefone" required value="<?= htmlspecialchars($old['telefone'] ?? '') ?>">
                        </div>
                        <div class="mb-3">
                            <label for="celular" class="form-label">Celular</label>
                            <input type="text" class="form-control" id="celular" name="celular" required value="<?= htmlspecialchars($old['celular'] ?? '') ?>">
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">E-Mail</label>
                            <input type="email" class="form-control" id="email" name="email" required value="<?= htmlspecialchars($old['email'] ?? '') ?>">
                        </div>
                        <div class="mb-3">
                            <label for="profissao" class="form-label">Profissão</label>
                            <select class="form-select" id="profissao" name="profissao" required>
                                <option value="">Selecione</option>
                                <?php foreach (['Médico','Dentista','Veterinário','Esteticista','Psicólogo','Farmacêutico','Biomédico','Nutricionista','Fisioterapeuta','Terapeuta','Enfermeiro','Educador Físico','Farmacêutico Estético'] as $p): ?>
                                    <option value="<?= htmlspecialchars($p) ?>" <?= ($old['profissao'] ?? '') === $p ? 'selected' : '' ?>><?= htmlspecialchars($p) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="numero_registro" class="form-label">Nº de Registro</label>
                            <input type="text" class="form-control" id="numero_registro" name="numero_registro" required value="<?= htmlspecialchars($old['numero_registro'] ?? '') ?>">
                        </div>
                        <div class="mb-3">
                            <label for="conselho" class="form-label">Conselho</label>
                            <input type="text" class="form-control" id="conselho" name="conselho" required value="<?= htmlspecialchars($old['conselho'] ?? '') ?>">
                        </div>
                        <div class="mb-3">
                            <label for="evento" class="form-label">Evento</label>
                            <input type="text" class="form-control" id="evento" name="evento" required value="<?= htmlspecialchars($old['evento'] ?? '') ?>">
                        </div>
                        <div class="mb-3">
                            <label for="cidade" class="form-label">Cidade</label>
                            <input type="text" class="form-control" id="cidade" name="cidade" required value="<?= htmlspecialchars($old['cidade'] ?? '') ?>">
                        </div>
                        <div class="mb-3">
                            <label for="estado" class="form-label">Estado</label>
                            <select class="form-select" id="estado" name="estado" required>
                                <option value="">Selecione</option>
                                <?php foreach (['AC'=>'Acre','AL'=>'Alagoas','AP'=>'Amapá','AM'=>'Amazonas','BA'=>'Bahia','CE'=>'Ceará','DF'=>'Distrito Federal','ES'=>'Espírito Santo','GO'=>'Goiás','MA'=>'Maranhão','MT'=>'Mato Grosso','MS'=>'Mato Grosso do Sul','MG'=>'Minas Gerais','PA'=>'Pará','PB'=>'Paraíba','PR'=>'Paraná','PE'=>'Pernambuco','PI'=>'Piauí','RJ'=>'Rio de Janeiro','RN'=>'Rio Grande do Norte','RS'=>'Rio Grande do Sul','RO'=>'Rondônia','RR'=>'Roraima','SC'=>'Santa Catarina','SE'=>'Sergipe','SP'=>'São Paulo','TO'=>'Tocantins'] as $sigla => $nomeEstado): ?>
                                    <option value="<?= htmlspecialchars($sigla) ?>" <?= ($old['estado'] ?? '') === $sigla ? 'selected' : '' ?>><?= htmlspecialchars($nomeEstado) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Enviar</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>
<script>$(function(){$('#telefone').mask('(00) 0000-0000');$('#celular').mask('(00) 00000-0000');});</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
