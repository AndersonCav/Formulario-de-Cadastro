<?php
require_once __DIR__.'/bootstrap.php';
app_bootstrap(['database', 'csrf', 'validator', 'validation_profiles', 'flash', 'logger']);
require_admin();
AppLogger::setLogDir(__DIR__.'/../storage/logs');

app_require_post_csrf('view_forms.php', 'Falha de validação CSRF ao atualizar formulário');

$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
if (!$id) {
    app_redirect('view_forms.php');
}

$normalized = InputNormalizer::formPayload($_POST);
$email = $normalized['email'];
$valid = new Validator(['email' => $email]);
ValidationProfiles::validateFormUpdate($valid);
if ($valid->fails()) {
    app_flash_redirect('error', 'E-mail inválido.', 'edit_form.php?id='.$id);
}

try {
    $pdo->prepare('UPDATE forms SET nome=?,telefone=?,celular=?,email=?,profissao=?,numero_registro=?,conselho=?,evento=?,cidade=?,estado=? WHERE id=?')
        ->execute([
            $normalized['nome'], $normalized['telefone'], $normalized['celular'],
            $normalized['email'], $normalized['profissao'], $normalized['numero_registro'],
            $normalized['conselho'], $normalized['evento'], $normalized['cidade'],
            $normalized['estado'], $id
        ]);
    app_flash_redirect('success', 'Cadastro atualizado com sucesso.', 'view_forms.php', true);
} catch (PDOException $e) {
    AppLogger::error('Erro ao atualizar formulário', ['id' => $id, 'error' => $e->getMessage()]);
    app_flash_redirect('error', 'Erro ao atualizar cadastro.', 'view_forms.php');
}
