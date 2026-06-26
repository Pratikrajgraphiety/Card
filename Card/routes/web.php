<?php

use App\Controllers\AdminController;
use App\Controllers\ApiController;
use App\Controllers\AuthController;
use App\Controllers\DashboardController;
use App\Controllers\HomeController;
use App\Controllers\PaymentController;
use App\Controllers\ProfileController;

$router->get('/', [HomeController::class, 'index']);
$router->get('/card', [HomeController::class, 'index']);
$router->get('/Card', [HomeController::class, 'index']);

$router->get('/register', [AuthController::class, 'showRegister']);
$router->post('/register', [AuthController::class, 'register']);
$router->get('/login', [AuthController::class, 'showLogin']);
$router->post('/login', [AuthController::class, 'login']);
$router->post('/logout', [AuthController::class, 'logout']);
$router->get('/forgot-password', [AuthController::class, 'showForgot']);
$router->post('/forgot-password', [AuthController::class, 'forgot']);
$router->get('/reset-password', [AuthController::class, 'showReset']);
$router->post('/reset-password', [AuthController::class, 'reset']);
$router->get('/verify-email', [AuthController::class, 'verifyEmail']);

$router->get('/dashboard', [DashboardController::class, 'index']);
$router->get('/dashboard/profile', [DashboardController::class, 'editProfile']);
$router->post('/dashboard/profile', [DashboardController::class, 'updateProfile']);
$router->get('/dashboard/analytics', [DashboardController::class, 'analytics']);
$router->get('/dashboard/qr', [DashboardController::class, 'qr']);
$router->get('/dashboard/upgrade', [DashboardController::class, 'upgrade']);
$router->post('/dashboard/upgrade', [DashboardController::class, 'choosePlan']);

$router->get('/payment/return', [PaymentController::class, 'return']);

$router->get('/admin', [AdminController::class, 'index']);
$router->get('/admin/users', [AdminController::class, 'users']);
$router->post('/admin/users', [AdminController::class, 'users']);
$router->get('/admin/categories', [AdminController::class, 'categories']);
$router->post('/admin/categories', [AdminController::class, 'categories']);
$router->get('/admin/plans', [AdminController::class, 'plans']);
$router->post('/admin/plans', [AdminController::class, 'plans']);
$router->get('/admin/themes', [AdminController::class, 'themes']);
$router->post('/admin/themes', [AdminController::class, 'themes']);
$router->get('/admin/orders', [AdminController::class, 'orders']);
$router->get('/admin/payments', [AdminController::class, 'payments']);
$router->get('/admin/notifications', [AdminController::class, 'notifications']);
$router->post('/admin/notifications', [AdminController::class, 'notifications']);
$router->get('/admin/reports', [AdminController::class, 'reports']);
$router->get('/admin/settings', [AdminController::class, 'settings']);
$router->post('/admin/settings', [AdminController::class, 'settings']);

$router->get('/api/category-fields/{slug}', [ApiController::class, 'categoryFields']);
$router->post('/api/track', [ApiController::class, 'track']);
$router->post('/api/preferences', [ApiController::class, 'preferences']);
$router->post('/api/social-links', [ApiController::class, 'socialLink']);
$router->delete('/api/social-links/{id}', [ApiController::class, 'deleteSocialLink']);

$router->get('/scan/{username}', [ProfileController::class, 'scan']);
$router->get('/wa/{username}', [ProfileController::class, 'whatsapp']);
$router->get('/go/{username}/{type}/{id}', [ProfileController::class, 'go']);
$router->get('/qr/{username}.svg', [ProfileController::class, 'qr']);
$router->get('/qr/{username}/download', [ProfileController::class, 'qrDownload']);
$router->get('/contact/{username}.vcf', [ProfileController::class, 'vcard']);

$router->get('/{username}', [ProfileController::class, 'show']);
