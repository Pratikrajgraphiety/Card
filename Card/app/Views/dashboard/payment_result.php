<?php
$status = $status ?? 'pending';
$message = $message ?? 'Payment status updated.';
$payment = $payment ?? null;
$order = $order ?? null;
$icon = $status === 'paid' ? 'badge-check' : ($status === 'failed' ? 'circle-alert' : 'clock');
?>

<section class="container py-5 checkout-page">
    <div class="checkout-panel">
        <div class="checkout-icon checkout-icon-<?= h($status) ?>"><i data-lucide="<?= h($icon) ?>"></i></div>
        <span class="eyebrow">Payment status</span>
        <h1><?= h(ucfirst((string) $status)) ?></h1>
        <p class="text-secondary"><?= h($message) ?></p>

        <?php if ($payment): ?>
            <div class="checkout-summary">
                <div>
                    <span>Plan</span>
                    <strong><?= h($payment['plan_name']) ?></strong>
                </div>
                <div>
                    <span>Amount</span>
                    <strong><?= h(money((float) $payment['amount'], $payment['currency'])) ?></strong>
                </div>
                <div>
                    <span>Order status</span>
                    <strong><?= h($order['order_status'] ?? $payment['status']) ?></strong>
                </div>
            </div>
        <?php endif; ?>

        <div class="d-flex flex-wrap justify-content-center gap-2 mt-4">
            <a class="btn btn-primary" href="<?= h(url('dashboard/upgrade')) ?>">Open billing</a>
            <a class="btn btn-outline-secondary" href="<?= h(url('/')) ?>">Home</a>
        </div>
    </div>
</section>
