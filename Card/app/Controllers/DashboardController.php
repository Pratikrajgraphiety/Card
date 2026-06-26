<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Session;
use App\Core\Uploader;
use App\Core\Validator;
use App\Models\Category;
use App\Models\Profile;
use App\Services\CashfreePaymentService;

final class DashboardController extends Controller
{
    public function index(): void
    {
        $user = $this->requireAuth();
        $profile = Profile::findByUserId((int) $user['id']);
        $stats = $profile ? Profile::stats((int) $profile['id']) : ['totals' => [], 'series' => [], 'monthly' => []];

        $this->view('dashboard/index', [
            'title' => 'Dashboard',
            'user' => $user,
            'profile' => $profile,
            'stats' => $stats,
        ]);
    }

    public function editProfile(): void
    {
        $user = $this->requireAuth();
        $profile = Profile::findByUserId((int) $user['id']);
        $lists = $profile ? Profile::lists((int) $profile['id']) : [];

        $this->view('dashboard/profile', [
            'title' => 'Edit profile',
            'user' => $user,
            'profile' => $profile,
            'categories' => Category::all(),
            'fields' => $profile ? Category::fieldsFor(Category::find((int) $profile['category_id'])) : [],
            'lists' => $lists,
        ]);
    }

    public function updateProfile(): void
    {
        $user = $this->requireAuth();
        $profile = Profile::findByUserId((int) $user['id']);
        if (!$profile) {
            $this->redirect('dashboard');
        }

        $data = $this->request->all();
        $errors = Validator::validate($data, [
            'username' => ['required', 'username'],
            'display_name' => ['required', 'max:140'],
            'website' => ['url'],
            'custom_domain' => ['url'],
            'booking_link' => ['url'],
        ]);

        $category = Category::find((int) ($data['category_id'] ?? 0));
        if (!$category) {
            $errors['category_id'][] = 'Select a valid category.';
        }

        if (Profile::usernameExists((string) ($data['username'] ?? ''), (int) $profile['id'])) {
            $errors['username'][] = 'This username is already taken.';
        }

        if ($errors) {
            Session::backWithErrors($errors, $data);
        }

        try {
            $photo = Uploader::store('profile_photo', 'profiles', config('app.allowed_image_mimes'));
            $cover = Uploader::store('cover_image', 'covers', config('app.allowed_image_mimes'));
            $ogImage = Uploader::store('og_image', 'profiles', config('app.allowed_image_mimes'));
            $meta = $this->metaFromCategory($category, $data, $profile['meta'], (int) $profile['id']);

            Profile::updateMain((int) $profile['id'], [
                'username' => $data['username'],
                'category_id' => (int) $category['id'],
                'display_name' => $data['display_name'] ?? $profile['display_name'],
                'headline' => $data['headline'] ?? null,
                'phone' => $data['phone'] ?? null,
                'public_email' => $data['public_email'] ?? $user['email'],
                'website' => $data['website'] ?? null,
                'company_name' => $data['company_name'] ?? ($meta['business_name'] ?? null),
                'address' => $data['address'] ?? ($meta['office_address'] ?? null),
                'whatsapp' => $data['whatsapp'] ?? ($meta['whatsapp'] ?? null),
                'booking_link' => $data['booking_link'] ?? ($meta['booking_link'] ?? null),
                'google_maps_embed' => $data['google_maps_embed'] ?? ($meta['google_maps_embed'] ?? null),
                'resume_path' => $meta['resume_path'] ?? null,
                'business_pdf_path' => $meta['business_pdf_path'] ?? null,
                'profile_photo' => $photo,
                'cover_image' => $cover,
                'bio' => $data['bio'] ?? null,
                'theme_slug' => $data['theme_slug'] ?? 'aurora',
                'theme_color' => $data['theme_color'] ?? '#7c3aed',
                'dark_mode' => isset($data['dark_mode']),
                'custom_domain' => $data['custom_domain'] ?? null,
                'seo_title' => $data['seo_title'] ?? null,
                'seo_description' => $data['seo_description'] ?? null,
                'seo_keywords' => $data['seo_keywords'] ?? null,
                'og_image' => $ogImage,
                'meta' => $meta,
            ]);

            Profile::syncSocialLinks((int) $profile['id'], $data['social'] ?? []);
            Profile::syncNameLevel((int) $profile['id'], $data['skills'] ?? []);
            Profile::syncRows((int) $profile['id'], 'education', ['institution', 'university', 'degree', 'course', 'start_year', 'end_year', 'grade', 'description'], $data['education'] ?? []);
            Profile::syncRows((int) $profile['id'], 'projects', ['title', 'role', 'description', 'url'], $data['projects'] ?? []);
            Profile::syncRows((int) $profile['id'], 'certificates', ['title', 'issuer', 'credential_id', 'credential_url'], $data['certificates'] ?? []);
            Profile::syncRows((int) $profile['id'], 'services', ['title', 'description', 'price_label', 'duration_label', 'cta_label', 'cta_url'], $data['services'] ?? []);
            Profile::syncRows((int) $profile['id'], 'products', ['name', 'description', 'price', 'currency', 'product_url'], $data['products'] ?? []);
            Profile::syncRows((int) $profile['id'], 'portfolios', ['title', 'client_name', 'description', 'url', 'tags'], $data['portfolios'] ?? []);
            Profile::syncRows((int) $profile['id'], 'videos', ['title', 'url', 'embed_url', 'platform'], $data['videos'] ?? []);
            Profile::syncRows((int) $profile['id'], 'testimonials', ['author_name', 'author_title', 'company', 'quote', 'rating'], $data['testimonials'] ?? []);

            $galleryImage = Uploader::store('gallery_image', 'gallery', config('app.allowed_image_mimes'));
            if ($galleryImage) {
                Database::execute(
                    'INSERT INTO gallery_images (profile_id, image_path, caption, alt_text, sort_order, created_at, updated_at)
                     VALUES (?, ?, ?, ?, 100, NOW(), NOW())',
                    [(int) $profile['id'], $galleryImage, trim((string) ($data['gallery_caption'] ?? '')) ?: null, trim((string) ($data['gallery_alt'] ?? '')) ?: null]
                );
            }
        } catch (\Throwable $exception) {
            Session::backWithErrors(['upload' => [$exception->getMessage()]], $data);
        }

        flash('success', 'Profile updated. Your public page is looking sharper.');
        $this->redirect('dashboard/profile');
    }

