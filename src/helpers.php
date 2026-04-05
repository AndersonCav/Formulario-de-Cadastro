<?php
/**
 * Helpers globais.
 */

if (!function_exists('get_base_path')) {
    function get_base_path(): string
    {
        // Detecta o caminho base da aplicação a partir do arquivo de entry point
        $script = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
        return $script ?: '';
    }
}

if (!function_exists('redirect')) {
    function redirect(string $path): void
    {
        header('Location: '.get_base_path().'/'.$path);
        exit;
    }
}

if (!function_exists('require_login')) {
    function require_login(): void
    {
        if (empty($_SESSION['user_id'])) {
            redirect('index.php');
        }
    }
}

if (!function_exists('require_admin')) {
    function require_admin(): void
    {
        require_login();
        if ((int) ($_SESSION['is_admin'] ?? 0) !== 1) {
            redirect('dashboard.php');
        }
    }
}

if (!function_exists('is_admin')) {
    function is_admin(): bool
    {
        return (int) ($_SESSION['is_admin'] ?? 0) === 1;
    }
}

if (!function_exists('user_id')) {
    function user_id(): int
    {
        return (int) ($_SESSION['user_id'] ?? 0);
    }
}
