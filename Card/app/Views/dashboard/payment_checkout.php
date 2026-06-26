<section class="container py-5 checkout-page">
    <div class="checkout-panel">
        <div class="checkout-icon"><i data-lucide="shield-check"></i></div>
        <span class="eyebrow">Secure checkout</span>
        <h1>Opening Cashfree</h1>
        <p class="text-secondary" data-checkout-status>
            We are redirecting you to Cashfree to complete payment for <?= h($plan['name']) ?>.
        </p>

        <div class="checkout-summary">
            <div>
                <span>Plan</span>
                <strong><?= h($plan['name']) ?></strong>
            </div>
            <div>
                <span>Amount</span>
                <strong><?= h(money((float) $plan['price'], $plan['currency'])) ?></strong>
            </div>
            <div>
                <span>Order ID</span>
                <strong><?= h($orderId) ?></strong>
            </div>
        </div>

        <button class="btn btn-primary btn-lg mt-4" type="button" data-open-cashfree>
            <i data-lucide="credit-card"></i><span>Open Checkout</span>
        </button>
        <a class="btn btn-outline-secondary mt-2" href="<?= h(url('dashboard/upgrade')) ?>">Back to plans</a>
    </div>
</section>

<script src="<?= h($cashfreeSdkUrl) ?>"></script>
<script>
(function () {
    const paymentSessionId = <?= json_encode($paymentSessionId, JSON_THROW_ON_ERROR) ?>;
    const mode = <?= json_encode($paymentMode, JSON_THROW_ON_ERROR) ?>;
    const status = document.querySelector('[data-checkout-status]');
    const button = document.querySelector('[data-open-cashfree]');

    const setStatus = (message) => {
        if (status) status.textContent = message;
    };

    const openCheckout = () => {
        if (!window.Cashfree) {
            setStatus('Cashfree checkout could not load. Please check your connection and try again.');
            return;
        }

        setStatus('Cashfree checkout is opening now...');
        const cashfree = window.Cashfree({ mode });
        cashfree.checkout({
            paymentSessionId,
            redirectTarget: '_self'
        }).catch(() => {
            setStatus('Checkout was not completed. You can reopen it or go back to plans.');
        });
    };

    button?.addEventListener('click', openCheckout);
    window.setTimeout(openCheckout, 650);
})();
</script>
