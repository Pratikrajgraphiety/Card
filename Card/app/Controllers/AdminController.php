<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Models\AdminRepository;
use App\Models\User;

final class AdminController extends Controller
{
    public function index(): void
    {
        $this->requireAdmin();
        $this->view('admin/index', [
            'title' => 'Admin Dashboard',
            'stats' => AdminRepository::dashboardStats(),
            'recentUsers' => User::all(10),
        ]);
    }

    public function users(): void
    {
        $this->requireAdmin();
        if ($this->request->method() === 'POST') {
            Database::execute('UPDATE users SET status = ?, role = ?, updated_at = NOW() WHERE id = ?', [
                (string) $this->request->input('status', 'active'),
                (string) $this->request->input('role', 'user'),
                (int) $this->request->input('id'),
            ]);
            flash('success', 'User updated.');
            $this->redirect('admin/users');
        }

        $this->view('admin/users', ['title' => 'Manage Users', 'users' => User::all()]);
    }

    public function categories(): void
    {
        $this->requireAdmin();
        if ($this->request->method() === 'POST') {
            Database::execute(
                'INSERT INTO categories (name, slug, description, fields_json, is_active, sort_order, created_at, updated_at)
                 VALUES (?, ?, ?, ?, 1, 100, NOW(), NOW())
                 ON DUPLICATE KEY UPDATE name = VALUES(name), description = VALUES(description), fields_json = VALUES(fields_json), updated_at = NOW()',
                [
                    (string) $this->request->input('name'),
                    strtolower((string) $this->request->input('slug')),
                    (string) $this->request->input('description'),
                    (string) $this->request->input('fields_json', '[]'),
                ]
            );
            flash('success', 'Category saved.');
            $this->redirect('admin/categories');
        }
        $this->view('admin/table', ['title' => 'Manage Categories', 'table' => 'categories', 'rows' => AdminRepository::table('categories')]);
    }

    public function plans(): void
    {
        $this->requireAdmin();
        if ($this->request->method() === 'POST') {
            Database::execute(
                'INSERT INTO plans (name, slug, price, currency, billing_type, features_json, is_active, created_at, updated_at)
                 VALUES (?, ?, ?, ?, ?, ?, 1, NOW(), NOW())
                 ON DUPLICATE KEY UPDATE name = VALUES(name), price = VALUES(price), currency = VALUES(currency), billing_type = VALUES(billing_type), features_json = VALUES(features_json), updated_at = NOW()',
                [
                    (string) $this->request->input('name'),
                    strtolower((string) $this->request->input('slug')),
                    (float) $this->request->input('price', 0),
                    strtoupper((string) $this->request->input('currency', 'INR')),
                    (string) $this->request->input('billing_type', 'lifetime'),
                    (string) $this->request->input('features_json', '[]'),
                ]
            );
            flash('success', 'Plan saved.');
            $this->redirect('admin/plans');
        }
        $this->view('admin/table', ['title' => 'Manage Plans', 'table' => 'plans', 'rows' => AdminRepository::table('plans')]);
    }

    public function themes(): void
    {
        $this->requireAdmin();
        if ($this->request->method() === 'POST') {
            Database::execute(
                'INSERT INTO themes (name, slug, accent_color, is_active, created_at, updated_at)
                 VALUES (?, ?, ?, 1, NOW(), NOW())
                 ON DUPLICATE KEY UPDATE name = VALUES(name), accent_color = VALUES(accent_color), updated_at = NOW()',
                [
                    (string) $this->request->input('name'),
                    strtolower((string) $this->request->input('slug')),
                    (string) $this->request->input('accent_color', '#4f46e5'),
                ]
            );
            flash('success', 'Theme saved.');
            $this->redirect('admin/themes');
        }
        $this->view('admin/table', ['title' => 'Manage Themes', 'table' => 'themes', 'rows' => AdminRepository::table('themes')]);
    }

    public function orders(): void
    {
        $this->requireAdmin();
        $this->view('admin/table', ['title' => 'Payment Orders', 'table' => 'payments', 'rows' => AdminRepository::table('payments')]);
    }

    public function payments(): void
    {
        $this->requireAdmin();
        $this->view('admin/table', ['title' => 'Payments', 'table' => 'payments', 'rows' => AdminRepository::table('payments')]);
    }

    public function notifications(): void
    {
        $this->requireAdmin();
        if ($this->request->method() === 'POST') {
            Database::execute(
                'INSERT INTO notifications (user_id, title, body, type, is_read, created_at, updated_at) VALUES (?, ?, ?, ?, 0, NOW(), NOW())',
                [
                    $this->request->input('user_id') ?: null,
                    (string) $this->request->input('title'),
                    (string) $this->request->input('body'),
                    (string) $this->request->input('type', 'info'),
                ]
            );
            flash('success', 'Notification queued.');
            $this->redirect('admin/notifications');
        }
        $this->view('admin/table', ['title' => 'Notifications', 'table' => 'notifications', 'rows' => AdminRepository::table('notifications')]);
    }

    public function reports(): void
    {
        $this->requireAdmin();
        $this->view('admin/reports', ['title' => 'Reports', 'reports' => AdminRepository::analyticsReport()]);
    }

    public function settings(): void
    {
        $this->requireAdmin();
        if ($this->request->method() === 'POST') {
            foreach (($this->request->input('settings', []) ?: []) as $key => $value) {
                Database::execute(
                    'INSERT INTO settings (setting_key, setting_value, setting_type, is_public, created_at, updated_at)
                     VALUES (?, ?, "text", 0, NOW(), NOW())
                     ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value), updated_at = NOW()',
                    [(string) $key, (string) $value]
                );
            }
            flash('success', 'Settings saved.');
            $this->redirect('admin/settings');
        }
        $this->view('admin/settings', ['title' => 'Settings', 'rows' => AdminRepository::table('settings')]);
    }
}
