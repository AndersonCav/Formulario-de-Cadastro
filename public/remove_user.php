<?php
require_once __DIR__.'/../config/env.php';
require_once __DIR__.'/../config/session.php';

// Admin only, e impede auto-deleção
if (!isset($_SESSION['user_id']) || (int) ($_SESSION['is_admin'] ?? 0) !== 1) {
    header('Location: dashboard.php');
    exit;
}

require_once __DIR__.'/../config/database.php';
require_once __DIR__.'/../src/Csrf.php';
require_once __DIR__.'/../src/Flash.php';
require_once __DIR__.'/../src/Logger.php';

AppLogger::setLogDir(__DIR__.'/../storage/logs');

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !Csrf::verify()) {
    header('Location: view_users.php');
    exit;
}

$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
if (!$id) {
    Flash::set('error', 'ID inválido.');
    header('Location: view_users.php');
    exit;
}

// Impede que o usuário remova a si mesmo
if ((int) $id === (int) $_SESSION['user_id']) {
    Flash::set('error', 'Você não pode remover a si mesmo.');
    header('Location: view_users.php');
    exit;
}

try {
    $stmt = $pdo->prepare('DELETE FROM users WHERE id = :id');
    $stmt->execute([':id' => $id]);

    if ($stmt->rowCount() > 0) {
        Flash::set('success', 'Usuário removido com sucesso.');
    } else {
        Flash::set('error', 'Usuário não encontrado.');
    }
} catch (PDOException $e) {
    AppLogger::error('Erro ao remover usuário', ['id' => $id, 'error' => $e->getMessage()]);
    Flash::set('error', 'Erro ao remover usuário.');
}

Csrf::regenerate();
header('Location: view_users.php');
exit;
