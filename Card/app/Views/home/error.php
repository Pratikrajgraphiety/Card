<section class="container py-5">
    <div class="empty-state">
        <i data-lucide="triangle-alert"></i>
        <h1 class="h3"><?= h($title ?? 'Something went wrong') ?></h1>
        <p><?= h($message ?? 'Please try again.') ?></p>
        <a class="btn btn-primary" href="<?= h(url('/')) ?>">Go Home</a>
    </div>
</section>
