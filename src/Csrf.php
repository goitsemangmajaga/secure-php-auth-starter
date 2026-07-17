<?php
declare(strict_types=1);

final class Csrf
{
    public function token(): string
    {
        if (empty($_SESSION['_csrf'])) {
            $_SESSION['_csrf'] = bin2hex(random_bytes(32));
        }

        return $_SESSION['_csrf'];
    }

    public function field(): string
    {
        return '<input type="hidden" name="csrf_token" value="' . e($this->token()) . '">';
    }

    public function validate(?string $token): bool
    {
        return is_string($token)
            && isset($_SESSION['_csrf'])
            && hash_equals($_SESSION['_csrf'], $token);
    }
}

