<?php

namespace App\Core;

abstract class Controller
{
    protected Request $request;

    public function __construct()
    {
        $this->request = new Request();
    }

    protected function view(string $view, array $data = [], string $layout = 'layouts/app'): void
    {
        View::render($view, $data, $layout);
    }

    protected function json(array $payload, int $status = 200): never
    {
        json_response($payload, $status);
    }

    protected function redirect(string $path): never
    {
        redirect($path);
    }

    protected function requireAuth(): array
    {
        $user = Auth::user();
        if (!$user) {
            flash('info', 'Please sign in to continue.');
            redirect('login');
        }

        return $user;
    }

    protected function requireAdmin(): array
    {
        $user = $this->requireAuth();
        if (($user['role'] ?? '') !== 'admin') {
            http_response_code(403);
            $this->view('home/error', ['title' => 'Forbidden', 'message' => 'You do not have permission to access this page.']);
            exit;
        }

        return $user;
    }
}
