<?php

namespace App\Models;

use App\Core\Database;
use App\Core\Request;

final class Profile
{
    public static function createDefault(int $userId, int $categoryId, string $username, string $displayName, string $publicEmail, array $meta = []): int
    {
        Database::execute(
            'INSERT INTO profiles (user_id, category_id, username, display_name, public_email, theme_slug, dark_mode, meta_json, is_published, created_at, updated_at)
             VALUES (?, ?, ?, ?, ?, ?, 1, ?, 1, NOW(), NOW())',
            [
                $userId,
                $categoryId,
                strtolower($username),
                $displayName,
                strtolower($publicEmail),
                'aurora',
                json_encode($meta, JSON_THROW_ON_ERROR),
            ]
        );

        $profileId = (int) Database::lastInsertId();
        self::syncProfileFields($profileId, $categoryId, $meta);
        return $profileId;
    }

    public static function usernameExists(string $username, ?int $exceptProfileId = null): bool
    {
        $sql = 'SELECT id FROM profiles WHERE username = ?';
        $bindings = [strtolower(trim($username))];
        if ($exceptProfileId) {
            $sql .= ' AND id <> ?';
            $bindings[] = $exceptProfileId;
        }

        return (bool) Database::fetch($sql . ' LIMIT 1', $bindings);
    }

    public static function findByUserId(int $userId): ?array
    {
        return self::hydrate(Database::fetch(
            'SELECT p.*, u.name, u.email, u.role, c.name AS category_name, c.slug AS category_slug, pl.name AS plan_name, pl.slug AS plan_slug
             FROM profiles p
             JOIN users u ON u.id = p.user_id
             JOIN categories c ON c.id = p.category_id
             LEFT JOIN subscriptions s ON s.user_id = u.id AND s.status = "active"
             LEFT JOIN plans pl ON pl.id = s.plan_id
             WHERE p.user_id = ?
             LIMIT 1',
            [$userId]
        ));
    }

    public static function findByUsername(string $username): ?array
    {
        return self::hydrate(Database::fetch(
            'SELECT p.*, u.name, u.email, u.role, c.name AS category_name, c.slug AS category_slug, pl.name AS plan_name, pl.slug AS plan_slug
             FROM profiles p
             JOIN users u ON u.id = p.user_id
             JOIN categories c ON c.id = p.category_id
             LEFT JOIN subscriptions s ON s.user_id = u.id AND s.status = "active"
             LEFT JOIN plans pl ON pl.id = s.plan_id
             WHERE p.username = ? AND p.is_published = 1
             LIMIT 1',
            [strtolower(trim($username))]
        ));
    }

    public static function updateMain(int $id, array $data): void
    {
        Database::execute(
            'UPDATE profiles
             SET username = ?, category_id = ?, display_name = ?, headline = ?, phone = ?, public_email = ?, website = ?,
                 company_name = ?, address = ?, whatsapp = ?, booking_link = ?, google_maps_embed = ?,
                 resume_path = COALESCE(?, resume_path), business_pdf_path = COALESCE(?, business_pdf_path),
                 profile_photo = COALESCE(?, profile_photo), cover_image = COALESCE(?, cover_image),
                 bio = ?, theme_slug = ?, theme_color = ?, dark_mode = ?, custom_domain = ?,
                 seo_title = ?, seo_description = ?, seo_keywords = ?, og_image = COALESCE(?, og_image),
                 meta_json = ?, updated_at = NOW()
             WHERE id = ?',
            [
                strtolower(trim((string) $data['username'])),
                (int) $data['category_id'],
                trim((string) $data['display_name']),
                $data['headline'] ?: null,
                $data['phone'] ?: null,
                strtolower((string) ($data['public_email'] ?: '')),
                $data['website'] ?: null,
                $data['company_name'] ?: null,
                $data['address'] ?: null,
                $data['whatsapp'] ?: null,
                $data['booking_link'] ?: null,
                $data['google_maps_embed'] ?: null,
                $data['resume_path'] ?? null,
                $data['business_pdf_path'] ?? null,
                $data['profile_photo'] ?? null,
                $data['cover_image'] ?? null,
                $data['bio'] ?: null,
                $data['theme_slug'] ?: 'aurora',
                $data['theme_color'] ?: '#7c3aed',
                !empty($data['dark_mode']) ? 1 : 0,
                $data['custom_domain'] ?: null,
                $data['seo_title'] ?: null,
                $data['seo_description'] ?: null,
                $data['seo_keywords'] ?: null,
                $data['og_image'] ?? null,
                json_encode($data['meta'] ?? [], JSON_THROW_ON_ERROR),
                $id,
            ]
        );

        self::syncProfileFields($id, (int) $data['category_id'], $data['meta'] ?? []);
    }

