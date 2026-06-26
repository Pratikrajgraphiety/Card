<?php

namespace App\Models;

use App\Core\Database;

final class User
{
    public static function find(int $id): ?array
    {
        return Database::fetch('SELECT * FROM users WHERE id = ? AND deleted_at IS NULL LIMIT 1', [$id]);
    }

    public static function findByEmail(string $email): ?array
    {
        return Database::fetch('SELECT * FROM users WHERE email = ? AND deleted_at IS NULL LIMIT 1', [strtolower(trim($email))]);
    }

    public static function emailExists(string $email, ?int $exceptId = null): bool
    {
        $sql = 'SELECT id FROM users WHERE email = ? AND deleted_at IS NULL';
        $bindings = [strtolower(trim($email))];
        if ($exceptId) {
            $sql .= ' AND id <> ?';
            $bindings[] = $exceptId;
        }

        return (bool) Database::fetch($sql . ' LIMIT 1', $bindings);
    }

    public static function create(array $data): int
    {
        Database::execute(
            'INSERT INTO users (category_id, name, email, phone, password_hash, role, status, created_at, updated_at)
             VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), NOW())',
            [
                $data['category_id'] ?? null,
                trim((string) $data['name']),
                strtolower(trim((string) $data['email'])),
                $data['phone'] ?? null,
                password_hash((string) $data['password'], PASSWORD_DEFAULT),
                $data['role'] ?? 'user',
                $data['status'] ?? 'active',
            ]
        );

        return (int) Database::lastInsertId();
    }

    public static function updatePassword(int $id, string $password): void
    {
        Database::execute(
            'UPDATE users SET password_hash = ?, remember_token_version = remember_token_version + 1, updated_at = NOW() WHERE id = ?',
            [password_hash($password, PASSWORD_DEFAULT), $id]
        );
        Database::execute('UPDATE password_resets SET used_at = NOW() WHERE user_id = ? AND used_at IS NULL', [$id]);
        Database::execute('UPDATE remember_tokens SET revoked_at = NOW() WHERE user_id = ? AND revoked_at IS NULL', [$id]);
    }

    public static function createPasswordReset(int $id, string $email, string $plainToken, string $expiresAt, string $ip): void
    {
        Database::execute(
            'INSERT INTO password_resets (user_id, email, token_hash, expires_at, requested_ip, created_at)
             VALUES (?, ?, ?, ?, ?, NOW())',
            [$id, strtolower($email), hash('sha256', $plainToken), $expiresAt, $ip]
        );
    }

    public static function findByResetToken(string $plainToken): ?array
    {
        return Database::fetch(
            'SELECT u.*
             FROM password_resets pr
             JOIN users u ON u.id = pr.user_id
             WHERE pr.token_hash = ? AND pr.expires_at > NOW() AND pr.used_at IS NULL AND u.deleted_at IS NULL
             LIMIT 1',
            [hash('sha256', $plainToken)]
        );
    }

    public static function createEmailVerification(int $id, string $email, string $plainToken, string $expiresAt): void
    {
        Database::execute(
            'INSERT INTO email_verifications (user_id, email, token_hash, expires_at, created_at)
             VALUES (?, ?, ?, ?, NOW())',
            [$id, strtolower($email), hash('sha256', $plainToken), $expiresAt]
        );
    }

    public static function findByVerificationToken(string $plainToken): ?array
    {
        return Database::fetch(
            'SELECT u.*, ev.id AS verification_id
             FROM email_verifications ev
             JOIN users u ON u.id = ev.user_id
             WHERE ev.token_hash = ? AND ev.expires_at > NOW() AND ev.verified_at IS NULL AND u.deleted_at IS NULL
             LIMIT 1',
            [hash('sha256', $plainToken)]
        );
    }

    public static function markEmailVerified(int $userId, int $verificationId): void
    {
        Database::execute('UPDATE users SET email_verified_at = NOW(), status = "active", updated_at = NOW() WHERE id = ?', [$userId]);
        Database::execute('UPDATE email_verifications SET verified_at = NOW() WHERE id = ?', [$verificationId]);
    }

    public static function markLogin(int $id, string $ip): void
    {
        Database::execute('UPDATE users SET last_login_at = NOW(), last_login_ip = ?, updated_at = NOW() WHERE id = ?', [$ip, $id]);
    }

    public static function loginLock(string $email, string $ip): ?array
    {
        return Database::fetch(
            'SELECT * FROM login_attempts WHERE email = ? AND ip_address = ? AND locked_until IS NOT NULL AND locked_until > NOW() LIMIT 1',
            [strtolower(trim($email)), $ip]
        );
    }

    public static function recordLoginFailure(string $email, string $ip): void
    {
        Database::execute(
            'INSERT INTO login_attempts (email, ip_address, attempts, locked_until, last_attempt_at, created_at, updated_at)
             VALUES (?, ?, 1, NULL, NOW(), NOW(), NOW())
             ON DUPLICATE KEY UPDATE
                attempts = attempts + 1,
                locked_until = IF(attempts + 1 >= 8, DATE_ADD(NOW(), INTERVAL 15 MINUTE), locked_until),
                last_attempt_at = NOW(),
                updated_at = NOW()',
            [strtolower(trim($email)), $ip]
        );
    }

    public static function clearLoginFailures(string $email, string $ip): void
    {
        Database::execute('DELETE FROM login_attempts WHERE email = ? AND ip_address = ?', [strtolower(trim($email)), $ip]);
    }

    public static function createRememberToken(int $userId, string $selector, string $validatorHash, string $userAgentHash, string $ipHash, string $expiresAt): void
    {
        Database::execute(
            'INSERT INTO remember_tokens (user_id, selector, validator_hash, user_agent_hash, ip_hash, expires_at, last_used_at, created_at)
             VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())',
            [$userId, $selector, $validatorHash, $userAgentHash, $ipHash, $expiresAt]
        );
    }

    public static function findRememberToken(string $selector): ?array
    {
        return Database::fetch(
            'SELECT rt.*, u.name, u.email, u.role, u.status, u.password_hash
             FROM remember_tokens rt
             JOIN users u ON u.id = rt.user_id
             WHERE rt.selector = ? AND rt.expires_at > NOW() AND rt.revoked_at IS NULL AND u.deleted_at IS NULL
             LIMIT 1',
            [$selector]
        );
    }

    public static function touchRememberToken(int $id): void
    {
        Database::execute('UPDATE remember_tokens SET last_used_at = NOW() WHERE id = ?', [$id]);
    }

    public static function revokeRememberSelector(string $selector): void
    {
        Database::execute('UPDATE remember_tokens SET revoked_at = NOW() WHERE selector = ?', [$selector]);
    }

    public static function all(int $limit = 200): array
    {
        return Database::fetchAll(
            'SELECT u.*, p.username, c.name AS category_name, pl.name AS plan_name
             FROM users u
             LEFT JOIN profiles p ON p.user_id = u.id
             LEFT JOIN categories c ON c.id = p.category_id
             LEFT JOIN subscriptions s ON s.user_id = u.id AND s.status = "active"
             LEFT JOIN plans pl ON pl.id = s.plan_id
             WHERE u.deleted_at IS NULL
             ORDER BY u.created_at DESC
             LIMIT ' . max(1, min(500, $limit))
        );
    }
}
