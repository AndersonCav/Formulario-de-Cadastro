<?php
require_once __DIR__.'/../config/env.php';
require_once __DIR__.'/../config/session.php';
require_once __DIR__.'/../src/helpers.php';
require_admin();

require_once __DIR__.'/../config/database.php';
require_once __DIR__.'/../src/Csrf.php';
require_once __DIR__.'/../src/Flash.php';
require_once __DIR__.'/../src/Logger.php';
AppLogger::setLogDir(__DIR__.'/../storage/logs');

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !Csrf::verify()) {
    header('Location: view_users.php'); exit;
}

$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
if (!$id) { header('Location: view_users.php'); exit; }

if ($id === user_id()) {
    Flash::set('error', 'Você não pode remover a si mesmo.');
    header('Location: view_users.php'); exit;
}

try {
    $stmt = $pdo->prepare('DELETE FROM users WHERE id = ?');
    $stmt->execute([$id]);
    Flash::set('success', 'Usuário removido com sucesso.');
} catch (PDOException $e) {
    AppLogger::error('Erro ao remover usuário', ['id' => $id, 'error' => $e->getMessage()]);
    Flash::set('error', 'Erro ao remover usuário.');
}
Csrf::regenerate();
header('Location: view_users.php');
exit;
