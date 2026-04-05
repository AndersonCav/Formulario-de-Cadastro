<?php

final class Csrf
{
    private const TOKEN_NAME = '_csrf_token';

    public static function generate(): string
    {
        if (!isset($_SESSION[self::TOKEN_NAME])) {
            $_SESSION[self::TOKEN_NAME] = bin2hex(random_bytes(32));
        }
        return $_SESSION[self::TOKEN_NAME];
    }

    public static function field(): string
    {
        return '<input type="hidden" name="'.self::TOKEN_NAME.'" value="'.self::generate().'">';
    }

    public static function verify(): bool
    {
        $token = $_POST[self::TOKEN_NAME] ?? '';
        if (!isset($_SESSION[self::TOKEN_NAME]) || $token === '' || !hash_equals($_SESSION[self::TOKEN_NAME], $token)) {
            return false;
        }
        return true;
    }

    public static function regenerate(): void
    {
        $_SESSION[self::TOKEN_NAME] = bin2hex(random_bytes(32));
    }
}
