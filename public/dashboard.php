<?php
require_once __DIR__.'/../config/env.php';
require_once __DIR__.'/../config/session.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}
?>
<?php
$pageTitle = 'Painel | Cadastro System';
$is_admin = (int) ($_SESSION['is_admin'] ?? 0) === 1;
$nome = htmlspecialchars($_SESSION['nome'] ?? 'Usuário');
include __DIR__.'/../views/partials/header.php';
include __DIR__.'/../views/partials/navbar.php';
?>
<div class="container mt-5">
    <h1 class="text-center">Bem-vindo, <span style="color: #53a8b1"><?php echo $nome; ?></span>.</h1>
</div>
<?php include __DIR__.'/../views/partials/footer.php'; ?>
