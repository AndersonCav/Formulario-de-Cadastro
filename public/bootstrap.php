<?php

require_once __DIR__.'/../config/env.php';
require_once __DIR__.'/../config/session.php';
require_once __DIR__.'/../src/helpers.php';
require_once __DIR__.'/../src/Csrf.php';
require_once __DIR__.'/../src/Flash.php';
require_once __DIR__.'/../src/Logger.php';
require_once __DIR__.'/../src/Validator.php';
require_once __DIR__.'/../src/ValidationProfiles.php';
require_once __DIR__.'/../src/InputNormalizer.php';

function app_bootstrap(array $modules = []): void
{
    static $loaded = [];

    foreach ($modules as $module) {
        if (isset($loaded[$module])) {
            continue;
        }

        switch ($module) {
            case 'database':
                require_once __DIR__.'/../config/database.php';
                break;
            case 'csrf':
                break;
            case 'flash':
                break;
            case 'logger':
                break;
            case 'validator':
                break;
            case 'validation_profiles':
                break;
            default:
                throw new InvalidArgumentException('Módulo de bootstrap inválido: '.$module);
        }

        $loaded[$module] = true;
    }
}

if (!function_exists('app_require_post_csrf')) {
    function app_require_post_csrf(string $redirectPath, ?string $logMessage = null): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && Csrf::verify()) {
            return;
        }

        Csrf::regenerate();

        if ($logMessage !== null) {
            AppLogger::setLogDir(__DIR__.'/../storage/logs');
            AppLogger::error($logMessage, [
                'method' => $_SERVER['REQUEST_METHOD'] ?? 'unknown',
                'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            ]);
        }

        Flash::set('error', 'Sessão expirada ou requisição inválida. Tente novamente.');
        header('Location: '.$redirectPath);
        exit;
    }
}

if (!function_exists('app_redirect')) {
    function app_redirect(string $path): void
    {
        header('Location: '.$path);
        exit;
    }
}

if (!function_exists('app_flash_redirect')) {
    function app_flash_redirect(string $type, string $message, string $path, bool $regenerateCsrf = false): void
    {
        Flash::set($type, $message);

        if ($regenerateCsrf) {
            Csrf::regenerate();
        }

        app_redirect($path);
    }
}