<?php
// views/partials/navbar.php
// Presume que session.php e helpers.php foram incluídos pela página pública
// que chama esse partial. Aqui não refazemos require_once para evitar
// redeclaração de funções.
?>
<nav class="navbar navbar-expand-lg navbar-custom">
    <div class="container">
        <a class="navbar-brand text-uppercase fw-bold text-primary" href="dashboard.php">
            Cadastro System
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="mainNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link" href="dashboard.php"><i class="fas fa-home"></i> Home</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="form.php"><i class="fas fa-edit"></i> Formulário</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="view_forms.php"><i class="fas fa-eye"></i> Ver Cadastros</a>
                </li>
                <?php if (is_admin()): ?>
                <li class="nav-item">
                    <a class="nav-link" href="export.php"><i class="fas fa-file-export"></i> Exportar</a>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                        <i class="fas fa-users"></i> Usuários
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="create_user.php"><i class="fas fa-user-plus"></i> Criar Usuário</a></li>
                        <li><a class="dropdown-item" href="view_users.php"><i class="fas fa-users"></i> Ver Usuários</a></li>
                    </ul>
                </li>
                <?php endif; ?>
            </ul>
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link" href="logout.php"><i class="fas fa-sign-out-alt"></i> Sair</a>
                </li>
            </ul>
        </div>
    </div>
</nav>
