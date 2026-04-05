<?php

final class InputNormalizer
{
    public static function userPayload(array $data): array
    {
        return [
            'username' => trim((string) ($data['username'] ?? '')),
            'password' => trim((string) ($data['password'] ?? '')),
            'nome' => trim((string) ($data['nome'] ?? '')),
            'sobrenome' => trim((string) ($data['sobrenome'] ?? '')),
            'is_admin' => in_array((string) ($data['is_admin'] ?? '0'), ['0', '1'], true) ? (int) $data['is_admin'] : 0,
        ];
    }

    public static function formPayload(array $data): array
    {
        return [
            'nome' => trim((string) ($data['nome'] ?? '')),
            'telefone' => trim((string) ($data['telefone'] ?? '')),
            'celular' => trim((string) ($data['celular'] ?? '')),
            'email' => trim((string) ($data['email'] ?? '')),
            'profissao' => trim((string) ($data['profissao'] ?? '')),
            'numero_registro' => trim((string) ($data['numero_registro'] ?? '')),
            'conselho' => trim((string) ($data['conselho'] ?? '')),
            'evento' => trim((string) ($data['evento'] ?? '')),
            'cidade' => trim((string) ($data['cidade'] ?? '')),
            'estado' => trim((string) ($data['estado'] ?? '')),
        ];
    }

    public static function representativeName(array $session): string
    {
        return trim((string) ($session['nome'] ?? '')).' '.trim((string) ($session['sobrenome'] ?? ''));
    }
}