    public function analytics(): void
    {
        $user = $this->requireAuth();
        $profile = Profile::findByUserId((int) $user['id']);
        $this->view('dashboard/analytics', [
            'title' => 'Analytics',
            'profile' => $profile,
            'stats' => $profile ? Profile::stats((int) $profile['id']) : ['totals' => [], 'series' => [], 'monthly' => []],
            'events' => $profile ? Profile::recentEvents((int) $profile['id']) : [],
            'visitors' => $profile ? Profile::recentVisitors((int) $profile['id']) : [],
        ]);
    }

    public function qr(): void
    {
        $user = $this->requireAuth();
        $profile = Profile::findByUserId((int) $user['id']);
        $this->view('dashboard/qr', ['title' => 'QR Code', 'profile' => $profile]);
    }

    public function upgrade(): void
    {
        $user = $this->requireAuth();
        $profile = Profile::findByUserId((int) $user['id']);
        $plans = Database::fetchAll('SELECT * FROM plans WHERE is_active = 1 ORDER BY sort_order, price');
        $payment = new CashfreePaymentService();
        $this->view('dashboard/upgrade', [
            'title' => 'Upgrade Plan',
            'user' => $user,
            'profile' => $profile,
            'plans' => $plans,
            'subscription' => $this->activeSubscription((int) $user['id']),
            'payments' => $this->recentPayments((int) $user['id']),
            'paymentReady' => $payment->isConfigured(),
            'paymentMode' => $payment->mode(),
        ]);
    }

