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
    header('Location: view_forms.php'); exit;
}

$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
if (!$id) { header('Location: view_forms.php'); exit; }

try {
    $stmt = $pdo->prepare('DELETE FROM forms WHERE id = ?');
    $stmt->execute([$id]);
    Flash::set('success', $stmt->rowCount() > 0 ? 'Registro removido.' : 'Registro não encontrado.');
} catch (PDOException $e) {
    AppLogger::error('Erro ao remover formulário', ['id' => $id, 'error' => $e->getMessage()]);
    Flash::set('error', 'Erro ao remover registro.');
}
Csrf::regenerate();
header('Location: view_forms.php');
exit;
