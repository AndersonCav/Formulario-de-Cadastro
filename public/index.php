<?php
require_once __DIR__.'/../config/env.php';
require_once __DIR__.'/../config/session.php';
require_once __DIR__.'/../src/helpers.php';

require_login();

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once __DIR__.'/../config/database.php';
    require_once __DIR__.'/../src/Csrf.php';
    require_once __DIR__.'/../src/Logger.php';
    AppLogger::setLogDir(__DIR__.'/../storage/logs');

    if (!Csrf::verify()) {
        $error = 'Sessão expirada. Tente novamente.';
    } else {
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        if ($username === '' || $password === '') {
            $error = 'Usuário e senha são obrigatórios.';
        } else {
            // Proteção contra brute force: limita 5 tentativas por minuto
            $login_key = 'login_attempts_'.$_SERVER['REMOTE_ADDR'];
            if (!isset($_SESSION[$login_key])) {
                $_SESSION[$login_key] = ['count' => 0, 'time' => $_SERVER['REQUEST_TIME']];
            }
            $attempts = &$_SESSION[$login_key];
            if ($_SERVER['REQUEST_TIME'] - $attempts['time'] > 60) {
                $attempts = ['count' => 0, 'time' => $_SERVER['REQUEST_TIME']];
            }
            if ($attempts['count'] >= 5) {
                $error = 'Muitas tentativas. Aguarde um minuto.';
            } else {
                $stmt = $pdo->prepare('SELECT id, username, password, is_admin, nome, sobrenome FROM users WHERE username = ?');
                $stmt->execute([$username]);
                $user = $stmt->fetch();

                if ($user && password_verify($password, $user['password'])) {
                    session_regenerate_id(true);
                    unset($_SESSION[$login_key]);
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['is_admin'] = (int) $user['is_admin'];
                    $_SESSION['nome'] = $user['nome'];
                    $_SESSION['sobrenome'] = $user['sobrenome'];
                    Csrf::regenerate();
                    header('Location: dashboard.php');
                    exit;
                }

                $attempts['count']++;
                $error = 'Usuário ou senha incorretos.';
                AppLogger::error('Tentativa de login falhou', ['username' => $username, 'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown']);
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | Cadastro System</title>
    <link rel="stylesheet" href="./css/style.css">
</head>
<body>
    <div class="container">
        <div class="login-card">
            <h2 class="text-center mb-3">Login</h2>
            <form method="post" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
                <?php
                require_once __DIR__.'/../src/Csrf.php';
                echo Csrf::field();
                ?>
                <input type="text" name="username" placeholder="Usuário" required autocomplete="username">
                <input type="password" name="password" placeholder="Senha" required autocomplete="current-password">
                <button type="submit" class="login-button">Entrar</button>
            </form>
            <?php if ($error): ?>
                <div class="alert"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
