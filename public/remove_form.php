<?php
require_once __DIR__.'/../config/env.php';
require_once __DIR__.'/../config/session.php';

// Admin apenas
if (!isset($_SESSION['user_id']) || (int) ($_SESSION['is_admin'] ?? 0) !== 1) {
    header('Location: dashboard.php');
    exit;
}

require_once __DIR__.'/../config/database.php';
require_once __DIR__.'/../src/Csrf.php';
require_once __DIR__.'/../src/Logger.php';
require_once __DIR__.'/../src/Flash.php';

AppLogger::setLogDir(__DIR__.'/../storage/logs');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: view_forms.php');
    exit;
}

if (!Csrf::verify()) {
    Flash::set('error', 'Requisição inválida. Tente novamente.');
    header('Location: view_forms.php');
    exit;
}

$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
if (!$id) {
    Flash::set('error', 'ID inválido.');
    header('Location: view_forms.php');
    exit;
}

try {
    $stmt = $pdo->prepare('DELETE FROM forms WHERE id = :id');
    $stmt->execute([':id' => $id]);

    if ($stmt->rowCount() > 0) {
        Flash::set('success', 'Registro removido com sucesso.');
    } else {
        Flash::set('error', 'Registro não encontrado.');
    }
} catch (PDOException $e) {
    AppLogger::error('Erro ao remover formulário', ['id' => $id, 'error' => $e->getMessage()]);
    Flash::set('error', 'Erro ao remover registro.');
}

Csrf::regenerate();
header('Location: view_forms.php');
exit;
