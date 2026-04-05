<?php
/**
 * Helpers globais.
 */

function get_base_path(): string
{
    // Detecta o caminho base da aplicação a partir do arquivo de entry point
    $script = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
    return $script ?: '';
}

function redirect(string $path): void
{
    header('Location: '.get_base_path().'/'.$path);
    exit;
}

function require_login(): void
{
    if (empty($_SESSION['user_id'])) {
        redirect('index.php');
    }
}

function require_admin(): void
{
    require_login();
    if ((int) ($_SESSION['is_admin'] ?? 0) !== 1) {
        redirect('dashboard.php');
    }
}

function is_admin(): bool
{
    return (int) ($_SESSION['is_admin'] ?? 0) === 1;
}

function user_id(): int
{
    return (int) ($_SESSION['user_id'] ?? 0);
}
