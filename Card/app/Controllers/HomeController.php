<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Category;
use App\Core\Database;

final class HomeController extends Controller
{
    public function index(): void
    {
        $categories = Category::all();
        $plans = Database::fetchAll('SELECT * FROM plans WHERE is_active = 1 ORDER BY price ASC, id ASC');
        $profiles = Database::fetchAll(
            'SELECT
                p.username,
                p.profile_photo AS photo,
                p.bio,
                p.headline,
                COALESCE(NULLIF(p.display_name, ""), u.name) AS name,
                c.name AS category_name
             FROM profiles p
             JOIN users u ON u.id = p.user_id
             JOIN categories c ON c.id = p.category_id
             WHERE p.is_published = 1
             ORDER BY p.created_at DESC
             LIMIT 6'
        );

        $this->view('home/index', [
            'title' => 'AstitvaHub - Digital Business Card & Smart Profile',
            'metaDescription' => 'Create one shareable digital identity profile with QR code, contact saving, portfolio sections, social links and analytics.',
            'categories' => $categories,
            'plans' => $plans,
            'profiles' => $profiles,
        ]);
    }

    public function error(): void
    {
        $this->view('home/error', ['title' => 'Not found', 'message' => 'The page you requested does not exist.']);
    }
}
