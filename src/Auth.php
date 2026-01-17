<?php

declare(strict_types=1);

final class Auth
{
    public const ROLE_ADMIN = 'admin';
    public const ROLE_RESTAURANT = 'restaurant';

    public static function login(array $user): void
    {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['restaurant_id'] = $user['restaurant_id'] ?? null;
    }

    public static function logout(): void
    {
        $_SESSION = [];
        session_destroy();
    }

    public static function isLoggedIn(): bool
    {
        return isset($_SESSION['user_id']);
    }

    public static function role(): ?string
    {
        return $_SESSION['user_role'] ?? null;
    }

    public static function restaurantId(): ?int
    {
        $restaurantId = $_SESSION['restaurant_id'] ?? null;
        return $restaurantId !== null ? (int) $restaurantId : null;
    }

    public static function requireRole(string $role, string $redirect): void
    {
        if (!self::isLoggedIn() || self::role() !== $role) {
            header('Location: ' . $redirect);
            exit;
        }
    }

    public static function startImpersonation(int $restaurantId, int $adminId): void
    {
        $_SESSION['impersonator_id'] = $adminId;
        $_SESSION['user_role'] = self::ROLE_RESTAURANT;
        $_SESSION['restaurant_id'] = $restaurantId;
    }

    public static function stopImpersonation(): void
    {
        if (!isset($_SESSION['impersonator_id'])) {
            return;
        }

        $_SESSION['user_id'] = $_SESSION['impersonator_id'];
        $_SESSION['user_role'] = self::ROLE_ADMIN;
        $_SESSION['restaurant_id'] = null;
        unset($_SESSION['impersonator_id']);
    }

    public static function isImpersonating(): bool
    {
        return isset($_SESSION['impersonator_id']);
    }
}
