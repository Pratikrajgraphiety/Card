<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Models\Category;
use App\Models\Profile;

final class ApiController extends Controller
{
    public function categoryFields(string $slug): void
    {
        $category = is_numeric($slug) ? Category::find((int) $slug) : Category::findBySlug($slug);
        $this->json([
            'ok' => (bool) $category,
            'category' => $category,
            'fields' => Category::fieldsFor($category),
        ], $category ? 200 : 404);
    }

    public function track(): void
    {
        $profileId = (int) $this->request->input('profile_id');
        $event = (string) $this->request->input('event');
        $label = $this->request->input('label') ? (string) $this->request->input('label') : null;

        $allowed = ['share_click', 'social_click', 'whatsapp_click', 'contact_download', 'qr_print', 'qr_download'];
        if (!$profileId || !in_array($event, $allowed, true)) {
            $this->json(['ok' => false, 'message' => 'Invalid tracking event.'], 422);
        }

        Profile::trackEvent($profileId, $event, $label, null, $this->request);
        $this->json(['ok' => true]);
    }

    public function preferences(): void
    {
        $user = $this->requireAuth();
        $profile = Profile::findByUserId((int) $user['id']);
        if (!$profile) {
            $this->json(['ok' => false], 404);
        }

        $theme = (string) $this->request->input('theme_slug', $profile['theme_slug']);
        $dark = (int) $this->request->input('dark_mode', $profile['dark_mode']);
        if (!array_key_exists($theme, config('app.themes'))) {
            $theme = 'indigo';
        }

        Database::execute('UPDATE profiles SET theme_slug = ?, dark_mode = ?, updated_at = NOW() WHERE id = ?', [$theme, $dark, (int) $profile['id']]);
        $this->json(['ok' => true]);
    }

    public function socialLink(): void
    {
        $user = $this->requireAuth();
        $profile = Profile::findByUserId((int) $user['id']);
        $url = trim((string) $this->request->input('url'));
        if (!$profile || !filter_var($url, FILTER_VALIDATE_URL)) {
            $this->json(['ok' => false, 'message' => 'Enter a valid URL.'], 422);
        }

        Database::execute(
            'INSERT INTO social_links (profile_id, platform, label, url, icon_class, sort_order, is_active, created_at, updated_at)
             VALUES (?, ?, ?, ?, ?, 99, 1, NOW(), NOW())',
            [
                (int) $profile['id'],
                strtolower((string) $this->request->input('platform', 'link')),
                (string) $this->request->input('label', 'Link'),
                $url,
                strtolower((string) $this->request->input('icon', 'link')),
            ]
        );

        $this->json(['ok' => true, 'id' => Database::lastInsertId()]);
    }

    public function deleteSocialLink(string $id): void
    {
        $user = $this->requireAuth();
        $profile = Profile::findByUserId((int) $user['id']);
        if (!$profile) {
            $this->json(['ok' => false], 404);
        }

        Database::execute('DELETE FROM social_links WHERE id = ? AND profile_id = ?', [(int) $id, (int) $profile['id']]);
        $this->json(['ok' => true]);
    }
}
