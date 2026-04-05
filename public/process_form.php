<?php
require_once __DIR__.'/bootstrap.php';
app_bootstrap(['database', 'csrf', 'validator', 'validation_profiles', 'logger']);

require_login();
AppLogger::setLogDir(__DIR__.'/../storage/logs');

app_require_post_csrf('form.php', 'Falha de validação CSRF no envio de formulário');

$data = $_POST;
$valid = new Validator($data);
ValidationProfiles::validateFormSubmission($valid);

if ($valid->fails()) {
    $_SESSION['__form_errors'] = $valid->errors();
    $_SESSION['__form_old'] = $data;
    header('Location: form.php');
    exit;
}

$normalized = InputNormalizer::formPayload($data);
$representante = InputNormalizer::representativeName($_SESSION);

try {
    $pdo->prepare('INSERT INTO forms (nome, telefone, celular, email, profissao, numero_registro, conselho, evento, cidade, estado, data_hora, representante, created_by_user_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)')
        ->execute([
            $normalized['nome'], $normalized['telefone'], $normalized['celular'],
            $normalized['email'], $normalized['profissao'], $normalized['numero_registro'],
            $normalized['conselho'], $normalized['evento'], $normalized['cidade'],
            $normalized['estado'], date('Y-m-d H:i:s'), $representante, user_id()
        ]);
    Csrf::regenerate();
    header('Location: form.php?success=true');
    exit;
} catch (PDOException $e) {
    AppLogger::error('Erro ao inserir formulário', ['error' => $e->getMessage()]);
    $_SESSION['__form_errors'] = ['Erro interno ao salvar o formulário.'];
    $_SESSION['__form_old'] = $data;
    header('Location: form.php');
    exit;
}
