<?php

namespace App\Core;

use App\Models\User;

final class Auth
{
    public static function attempt(string $email, string $password, bool $remember = false): bool
    {
        $email = strtolower(trim($email));
        $user = User::findByEmail($email);
        if (!$user || $user['status'] !== 'active' || !password_verify($password, $user['password_hash'])) {
            return false;
        }

        self::login($user);
        User::markLogin((int) $user['id'], (new Request())->ip());

        if ($remember) {
            self::remember((int) $user['id']);
        }

        return true;
    }

    public static function login(array $user): void
    {
        Session::regenerate();
        Session::put('user_id', (int) $user['id']);
    }

    public static function logout(): void
    {
        $cookie = $_COOKIE[config('app.remember_cookie')] ?? '';
        if (is_string($cookie) && str_contains($cookie, ':')) {
            [$selector] = explode(':', $cookie, 2);
            User::revokeRememberSelector($selector);
        }

        self::forgetRememberCookie();
        Session::destroy();
    }

    public static function id(): ?int
    {
        $id = Session::get('user_id');
        return $id ? (int) $id : null;
    }

    public static function check(): bool
    {
        return self::id() !== null;
    }

    public static function user(): ?array
    {
        $id = self::id();
        return $id ? User::find($id) : null;
    }

    public static function viaRememberCookie(): void
    {
        if (self::check()) {
            return;
        }

        $cookie = $_COOKIE[config('app.remember_cookie')] ?? '';
        if (!is_string($cookie) || !str_contains($cookie, ':')) {
            return;
        }

        [$selector, $validator] = explode(':', $cookie, 2);
        if (!preg_match('/^[a-f0-9]{24}$/', $selector) || !preg_match('/^[a-f0-9]{64}$/', $validator)) {
            self::forgetRememberCookie();
            return;
        }

        $token = User::findRememberToken($selector);
        if (!$token || ($token['status'] ?? '') !== 'active' || !hash_equals((string) $token['validator_hash'], hash('sha256', $validator))) {
            self::forgetRememberCookie();
            return;
        }

        User::touchRememberToken((int) $token['id']);
        self::login(['id' => (int) $token['user_id']]);
    }

    private static function remember(int $userId): void
    {
        $selector = bin2hex(random_bytes(12));
        $validator = bin2hex(random_bytes(32));
        $request = new Request();
        $expires = time() + (60 * 60 * 24 * 30);

        User::createRememberToken(
            $userId,
            $selector,
            hash('sha256', $validator),
            hash('sha256', $request->userAgent()),
            hash_hmac('sha256', $request->ip(), (string) config('app.key')),
            date('Y-m-d H:i:s', $expires)
        );

        setcookie(config('app.remember_cookie'), $selector . ':' . $validator, [
            'expires' => $expires,
            'path' => '/',
            'secure' => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
    }

    private static function forgetRememberCookie(): void
    {
        setcookie(config('app.remember_cookie'), '', [
            'expires' => time() - 3600,
            'path' => '/',
            'secure' => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
    }
}
