<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Database;
use App\Core\Session;
use App\Core\Validator;
use App\Models\Category;
use App\Models\Profile;
use App\Models\User;

final class AuthController extends Controller
{
    public function showRegister(): void
    {
        $this->view('auth/register', [
            'title' => 'Create your AstitvaHub',
            'categories' => Category::all(),
        ]);
    }

    public function register(): void
    {
        $data = $this->request->all();
        $errors = Validator::validate($data, [
            'name' => ['required', 'max:120'],
            'email' => ['required', 'email', 'max:190'],
            'username' => ['required', 'username'],
            'password' => ['required', 'min:8'],
        ]);

        if (($data['password'] ?? '') !== ($data['password_confirmation'] ?? '')) {
            $errors['password'][] = 'Passwords do not match.';
        }

        if (User::emailExists((string) ($data['email'] ?? ''))) {
            $errors['email'][] = 'This email is already registered.';
        }

        if (Profile::usernameExists((string) ($data['username'] ?? ''))) {
            $errors['username'][] = 'This username is already taken.';
        }

        $category = Category::find((int) ($data['category_id'] ?? 0));
        if (!$category) {
            $errors['category_id'][] = 'Select a valid category.';
        }

        if ($errors) {
            Session::backWithErrors($errors, $data);
        }

        $meta = $this->metaFromCategory($category, $data);
        $pdo = Database::connection();
        $pdo->beginTransaction();
        try {
            $userId = User::create([
                'category_id' => (int) $category['id'],
                'name' => trim((string) $data['name']),
                'email' => trim((string) $data['email']),
                'phone' => trim((string) ($data['phone'] ?? '')) ?: null,
                'password' => (string) $data['password'],
                'status' => 'active',
            ]);

            $profileId = Profile::createDefault(
                $userId,
                (int) $category['id'],
                trim((string) $data['username']),
                trim((string) $data['name']),
                trim((string) $data['email']),
                $meta
            );

            $plan = Database::fetch('SELECT id FROM plans WHERE slug = "free" LIMIT 1');
            if ($plan) {
                Database::execute(
                    'INSERT INTO subscriptions (user_id, plan_id, status, starts_at, is_lifetime, created_at, updated_at)
                     VALUES (?, ?, "active", NOW(), 1, NOW(), NOW())',
                    [$userId, (int) $plan['id']]
                );
            }

            $token = bin2hex(random_bytes(32));
            User::createEmailVerification($userId, trim((string) $data['email']), $token, date('Y-m-d H:i:s', time() + 86400));
            $verifyUrl = url('verify-email?token=' . urlencode($token));
            $this->sendLocalMail(trim((string) $data['email']), 'Verify your AstitvaHub email', "Open this secure link to verify your email:\n\n{$verifyUrl}");

            Database::execute(
                'INSERT INTO qr_codes (profile_id, qr_url, format, last_generated_at, created_at, updated_at)
                 VALUES (?, ?, "svg", NOW(), NOW(), NOW())',
                [$profileId, url('scan/' . strtolower(trim((string) $data['username'])) . '?src=qr')]
            );

            $pdo->commit();
        } catch (\Throwable $exception) {
            $pdo->rollBack();
            throw $exception;
        }

        Auth::attempt((string) $data['email'], (string) $data['password'], true);
        flash('success', 'Your AstitvaHub profile is ready. We created a verification link for your email.');
        if (config('app.debug')) {
            flash('debug_reset_link', $verifyUrl);
        }
        $this->redirect('dashboard/profile');
    }

    public function showLogin(): void
    {
        $this->view('auth/login', ['title' => 'Sign in to AstitvaHub']);
    }

    public function login(): void
    {
        $email = trim((string) $this->request->input('email'));
        $password = (string) $this->request->input('password');
        $ip = $this->request->ip();

        if (User::loginLock($email, $ip)) {
            Session::backWithErrors(['email' => ['Too many attempts. Try again in 15 minutes.']], ['email' => $email]);
        }

        if (!Auth::attempt($email, $password, (bool) $this->request->input('remember'))) {
            User::recordLoginFailure($email, $ip);
            Session::backWithErrors(['email' => ['Invalid email or password.']], ['email' => $email]);
        }

        User::clearLoginFailures($email, $ip);
        flash('success', 'Welcome back.');
        $this->redirect('dashboard');
    }

    public function logout(): void
    {
        Auth::logout();
        redirect('login');
    }

    public function verifyEmail(): void
    {
        $token = (string) $this->request->input('token');
        $user = $token ? User::findByVerificationToken($token) : null;
        if (!$user) {
            flash('error', 'The verification link is invalid or expired.');
            $this->redirect('login');
        }

        User::markEmailVerified((int) $user['id'], (int) $user['verification_id']);
        flash('success', 'Email verified. Your profile is trusted and ready to share.');
        $this->redirect('dashboard');
    }

    public function showForgot(): void
    {
        $this->view('auth/forgot', ['title' => 'Reset password']);
    }

    public function forgot(): void
    {
        $email = trim((string) $this->request->input('email'));
        $user = User::findByEmail($email);

        if ($user) {
            $token = bin2hex(random_bytes(32));
            User::createPasswordReset((int) $user['id'], $email, $token, date('Y-m-d H:i:s', time() + 3600), $this->request->ip());
            $resetUrl = url('reset-password?token=' . urlencode($token));
            $this->sendLocalMail($email, 'Reset your AstitvaHub password', "Use this secure link to reset your password:\n\n{$resetUrl}");

            if (config('app.debug')) {
                flash('debug_reset_link', $resetUrl);
            }
        }

        flash('success', 'If that email exists, a password reset link has been sent.');
        $this->redirect('forgot-password');
    }

    public function showReset(): void
    {
        $token = (string) $this->request->input('token');
        $user = $token ? User::findByResetToken($token) : null;
        if (!$user) {
            flash('error', 'The reset link is invalid or expired.');
            $this->redirect('forgot-password');
        }

        $this->view('auth/reset', ['title' => 'Choose a new password', 'token' => $token]);
    }

    public function reset(): void
    {
        $token = (string) $this->request->input('token');
        $password = (string) $this->request->input('password');
        $confirm = (string) $this->request->input('password_confirmation');
        $user = User::findByResetToken($token);

        $errors = [];
        if (!$user) {
            $errors['token'][] = 'The reset link is invalid or expired.';
        }
        if (strlen($password) < 8) {
            $errors['password'][] = 'Password must be at least 8 characters.';
        }
        if ($password !== $confirm) {
            $errors['password'][] = 'Passwords do not match.';
        }

        if ($errors) {
            Session::backWithErrors($errors, []);
        }

        User::updatePassword((int) $user['id'], $password);
        flash('success', 'Your password has been updated. Please sign in.');
        $this->redirect('login');
    }

    private function metaFromCategory(array $category, array $data): array
    {
        $meta = [];
        foreach (Category::fieldsFor($category) as $field) {
            if (($field['type'] ?? '') === 'file') {
                continue;
            }
            $name = $field['name'];
            $meta[$name] = trim((string) ($data[$name] ?? ''));
        }

        return $meta;
    }

    private function sendLocalMail(string $email, string $subject, string $body): void
    {
        $sent = @mail($email, $subject, $body);
        if (!$sent || config('app.debug')) {
            $file = storage_path('mail/' . date('Ymd_His') . '_' . preg_replace('/[^A-Za-z0-9_-]/', '_', $email) . '.txt');
            file_put_contents($file, "To: {$email}\nSubject: {$subject}\n\n{$body}");
        }
    }
}
