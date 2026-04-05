<?php
require_once __DIR__.'/bootstrap.php';

require_login();
?>
<?php
$pageTitle = 'Painel | Cadastro System';
include __DIR__.'/../views/partials/header.php';
include __DIR__.'/../views/partials/navbar.php';
?>
<div class="container mt-5">
    <h1 class="text-center">Bem-vindo, <span style="color: #53a8b1"><?php echo htmlspecialchars($_SESSION['nome'] ?? 'Usuário'); ?></span>.</h1>
</div>
<?php include __DIR__.'/../views/partials/footer.php'; ?>
