<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Database;
use App\Services\CashfreePaymentService;

final class PaymentController extends Controller
{
    public function return(): void
    {
        $orderId = trim((string) $this->request->input('order_id'));
        if ($orderId === '') {
            http_response_code(400);
            $this->view('dashboard/payment_result', [
                'title' => 'Payment Status',
                'status' => 'failed',
                'message' => 'Missing Cashfree order ID.',
                'payment' => null,
                'order' => null,
            ]);
            return;
        }

        $payment = Database::fetch('SELECT * FROM payments WHERE transaction_id = ? LIMIT 1', [$orderId]);
        if (!$payment) {
            http_response_code(404);
            $this->view('dashboard/payment_result', [
                'title' => 'Payment Status',
                'status' => 'failed',
                'message' => 'We could not find this payment request.',
                'payment' => null,
                'order' => null,
            ]);
            return;
        }

        $service = new CashfreePaymentService();
        try {
            $order = $service->fetchOrder($orderId);
        } catch (\Throwable $exception) {
            $this->view('dashboard/payment_result', [
                'title' => 'Payment Status',
                'status' => 'pending',
                'message' => 'Payment verification is still pending. Please retry from your dashboard in a moment.',
                'payment' => $payment,
                'order' => ['error' => $exception->getMessage()],
            ]);
            return;
        }

        $cashfreeStatus = strtoupper((string) ($order['order_status'] ?? ''));
        $status = match ($cashfreeStatus) {
            'PAID' => 'paid',
            'EXPIRED', 'TERMINATED', 'FAILED' => 'failed',
            default => 'pending',
        };

        Database::execute(
            'UPDATE payments
             SET status = ?, payload_json = ?, payment_date = IF(? = "paid", NOW(), payment_date), updated_at = NOW()
             WHERE id = ?',
            [
                $status,
                json_encode(['verify_order' => $order], JSON_THROW_ON_ERROR),
                $status,
                (int) $payment['id'],
            ]
        );

        if ($status === 'paid') {
            $this->activatePaidPlan((int) $payment['user_id'], (int) $payment['plan_id']);
            $message = 'Payment received. Your plan is active now.';
            flash('success', $message);
        } elseif ($status === 'failed') {
            $message = 'Payment was not completed. You can try again from the upgrade page.';
            flash('error', $message);
        } else {
            $message = 'Payment is still pending. We will show the latest status here.';
            flash('info', $message);
        }

        $authUser = Auth::user();
        if ($authUser && (int) $authUser['id'] === (int) $payment['user_id']) {
            $this->redirect('dashboard/upgrade');
        }

        $this->view('dashboard/payment_result', [
            'title' => 'Payment Status',
            'status' => $status,
            'message' => $message,
            'payment' => array_merge($payment, ['status' => $status]),
            'order' => $order,
        ]);
    }

    private function activatePaidPlan(int $userId, int $planId): void
    {
        $existing = Database::fetch(
            'SELECT id FROM subscriptions WHERE user_id = ? AND plan_id = ? AND status = "active" LIMIT 1',
            [$userId, $planId]
        );
        if ($existing) {
            return;
        }

        $pdo = Database::connection();
        $pdo->beginTransaction();
        try {
            Database::execute(
                'UPDATE subscriptions SET status = "cancelled", updated_at = NOW() WHERE user_id = ? AND status = "active"',
                [$userId]
            );
            Database::execute(
                'INSERT INTO subscriptions (user_id, plan_id, status, starts_at, is_lifetime, created_at, updated_at)
                 VALUES (?, ?, "active", NOW(), 1, NOW(), NOW())',
                [$userId, $planId]
            );
            $pdo->commit();
        } catch (\Throwable $exception) {
            $pdo->rollBack();
            throw $exception;
        }
    }
}
