<?php
namespace App\Core;

class Csrf
{
    public static function token(): string
    {
        if (empty($_SESSION['_csrf'])) {
            $_SESSION['_csrf'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['_csrf'];
    }

    public static function verify(?string $token): bool
    {
        if (empty($_SESSION['_csrf']) || $token === null) return false;
        return hash_equals($_SESSION['_csrf'], $token);
    }
}