    public static function lists(int $profileId): array
    {
        return [
            'social_links' => Database::fetchAll('SELECT * FROM social_links WHERE profile_id = ? AND is_active = 1 ORDER BY sort_order, id', [$profileId]),
            'skills' => Database::fetchAll('SELECT * FROM skills WHERE profile_id = ? ORDER BY sort_order, id', [$profileId]),
            'education' => Database::fetchAll('SELECT * FROM education WHERE profile_id = ? ORDER BY sort_order, id', [$profileId]),
            'projects' => Database::fetchAll('SELECT * FROM projects WHERE profile_id = ? ORDER BY sort_order, id', [$profileId]),
            'certificates' => Database::fetchAll('SELECT * FROM certificates WHERE profile_id = ? ORDER BY sort_order, id', [$profileId]),
            'services' => Database::fetchAll('SELECT * FROM services WHERE profile_id = ? ORDER BY sort_order, id', [$profileId]),
            'products' => Database::fetchAll('SELECT * FROM products WHERE profile_id = ? ORDER BY sort_order, id', [$profileId]),
            'portfolios' => Database::fetchAll('SELECT * FROM portfolios WHERE profile_id = ? ORDER BY sort_order, id', [$profileId]),
            'documents' => Database::fetchAll('SELECT * FROM profile_documents WHERE profile_id = ? AND is_public = 1 ORDER BY sort_order, id', [$profileId]),
            'videos' => Database::fetchAll('SELECT * FROM videos WHERE profile_id = ? ORDER BY sort_order, id', [$profileId]),
            'gallery' => Database::fetchAll('SELECT * FROM gallery_images WHERE profile_id = ? ORDER BY sort_order, id', [$profileId]),
            'testimonials' => Database::fetchAll('SELECT * FROM testimonials WHERE profile_id = ? ORDER BY sort_order, id', [$profileId]),
            'reviews' => Database::fetchAll('SELECT * FROM reviews WHERE profile_id = ? AND status = "approved" ORDER BY created_at DESC, id DESC', [$profileId]),
        ];
    }

    public static function syncProfileFields(int $profileId, int $categoryId, array $meta): void
    {
        foreach ($meta as $key => $value) {
            $field = Database::fetch(
                'SELECT id FROM category_fields WHERE category_id = ? AND field_key = ? LIMIT 1',
                [$categoryId, (string) $key]
            );

            Database::execute(
                'INSERT INTO profile_fields (profile_id, category_field_id, field_key, field_value, created_at, updated_at)
                 VALUES (?, ?, ?, ?, NOW(), NOW())
                 ON DUPLICATE KEY UPDATE category_field_id = VALUES(category_field_id), field_value = VALUES(field_value), updated_at = NOW()',
                [$profileId, $field['id'] ?? null, (string) $key, is_scalar($value) ? (string) $value : json_encode($value)]
            );
        }
    }

    public static function addDocument(int $profileId, string $type, string $title, string $path, ?array $file = null): void
    {
        Database::execute(
            'INSERT INTO profile_documents (profile_id, document_type, title, file_path, original_name, mime_type, file_size, is_public, sort_order, created_at, updated_at)
             VALUES (?, ?, ?, ?, ?, ?, ?, 1, 100, NOW(), NOW())',
            [
                $profileId,
                $type,
                $title,
                $path,
                $file['name'] ?? null,
                $file['type'] ?? 'application/pdf',
                $file['size'] ?? null,
            ]
        );
    }

    public static function syncSocialLinks(int $profileId, array $rows): void
    {
        Database::execute('DELETE FROM social_links WHERE profile_id = ?', [$profileId]);
        $sort = 1;
        foreach ($rows as $row) {
            $url = trim((string) ($row['url'] ?? ''));
            if ($url === '' || !filter_var($url, FILTER_VALIDATE_URL)) {
                continue;
            }
            Database::execute(
                'INSERT INTO social_links (profile_id, platform, label, url, icon_class, sort_order, is_active, created_at, updated_at)
                 VALUES (?, ?, ?, ?, ?, ?, 1, NOW(), NOW())',
                [
                    $profileId,
                    strtolower(trim((string) ($row['platform'] ?? 'link'))),
                    trim((string) ($row['label'] ?? $row['platform'] ?? 'Link')),
                    $url,
                    self::iconForPlatform((string) ($row['platform'] ?? 'link')),
                    $sort++,
                ]
            );
        }
    }

