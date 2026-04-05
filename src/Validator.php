<?php

final class Validator
{
    private array $errors = [];
    private array $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function required(string $field, string $label): self
    {
        $value = trim($this->data[$field] ?? '');
        if ($value === '') {
            $this->errors[$field] = "{$label} é obrigatório.";
        }
        return $this;
    }

    public function email(string $field, string $label = 'E-mail'): self
    {
        $value = trim($this->data[$field] ?? '');
        if ($value !== '' && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $this->errors[$field] = "{$label} inválido.";
        }
        return $this;
    }

    public function maxLength(string $field, string $label, int $max): self
    {
        $value = trim($this->data[$field] ?? '');
        if (strlen($value) > $max) {
            $this->errors[$field] = "{$label} deve ter no máximo {$max} caracteres.";
        }
        return $this;
    }

    public function phone(string $field, string $label): self
    {
        $value = preg_replace('/\D/', '', $this->data[$field] ?? '');
        if ($value !== '' && !in_array(strlen($value), [10, 11], true)) {
            $this->errors[$field] = "{$label} deve ter 10 ou 11 dígitos.";
        }
        return $this;
    }

    public function inArray(string $field, string $label, array $allowed): self
    {
        $value = $this->data[$field] ?? '';
        if ($value !== '' && !in_array($value, $allowed, true)) {
            $this->errors[$field] = "{$label} selecionado é inválido.";
        }
        return $this;
    }

    public function username(string $field, string $label = 'Nome de usuário'): self
    {
        $value = trim($this->data[$field] ?? '');
        if ($value !== '' && !preg_match('/^[a-zA-Z0-9._-]{3,50}$/', $value)) {
            $this->errors[$field] = "{$label} deve conter 3-50 caracteres alfanuméricos, ponto, hífen ou underscore.";
        }
        return $this;
    }

    public function fails(): bool
    {
        return !empty($this->errors);
    }

    public function errors(): array
    {
        return $this->errors;
    }

    public function firstError(): string
    {
        return $this->errors[array_key_first($this->errors)] ?? 'Erro de validação.';
    }
}
