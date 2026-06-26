<?php

namespace App\Models;

use App\Core\Database;

final class AdminRepository
{
    public static function dashboardStats(): array
    {
        return Database::fetch(
            "SELECT
                (SELECT COUNT(*) FROM users WHERE deleted_at IS NULL) AS users,
                (SELECT COUNT(*) FROM users WHERE status = 'active' AND deleted_at IS NULL) AS active_users,
                (SELECT COUNT(*) FROM users WHERE status = 'banned' AND deleted_at IS NULL) AS banned_users,
                (SELECT COUNT(*) FROM profiles) AS profiles,
                (SELECT COUNT(*) FROM payments) AS payments,
                (SELECT COALESCE(SUM(amount), 0) FROM payments WHERE status = 'paid') AS revenue,
                (SELECT COUNT(*) FROM analytics WHERE event_type = 'profile_view') AS profile_views,
                (SELECT COUNT(*) FROM analytics WHERE event_type = 'qr_scan') AS qr_scans"
        ) ?: [];
    }

    public static function planBreakdown(): array
    {
        return Database::fetchAll(
            "SELECT COALESCE(pl.name, 'Free') AS label, COUNT(u.id) AS total
             FROM users u
             LEFT JOIN subscriptions s ON s.user_id = u.id AND s.status = 'active'
             LEFT JOIN plans pl ON pl.id = s.plan_id
             WHERE u.deleted_at IS NULL
             GROUP BY COALESCE(pl.name, 'Free')
             ORDER BY total DESC"
        );
    }

    public static function categoryBreakdown(): array
    {
        return Database::fetchAll(
            "SELECT c.name AS label, COUNT(p.id) AS total
             FROM categories c
             LEFT JOIN profiles p ON p.category_id = c.id
             GROUP BY c.id, c.name
             ORDER BY c.sort_order"
        );
    }

    public static function signupsSeries(): array
    {
        return Database::fetchAll(
            "SELECT DATE(created_at) AS date, COUNT(*) AS total
             FROM users
             WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
             GROUP BY DATE(created_at)
             ORDER BY date"
        );
    }

    public static function table(string $table, int $limit = 200): array
    {
        $allowed = ['categories', 'category_fields', 'plans', 'themes', 'payments', 'notifications', 'settings'];
        if (!in_array($table, $allowed, true)) {
            throw new \InvalidArgumentException('Unsupported admin table.');
        }

        return Database::fetchAll("SELECT * FROM {$table} ORDER BY id DESC LIMIT " . max(1, min(500, $limit)));
    }

    public static function analyticsReport(): array
    {
        return Database::fetchAll(
            "SELECT p.username, u.name, a.event_type, a.source, COUNT(*) AS total
             FROM analytics a
             JOIN profiles p ON p.id = a.profile_id
             JOIN users u ON u.id = p.user_id
             GROUP BY p.username, u.name, a.event_type, a.source
             ORDER BY total DESC
             LIMIT 100"
        );
    }
}
