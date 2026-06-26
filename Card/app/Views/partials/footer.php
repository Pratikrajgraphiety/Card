<?php
$phones = config('app.contact.phones', []);
$email = config('app.contact.email');
?>

<footer class="app-footer">
    <div class="container">
        <div class="footer-grid">
            <div>
                <a class="footer-brand" href="<?= h(url('/')) ?>">
                    <span class="brand-mark">AH</span>
                    <span><?= h(config('app.name')) ?></span>
                </a>
                <p><?= h(config('app.tagline')) ?></p>
            </div>
            <div>
                <span class="footer-label">Phone</span>
                <div class="footer-links">
                    <?php foreach ($phones as $phone): ?>
                        <a href="tel:<?= h($phone) ?>"><?= h($phone) ?></a>
                    <?php endforeach; ?>
                </div>
            </div>
            <div>
                <span class="footer-label">Email</span>
                <div class="footer-links">
                    <a href="mailto:<?= h(strtolower((string) $email)) ?>"><?= h($email) ?></a>
                </div>
            </div>
        </div>
        <div class="footer-bottom">
            <span>&copy; <?= date('Y') ?> AstitvaHub. Powered by Graphiety.</span>
            <span>Secure payments via Cashfree</span>
        </div>
    </div>
</footer>
