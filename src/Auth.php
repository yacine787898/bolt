<?php

declare(strict_types=1);

final class Auth
{
    public const ROLE_ADMIN = 'admin';

    public static function loginAdmin(): void
    {
        $_SESSION['user_role'] = self::ROLE_ADMIN;
    }

    public static function logout(): void
    {
        $_SESSION = [];
        session_destroy();
    }

    public static function isAdmin(): bool
    {
        return ($_SESSION['user_role'] ?? null) === self::ROLE_ADMIN;
    }

    public static function requireAdmin(string $redirect): void
    {
        if (!self::isAdmin()) {
            header('Location: ' . $redirect);
            exit;
        }
    }
}
