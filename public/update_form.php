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

$email = trim($_POST['email'] ?? '');
if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    Flash::set('error', 'E-mail inválido.');
    header('Location: edit_form.php?id='.$id); exit;
}

try {
    $pdo->prepare('UPDATE forms SET nome=?,telefone=?,celular=?,email=?,profissao=?,numero_registro=?,conselho=?,evento=?,cidade=?,estado=? WHERE id=?')
        ->execute([
            trim($_POST['nome'] ?? ''), trim($_POST['telefone'] ?? ''), trim($_POST['celular'] ?? ''),
            $email, trim($_POST['profissao'] ?? ''), trim($_POST['numero_registro'] ?? ''),
            trim($_POST['conselho'] ?? ''), trim($_POST['evento'] ?? ''), trim($_POST['cidade'] ?? ''),
            trim($_POST['estado'] ?? ''), $id
        ]);
    Flash::set('success', 'Cadastro atualizado com sucesso.');
    Csrf::regenerate();
} catch (PDOException $e) {
    AppLogger::error('Erro ao atualizar formulário', ['id' => $id, 'error' => $e->getMessage()]);
    Flash::set('error', 'Erro ao atualizar cadastro.');
}
header('Location: view_forms.php');
exit;
