<?php
$plans = $plans ?? [];
$subscription = $subscription ?? null;
$payments = $payments ?? [];
$activePlanId = (int) ($subscription['plan_id'] ?? 0);
$phones = config('app.contact.phones', []);
$email = config('app.contact.email');
?>

<section class="container pb-5 upgrade-page">
    <div class="dashboard-header">
        <div>
            <span class="eyebrow">Billing</span>
            <h1 class="h3 mb-0">Upgrade plan</h1>
            <p class="text-secondary mb-0">Choose a plan and pay securely with Cashfree.</p>
        </div>
        <a class="btn btn-outline-secondary d-inline-flex align-items-center gap-2" href="<?= h(url('dashboard')) ?>">
            <i data-lucide="layout-dashboard"></i><span>Dashboard</span>
        </a>
    </div>

    <div class="upgrade-status mb-4">
        <div>
            <span>Current plan</span>
            <strong><?= h($subscription['plan_name'] ?? 'Free') ?></strong>
        </div>
        <div>
            <span>Payment mode</span>
            <strong><?= h(ucfirst((string) ($paymentMode ?? 'sandbox'))) ?></strong>
        </div>
        <div>
            <span>Support</span>
            <strong><?= h($email) ?></strong>
        </div>
    </div>

    <?php if (!$paymentReady): ?>
        <div class="alert alert-warning">
            Cashfree keys are missing. Add them in `.env` before accepting paid upgrades.
        </div>
    <?php endif; ?>

    <div class="row g-3">
        <?php foreach ($plans as $plan): ?>
            <?php
            $features = json_decode((string) ($plan['features_json'] ?? '[]'), true) ?: [];
            $isActive = $activePlanId === (int) $plan['id'];
            $isPaid = (float) $plan['price'] > 0;
            $isPopular = ($plan['slug'] ?? '') === 'smart';
            ?>
            <div class="col-md-4">
                <article class="home-plan upgrade-plan <?= $isPopular ? 'is-popular' : '' ?>">
                    <div class="upgrade-plan-head">
                        <div>
                            <?php if ($isPopular): ?><span class="plan-badge">Popular</span><?php endif; ?>
                            <h2><?= h($plan['name']) ?></h2>
                        </div>
                        <?php if ($isActive): ?><span class="active-plan-badge">Active</span><?php endif; ?>
                    </div>
                    <div class="plan-price"><?= h(money((float) $plan['price'], $plan['currency'])) ?></div>
                    <p class="text-secondary"><?= h(ucfirst((string) $plan['billing_type'])) ?> access</p>
                    <ul>
                        <?php foreach ($features as $feature): ?>
                            <li><i data-lucide="check"></i><span><?= h($feature) ?></span></li>
                        <?php endforeach; ?>
                    </ul>
                    <form method="post" action="<?= h(url('dashboard/upgrade')) ?>">
                        <?= csrf_field() ?>
                        <input type="hidden" name="plan" value="<?= h($plan['slug']) ?>">
                        <button
                            class="btn <?= $isPopular ? 'btn-primary' : 'btn-outline-primary' ?> w-100"
                            type="submit"
                            <?= ($isActive || ($isPaid && !$paymentReady)) ? 'disabled' : '' ?>
                        >
                            <?php if ($isActive): ?>
                                Active now
                            <?php elseif ($isPaid): ?>
                                Pay with Cashfree
                            <?php else: ?>
                                Activate free
                            <?php endif; ?>
                        </button>
                    </form>
                </article>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="row g-3 mt-2">
        <div class="col-lg-7">
            <div class="card h-100">
                <div class="card-body">
                    <h2 class="h5">Recent payments</h2>
                    <?php if ($payments): ?>
                        <div class="table-responsive">
                            <table class="table align-middle payment-table">
                                <thead>
                                <tr>
                                    <th>Plan</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                    <th>Order</th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php foreach ($payments as $payment): ?>
                                    <tr>
                                        <td><?= h($payment['plan_name']) ?></td>
                                        <td><?= h(money((float) $payment['amount'], $payment['currency'])) ?></td>
                                        <td><span class="payment-status payment-status-<?= h($payment['status']) ?>"><?= h($payment['status']) ?></span></td>
                                        <td class="text-truncate" style="max-width: 180px"><?= h($payment['transaction_id']) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p class="text-secondary mb-0">Your Cashfree payment history will appear here after checkout.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="col-lg-5">
            <div class="card h-100 support-card">
                <div class="card-body">
                    <span class="eyebrow">Need help?</span>
                    <h2 class="h5 mt-2">Contact Graphiety support</h2>
                    <div class="support-list">
                        <?php foreach ($phones as $phone): ?>
                            <a href="tel:<?= h($phone) ?>"><i data-lucide="phone"></i><span><?= h($phone) ?></span></a>
                        <?php endforeach; ?>
                        <a href="mailto:<?= h(strtolower((string) $email)) ?>"><i data-lucide="mail"></i><span><?= h($email) ?></span></a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
