<?php
require_once __DIR__.'/../config/env.php';
require_once __DIR__.'/../config/session.php';
require_once __DIR__.'/../src/helpers.php';

require_admin();

$pageTitle = 'Criar Usuário | Cadastro System';
include __DIR__.'/../views/partials/header.php';
include __DIR__.'/../views/partials/navbar.php';
require_once __DIR__.'/../src/Flash.php';
?>
<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-light text-center"><h2 class="mb-0">Criar Usuário</h2></div>
                <div class="card-body">
                    <?php Flash::renderIfPresent(); ?>
                    <form action="create_user_process.php" method="post">
                        <?php require_once __DIR__.'/../src/Csrf.php'; echo Csrf::field(); ?>
                        <div class="mb-3">
                            <label for="nome" class="form-label">Nome</label>
                            <input type="text" class="form-control" id="nome" name="nome" required>
                        </div>
                        <div class="mb-3">
                            <label for="sobrenome" class="form-label">Sobrenome</label>
                            <input type="text" class="form-control" id="sobrenome" name="sobrenome" required>
                        </div>
                        <div class="mb-3">
                            <label for="username" class="form-label">Nome de Usuário</label>
                            <input type="text" class="form-control" id="username" name="username" required minlength="3" maxlength="50">
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Senha</label>
                            <input type="password" class="form-control" id="password" name="password" required minlength="6">
                        </div>
                        <div class="mb-3">
                            <label for="is_admin" class="form-label">Administrador</label>
                            <select class="form-select" id="is_admin" name="is_admin">
                                <option value="0" selected>Não</option>
                                <option value="1">Sim</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary">Criar</button>
                        <a href="dashboard.php" class="btn btn-secondary">Cancelar</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include __DIR__.'/../views/partials/footer.php'; ?>
