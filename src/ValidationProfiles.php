<?php

final class ValidationProfiles
{
    private const BRAZIL_STATES = [
        'AC', 'AL', 'AP', 'AM', 'BA', 'CE', 'DF', 'ES', 'GO',
        'MA', 'MT', 'MS', 'MG', 'PA', 'PB', 'PR', 'PE', 'PI',
        'RJ', 'RN', 'RS', 'RO', 'RR', 'SC', 'SE', 'SP', 'TO',
    ];

    public static function validateUserIdentity(Validator $validator): void
    {
        $validator->username('username');
        $validator->required('nome', 'Nome');
        $validator->maxLength('nome', 'Nome', 100);
        $validator->required('sobrenome', 'Sobrenome');
        $validator->maxLength('sobrenome', 'Sobrenome', 100);
    }

    public static function validatePassword(string $password, bool $required): ?string
    {
        if ($required && $password === '') {
            return 'Senha é obrigatória.';
        }

        if ($password !== '' && strlen($password) < 6) {
            return 'A senha deve ter no mínimo 6 caracteres.';
        }

        return null;
    }

    public static function validateFormSubmission(Validator $validator): void
    {
        $validator->required('nome', 'Nome');
        $validator->maxLength('nome', 'Nome', 100);
        $validator->required('telefone', 'Telefone');
        $validator->required('celular', 'Celular');
        $validator->required('email', 'E-mail');
        $validator->email('email', 'E-mail');
        $validator->maxLength('email', 'E-mail', 100);
        $validator->required('profissao', 'Profissão');
        $validator->required('numero_registro', 'Nº de Registro');
        $validator->maxLength('numero_registro', 'Nº de Registro', 50);
        $validator->required('conselho', 'Conselho');
        $validator->maxLength('conselho', 'Conselho', 50);
        $validator->required('evento', 'Evento');
        $validator->maxLength('evento', 'Evento', 100);
        $validator->required('cidade', 'Cidade');
        $validator->maxLength('cidade', 'Cidade', 100);
        $validator->required('estado', 'Estado');
        $validator->inArray('estado', 'Estado', self::BRAZIL_STATES);
    }

    public static function validateFormUpdate(Validator $validator): void
    {
        // Preserve existing update behavior: only optional e-mail format validation.
        $validator->email('email', 'E-mail');
    }
}