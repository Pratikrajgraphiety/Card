<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Models\Profile;
use App\Services\QrCodeService;

final class ProfileController extends Controller
{
    public function show(string $username): void
    {
        $profile = Profile::findByUsername($username);
        if (!$profile) {
            http_response_code(404);
            $this->view('home/error', ['title' => 'Profile not found', 'message' => 'This AstitvaHub profile is not available.']);
            return;
        }

        Profile::trackView((int) $profile['id'], $this->request);
        $description = $profile['seo_description'] ?: mb_substr(strip_tags((string) ($profile['bio'] ?: $profile['headline'] ?: config('app.tagline'))), 0, 155);
        $this->view('profile/show', [
            'title' => $profile['seo_title'] ?: ($profile['display_name'] . ' | AstitvaHub'),
            'metaDescription' => $description,
            'metaKeywords' => $profile['seo_keywords'] ?: 'digital profile, contact card, QR profile, AstitvaHub',
            'ogTitle' => $profile['seo_title'] ?: ($profile['display_name'] . ' on AstitvaHub'),
            'ogDescription' => $description,
            'ogImage' => uploaded_asset($profile['og_image'] ?: $profile['profile_photo'] ?: $profile['cover_image']),
            'profile' => $profile,
            'lists' => Profile::lists((int) $profile['id']),
        ]);
    }

    public function scan(string $username): void
    {
        $profile = Profile::findByUsername($username);
        if ($profile) {
            Database::execute('UPDATE qr_codes SET scan_count = scan_count + 1, updated_at = NOW() WHERE profile_id = ?', [(int) $profile['id']]);
        }

        $this->redirect($username . '?src=qr');
    }

    public function vcard(string $username): void
    {
        $profile = Profile::findByUsername(str_replace('.vcf', '', $username));
        if (!$profile) {
            http_response_code(404);
            exit('Profile not found');
        }

        Profile::trackContactDownload((int) $profile['id'], $this->request);
        $nameParts = preg_split('/\s+/', trim((string) $profile['display_name']), 2);
        $first = $nameParts[0] ?? $profile['display_name'];
        $last = $nameParts[1] ?? '';

        $company = $profile['company_name'] ?: ($profile['meta']['business_name'] ?? '');
        $vcard = [
            'BEGIN:VCARD',
            'VERSION:3.0',
            'N:' . vcard_escape($last) . ';' . vcard_escape($first) . ';;;',
            'FN:' . vcard_escape($profile['display_name']),
            'ORG:' . vcard_escape($company),
            'TITLE:' . vcard_escape($profile['headline']),
            'TEL;TYPE=CELL:' . vcard_escape($profile['phone']),
            'EMAIL;TYPE=INTERNET:' . vcard_escape($profile['public_email'] ?: $profile['email']),
            'URL:' . vcard_escape($profile['website'] ?: url($profile['username'])),
            'ADR;TYPE=WORK:;;' . vcard_escape($profile['address']) . ';;;;',
            'NOTE:' . vcard_escape($profile['bio']),
            'END:VCARD',
        ];

        header('Content-Type: text/vcard; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . preg_replace('/[^A-Za-z0-9_-]/', '', $profile['username']) . '.vcf"');
        header('Cache-Control: no-store, no-cache, must-revalidate');
        echo implode("\r\n", $vcard) . "\r\n";
    }

    public function qr(string $username): void
    {
        $profile = Profile::findByUsername(str_replace('.svg', '', $username));
        if (!$profile) {
            http_response_code(404);
            exit('Profile not found');
        }

        header('Content-Type: image/svg+xml; charset=utf-8');
        echo QrCodeService::svg(url('scan/' . $profile['username'] . '?src=qr'));
    }

    public function qrDownload(string $username): void
    {
        $profile = Profile::findByUsername($username);
        if (!$profile) {
            http_response_code(404);
            exit('Profile not found');
        }

        Profile::trackEvent((int) $profile['id'], 'custom', 'qr_download', null, $this->request);
        header('Content-Type: image/svg+xml; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . preg_replace('/[^A-Za-z0-9_-]/', '', $profile['username']) . '-qr.svg"');
        echo QrCodeService::svg(url('scan/' . $profile['username'] . '?src=qr'));
    }

    public function go(string $username, string $type, string $id): void
    {
        $profile = Profile::findByUsername($username);
        if (!$profile) {
            http_response_code(404);
            exit('Profile not found');
        }

        if ($type === 'social') {
            $link = Database::fetch('SELECT * FROM social_links WHERE id = ? AND profile_id = ? AND is_active = 1 LIMIT 1', [(int) $id, (int) $profile['id']]);
            if (!$link || !filter_var($link['url'], FILTER_VALIDATE_URL)) {
                http_response_code(404);
                exit('Link not found');
            }
            Database::execute('UPDATE social_links SET click_count = click_count + 1 WHERE id = ?', [(int) $link['id']]);
            Profile::trackEvent((int) $profile['id'], 'social_click', $link['platform'], (int) $link['id'], $this->request);
            header('Location: ' . $link['url'], true, 302);
            exit;
        }

        http_response_code(404);
        exit('Unsupported link type');
    }

    public function whatsapp(string $username): void
    {
        $profile = Profile::findByUsername($username);
        if (!$profile) {
            http_response_code(404);
            exit('Profile not found');
        }

        $number = preg_replace('/\D+/', '', (string) ($profile['whatsapp'] ?: $profile['meta']['whatsapp'] ?? $profile['phone']));
        if (!$number) {
            $this->redirect($username);
        }

        Profile::trackEvent((int) $profile['id'], 'whatsapp_click', 'whatsapp', null, $this->request);
        header('Location: https://wa.me/' . $number, true, 302);
        exit;
    }
}