    public function choosePlan(): void
    {
        $user = $this->requireAuth();
        $plan = Database::fetch('SELECT * FROM plans WHERE slug = ? AND is_active = 1 LIMIT 1', [(string) $this->request->input('plan')]);
        if (!$plan) {
            Session::backWithErrors(['plan' => ['Select a valid plan.']], []);
        }

        $active = $this->activeSubscription((int) $user['id']);
        if ($active && (int) $active['plan_id'] === (int) $plan['id']) {
            flash('info', 'This plan is already active on your account.');
            $this->redirect('dashboard/upgrade');
        }

        if ((float) $plan['price'] <= 0) {
            $this->activatePlan((int) $user['id'], (int) $plan['id'], (int) ($plan['is_lifetime'] ?? 1));
            flash('success', 'Free plan activated.');
            $this->redirect('dashboard/upgrade');
        }

        $payment = new CashfreePaymentService();
        if (!$payment->isConfigured()) {
            Session::backWithErrors(['payment' => ['Cashfree payment keys are not configured.']], []);
        }

        $orderId = $this->newOrderId((int) $user['id']);
        Database::execute(
            'INSERT INTO payments (user_id, plan_id, plan_name, amount, currency, status, gateway, transaction_id, payer_email, payload_json, created_at, updated_at)
             VALUES (?, ?, ?, ?, ?, "pending", "cashfree", ?, ?, ?, NOW(), NOW())',
            [
                (int) $user['id'],
                (int) $plan['id'],
                (string) $plan['name'],
                (float) $plan['price'],
                strtoupper((string) $plan['currency']),
                $orderId,
                (string) $user['email'],
                json_encode(['source' => 'dashboard_upgrade'], JSON_THROW_ON_ERROR),
            ]
        );

        try {
            $cashfreeOrder = $payment->createOrder($user, $plan, $orderId);
        } catch (\Throwable $exception) {
            Database::execute(
                'UPDATE payments SET status = "failed", payload_json = ?, updated_at = NOW() WHERE transaction_id = ?',
                [json_encode(['error' => $exception->getMessage()], JSON_THROW_ON_ERROR), $orderId]
            );
            Session::backWithErrors(['payment' => ['Could not start Cashfree checkout: ' . $exception->getMessage()]], []);
        }

        Database::execute(
            'UPDATE payments SET payload_json = ?, updated_at = NOW() WHERE transaction_id = ?',
            [json_encode(['create_order' => $cashfreeOrder], JSON_THROW_ON_ERROR), $orderId]
        );

        $sessionId = (string) ($cashfreeOrder['payment_session_id'] ?? '');
        if ($sessionId === '') {
            Session::backWithErrors(['payment' => ['Cashfree did not return a checkout session.']], []);
        }

        $this->view('dashboard/payment_checkout', [
            'title' => 'Cashfree Checkout',
            'plan' => $plan,
            'orderId' => $orderId,
            'paymentSessionId' => $sessionId,
            'paymentMode' => $payment->mode(),
            'cashfreeSdkUrl' => $payment->sdkUrl(),
        ]);
    }

    private function activeSubscription(int $userId): ?array
    {
        return Database::fetch(
            'SELECT s.*, p.name AS plan_name, p.slug AS plan_slug, p.price, p.currency
             FROM subscriptions s
             JOIN plans p ON p.id = s.plan_id
             WHERE s.user_id = ? AND s.status = "active"
             ORDER BY s.id DESC
             LIMIT 1',
            [$userId]
        );
    }

    private function recentPayments(int $userId): array
    {
        return Database::fetchAll(
            'SELECT * FROM payments WHERE user_id = ? ORDER BY id DESC LIMIT 8',
            [$userId]
        );
    }

    private function activatePlan(int $userId, int $planId, int $isLifetime = 1): void
    {
        $pdo = Database::connection();
        $pdo->beginTransaction();
        try {
            Database::execute(
                'UPDATE subscriptions SET status = "cancelled", updated_at = NOW() WHERE user_id = ? AND status = "active"',
                [$userId]
            );
            Database::execute(
                'INSERT INTO subscriptions (user_id, plan_id, status, starts_at, is_lifetime, created_at, updated_at)
                 VALUES (?, ?, "active", NOW(), ?, NOW(), NOW())',
                [$userId, $planId, $isLifetime]
            );
            $pdo->commit();
        } catch (\Throwable $exception) {
            $pdo->rollBack();
            throw $exception;
        }
    }

    private function newOrderId(int $userId): string
    {
        return 'AH_' . $userId . '_' . date('YmdHis') . '_' . strtoupper(bin2hex(random_bytes(3)));
    }

    private function metaFromCategory(array $category, array $data, array $current, int $profileId): array
    {
        $meta = $current;
        foreach (Category::fieldsFor($category) as $field) {
            $name = $field['name'];
            if (($field['type'] ?? '') === 'file') {
                $directory = $name === 'business_pdf_path' ? 'documents' : 'resumes';
                $mimes = $name === 'business_pdf_path' ? ['application/pdf'] : config('app.allowed_document_mimes');
                $stored = Uploader::store($name, $directory, $mimes, 10);
                if ($stored) {
                    $meta[$name] = $stored;
                    Profile::addDocument(
                        $profileId,
                        $name === 'business_pdf_path' ? 'business_pdf' : 'resume',
                        $name === 'business_pdf_path' ? 'Business PDF / Catalog' : 'Resume',
                        $stored,
                        $_FILES[$name] ?? null
                    );
                }
                continue;
            }

            $meta[$name] = trim((string) ($data[$name] ?? ''));
        }

        return $meta;
    }
}
