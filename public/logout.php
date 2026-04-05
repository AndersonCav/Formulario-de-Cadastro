<?php
require_once __DIR__.'/../config/env.php';
require_once __DIR__.'/../config/session.php';

$_SESSION = [];

if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params['path'],
        $params['domain'],
        $params['secure'] ?? false,
        true
    );
}

session_destroy();
header('Location: index.php');
exit;