    public static function syncNameLevel(int $profileId, array $rows): void
    {
        Database::execute('DELETE FROM skills WHERE profile_id = ?', [$profileId]);
        $sort = 1;
        foreach ($rows as $row) {
            $name = trim((string) ($row['name'] ?? ''));
            if ($name === '') {
                continue;
            }
            Database::execute(
                'INSERT INTO skills (profile_id, name, level, category, sort_order, created_at, updated_at)
                 VALUES (?, ?, ?, ?, ?, NOW(), NOW())',
                [$profileId, $name, max(0, min(100, (int) ($row['level'] ?? 80))), trim((string) ($row['category'] ?? '')), $sort++]
            );
        }
    }

    public static function syncRows(int $profileId, string $table, array $columns, array $rows): void
    {
        $allowed = [
            'education' => ['institution', 'university', 'degree', 'course', 'start_year', 'end_year', 'grade', 'description'],
            'projects' => ['title', 'role', 'description', 'url'],
            'certificates' => ['title', 'issuer', 'credential_id', 'credential_url'],
            'services' => ['title', 'description', 'price_label', 'duration_label', 'cta_label', 'cta_url'],
            'products' => ['name', 'description', 'price', 'currency', 'product_url'],
            'portfolios' => ['title', 'client_name', 'description', 'url', 'tags'],
            'videos' => ['title', 'url', 'embed_url', 'platform'],
            'testimonials' => ['author_name', 'author_title', 'company', 'quote', 'rating'],
        ];

        if (!isset($allowed[$table]) || array_diff($columns, $allowed[$table])) {
            throw new \InvalidArgumentException('Unsupported table sync.');
        }

        Database::execute("DELETE FROM {$table} WHERE profile_id = ?", [$profileId]);
        $sort = 1;
        foreach ($rows as $row) {
            $required = match ($table) {
                'products' => 'name',
                'education' => 'institution',
                'videos' => 'url',
                'testimonials' => 'quote',
                default => 'title',
            };

            if (trim((string) ($row[$required] ?? '')) === '') {
                continue;
            }

            $values = [];
            foreach ($columns as $column) {
                $value = trim((string) ($row[$column] ?? '')) ?: null;
                if ($table === 'products' && $column === 'currency' && !$value) {
                    $value = 'INR';
                }
                $values[] = $value;
            }

            if (count(array_filter($values, static fn ($value) => $value !== null && $value !== '')) === 0) {
                continue;
            }

            $columnSql = implode(', ', array_merge(['profile_id'], $columns, ['sort_order']));
            $placeholders = implode(', ', array_fill(0, count($columns) + 2, '?'));
            Database::execute(
                "INSERT INTO {$table} ({$columnSql}, created_at, updated_at) VALUES ({$placeholders}, NOW(), NOW())",
                array_merge([$profileId], $values, [$sort++])
            );
        }
    }

