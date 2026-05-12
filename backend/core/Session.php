<?php

/**
 * Session
 * Enkapsulasi semua operasi session PHP.
 * Prinsip OOP: Encapsulation — mencegah akses langsung ke superglobal $_SESSION.
 */
class Session
{
    public static function start(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public static function get(string $key): mixed
    {
        return $_SESSION[$key] ?? null;
    }

    public static function set(string $key, mixed $value): void
    {
        $_SESSION[$key] = $value;
    }

    public static function has(string $key): bool
    {
        return isset($_SESSION[$key]);
    }

    public static function remove(string $key): void
    {
        unset($_SESSION[$key]);
    }

    public static function flash(string $key): mixed
    {
        $value = self::get($key);
        self::remove($key);
        return $value;
    }

    public static function setFlash(string $type, string $message): void
    {
        self::set($type, $message);
    }

    public static function destroy(): void
    {
        $_SESSION = [];
        session_destroy();

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }
    }

    public static function regenerate(): void
    {
        session_regenerate_id(true);
    }

    public static function getUserId(): ?int
    {
        $id = self::get('user_id');
        return $id !== null ? (int) $id : null;
    }

    public static function getRole(): string
    {
        return self::get('role') ?? '';
    }

    public static function isLoggedIn(): bool
    {
        return self::getUserId() !== null;
    }
}
