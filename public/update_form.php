<?php
require_once __DIR__.'/../config/env.php';
require_once __DIR__.'/../config/session.php';

if (!isset($_SESSION['user_id']) || (int) ($_SESSION['is_admin'] ?? 0) !== 1) {
    header('Location: dashboard.php');
    exit;
}

require_once __DIR__.'/../config/database.php';
require_once __DIR__.'/../src/Csrf.php';
require_once __DIR__.'/../src/Logger.php';

AppLogger::setLogDir(__DIR__.'/../storage/logs');

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !Csrf::verify()) {
    header('Location: view_forms.php');
    exit;
}

$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
if (!$id) {
    header('Location: view_forms.php');
    exit;
}

$email = trim($_POST['email'] ?? '');
if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header('Location: edit_form.php?id='.$id.'&error=E-mail+invalido');
    exit;
}

$sql = 'UPDATE forms SET nome = :nome, telefone = :telefone, celular = :celular, email = :email, profissao = :profissao, numero_registro = :numero_registro, cidade = :cidade, estado = :estado WHERE id = :id';

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':nome' => trim($_POST['nome'] ?? ''),
        ':telefone' => trim($_POST['telefone'] ?? ''),
        ':celular' => trim($_POST['celular'] ?? ''),
        ':email' => $email,
        ':profissao' => trim($_POST['profissao'] ?? ''),
        ':numero_registro' => trim($_POST['numero_registro'] ?? ''),
        ':cidade' => trim($_POST['cidade'] ?? ''),
        ':estado' => trim($_POST['estado'] ?? ''),
        ':id' => $id,
    ]);

    require_once __DIR__.'/../src/Flash.php';
    Flash::set('success', 'Cadastro atualizado com sucesso.');
    Csrf::regenerate();
} catch (PDOException $e) {
    AppLogger::error('Erro ao atualizar formulário', ['id' => $id, 'error' => $e->getMessage()]);
    require_once __DIR__.'/../src/Flash.php';
    Flash::set('error', 'Erro ao atualizar cadastro.');
}

header('Location: view_forms.php');
exit;