    public static function trackEvent(int $profileId, string $eventType, ?string $eventLabel = null, ?int $targetId = null, ?Request $request = null, ?string $source = null): void
    {
        $request ??= new Request();
        $agent = $request->userAgent();
        $ip = $request->ip();
        $geo = self::geo($ip);

        Database::execute(
            'INSERT INTO analytics
                (profile_id, event_type, event_label, source, target_id, ip_address, ip_hash, visitor_hash, session_key,
                 user_agent, device_type, browser, os, referrer, country, region, city, latitude, longitude, occurred_at)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())',
            [
                $profileId,
                self::eventType($eventType),
                $eventLabel,
                $source ?: self::sourceFromRequest($request),
                $targetId,
                substr($ip, 0, 45),
                self::hashVisitor($ip),
                self::visitorHash($request),
                session_id() ?: null,
                $agent,
                self::deviceType($agent),
                self::browser($agent),
                self::os($agent),
                $request->referer(),
                $geo['country'] ?? null,
                $geo['region'] ?? null,
                $geo['city'] ?? null,
                $geo['latitude'] ?? null,
                $geo['longitude'] ?? null,
            ]
        );
    }

    public static function trackView(int $profileId, Request $request): void
    {
        $agent = $request->userAgent();
        $ip = $request->ip();
        $visitorHash = self::visitorHash($request);
        $source = self::sourceFromRequest($request);
        $geo = self::geo($ip);
        $already = Database::fetch(
            'SELECT id FROM profile_views WHERE profile_id = ? AND visitor_hash = ? AND view_date = CURDATE() LIMIT 1',
            [$profileId, $visitorHash]
        );

        Database::execute(
            'INSERT INTO profile_views
                (profile_id, source, view_date, ip_address, ip_hash, visitor_hash, session_key, user_agent,
                 device_type, browser, os, referrer, country, region, city, is_unique, viewed_at)
             VALUES (?, ?, CURDATE(), ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())',
            [
                $profileId,
                $source,
                substr($ip, 0, 45),
                self::hashVisitor($ip),
                $visitorHash,
                session_id() ?: null,
                $agent,
                self::deviceType($agent),
                self::browser($agent),
                self::os($agent),
                $request->referer(),
                $geo['country'] ?? null,
                $geo['region'] ?? null,
                $geo['city'] ?? null,
                $already ? 0 : 1,
            ]
        );

        self::trackEvent($profileId, $source === 'qr' ? 'qr_scan' : 'profile_view', null, null, $request, $source);
    }

    public static function trackContactDownload(int $profileId, Request $request): void
    {
        $agent = $request->userAgent();
        $ip = $request->ip();
        $geo = self::geo($ip);
        Database::execute(
            'INSERT INTO contact_downloads
                (profile_id, source, ip_address, ip_hash, visitor_hash, session_key, user_agent, device_type, browser, os, referrer, country, city, downloaded_at)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())',
            [
                $profileId,
                self::sourceFromRequest($request),
                substr($ip, 0, 45),
                self::hashVisitor($ip),
                self::visitorHash($request),
                session_id() ?: null,
                $agent,
                self::deviceType($agent),
                self::browser($agent),
                self::os($agent),
                $request->referer(),
                $geo['country'] ?? null,
                $geo['city'] ?? null,
            ]
        );
        self::trackEvent($profileId, 'contact_download', null, null, $request);
    }

    public static function stats(int $profileId): array
    {
        $totals = Database::fetch(
            "SELECT
                (SELECT COUNT(*) FROM profile_views WHERE profile_id = ?) AS profile_views,
                (SELECT COUNT(DISTINCT visitor_hash) FROM profile_views WHERE profile_id = ?) AS unique_visitors,
                (SELECT COUNT(*) FROM analytics WHERE profile_id = ? AND event_type = 'qr_scan') AS qr_scans,
                (SELECT COUNT(*) FROM contact_downloads WHERE profile_id = ?) AS contact_downloads,
                (SELECT COUNT(*) FROM analytics WHERE profile_id = ? AND event_type = 'whatsapp_click') AS whatsapp_clicks,
                (SELECT COUNT(*) FROM analytics WHERE profile_id = ? AND event_type = 'social_click') AS social_clicks",
            [$profileId, $profileId, $profileId, $profileId, $profileId, $profileId]
        );

        $series = Database::fetchAll(
            "SELECT DATE(occurred_at) AS date, event_type, COUNT(*) AS total
             FROM analytics
             WHERE profile_id = ? AND occurred_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
             GROUP BY DATE(occurred_at), event_type
             ORDER BY date ASC",
            [$profileId]
        );

        $monthly = Database::fetchAll(
            "SELECT DATE_FORMAT(occurred_at, '%Y-%m') AS month, event_type, COUNT(*) AS total
             FROM analytics
             WHERE profile_id = ? AND occurred_at >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
             GROUP BY DATE_FORMAT(occurred_at, '%Y-%m'), event_type
             ORDER BY month ASC",
            [$profileId]
        );

        return ['totals' => $totals ?: [], 'series' => $series, 'monthly' => $monthly];
    }

    public static function recentEvents(int $profileId, int $limit = 50): array
    {
        return Database::fetchAll(
            'SELECT event_type, event_label, source, city, country, device_type, browser, referrer, occurred_at
             FROM analytics
             WHERE profile_id = ?
             ORDER BY occurred_at DESC
             LIMIT ' . max(1, min(100, $limit)),
            [$profileId]
        );
    }

    public static function recentVisitors(int $profileId, int $limit = 50): array
    {
        return Database::fetchAll(
            'SELECT viewed_at, city, country, device_type, browser, referrer, source, is_unique
             FROM profile_views
             WHERE profile_id = ?
             ORDER BY viewed_at DESC
             LIMIT ' . max(1, min(100, $limit)),
            [$profileId]
        );
    }

    private static function hydrate(?array $profile): ?array
    {
        if (!$profile) {
            return null;
        }

        $profile['user_name'] = $profile['name'] ?? '';
        $profile['name'] = $profile['display_name'] ?: ($profile['user_name'] ?: 'SmartProfile User');
        $profile['photo'] = $profile['profile_photo'] ?? null;
        $profile['meta'] = json_decode((string) ($profile['meta_json'] ?? '{}'), true) ?: [];
        $fields = Database::fetchAll('SELECT field_key, field_value FROM profile_fields WHERE profile_id = ?', [(int) $profile['id']]);
        foreach ($fields as $field) {
            $profile['meta'][$field['field_key']] = $field['field_value'];
        }

        return $profile;
    }

    private static function eventType(string $eventType): string
    {
        $allowed = ['profile_view', 'unique_view', 'qr_scan', 'contact_download', 'whatsapp_click', 'social_click', 'share_click', 'link_click', 'login', 'signup', 'payment', 'custom'];
        return in_array($eventType, $allowed, true) ? $eventType : 'custom';
    }

    private static function sourceFromRequest(Request $request): string
    {
        $src = strtolower((string) $request->input('src', ''));
        if ($src === 'qr') {
            return 'qr';
        }

        $referer = (string) $request->referer();
        if ($referer === '') {
            return 'direct';
        }

        return str_contains($referer, parse_url(url('/'), PHP_URL_HOST) ?: '') ? 'direct' : 'referral';
    }

    private static function visitorHash(Request $request): string
    {
        return hash_hmac('sha256', $request->ip() . '|' . $request->userAgent(), (string) config('app.key'));
    }

    private static function hashVisitor(string $value): string
    {
        return hash_hmac('sha256', $value, (string) config('app.key'));
    }

    private static function iconForPlatform(string $platform): string
    {
        return match (strtolower(trim($platform))) {
            'instagram' => 'fa-brands fa-instagram',
            'youtube' => 'fa-brands fa-youtube',
            'linkedin' => 'fa-brands fa-linkedin-in',
            'github' => 'fa-brands fa-github',
            'twitter', 'x' => 'fa-brands fa-x-twitter',
            'facebook' => 'fa-brands fa-facebook-f',
            'whatsapp' => 'fa-brands fa-whatsapp',
            default => 'fa-solid fa-link',
        };
    }

    private static function deviceType(string $agent): string
    {
        $ua = strtolower($agent);
        if (str_contains($ua, 'tablet') || str_contains($ua, 'ipad')) {
            return 'Tablet';
        }
        if (str_contains($ua, 'mobi') || str_contains($ua, 'android') || str_contains($ua, 'iphone')) {
            return 'Mobile';
        }
        return 'Desktop';
    }

    private static function browser(string $agent): string
    {
        return match (true) {
            str_contains($agent, 'Edg/') => 'Edge',
            str_contains($agent, 'Chrome/') && !str_contains($agent, 'Chromium') => 'Chrome',
            str_contains($agent, 'Safari/') && !str_contains($agent, 'Chrome/') => 'Safari',
            str_contains($agent, 'Firefox/') => 'Firefox',
            default => 'Browser',
        };
    }

    private static function os(string $agent): string
    {
        $ua = strtolower($agent);
        return match (true) {
            str_contains($ua, 'windows') => 'Windows',
            str_contains($ua, 'mac os') || str_contains($ua, 'macintosh') => 'macOS',
            str_contains($ua, 'iphone') || str_contains($ua, 'ipad') => 'iOS',
            str_contains($ua, 'android') => 'Android',
            str_contains($ua, 'linux') => 'Linux',
            default => 'Unknown',
        };
    }

    private static function geo(string $ip): array
    {
        if ($ip === '127.0.0.1' || $ip === '::1' || str_starts_with($ip, '192.168.') || str_starts_with($ip, '10.')) {
            return [];
        }

        $context = stream_context_create(['http' => ['timeout' => 1.2]]);
        $json = @file_get_contents('https://ipapi.co/' . rawurlencode($ip) . '/json/', false, $context);
        if (!$json) {
            return [];
        }

        $data = json_decode($json, true);
        if (!is_array($data) || isset($data['error'])) {
            return [];
        }

        return [
            'country' => $data['country_name'] ?? null,
            'region' => $data['region'] ?? null,
            'city' => $data['city'] ?? null,
            'latitude' => $data['latitude'] ?? null,
            'longitude' => $data['longitude'] ?? null,
        ];
    }
}
