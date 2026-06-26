<?php
$authUser = \App\Core\Auth::user();
$profileForTheme = $profile ?? [];
$themeSlug = $profileForTheme['theme_slug'] ?? 'emerald';
$theme = config('app.themes.' . $themeSlug, config('app.themes.indigo'));
$dark = array_key_exists('dark_mode', $profileForTheme) ? !empty($profileForTheme['dark_mode']) : true;
$alerts = [
    'success' => flash('success'),
    'error' => flash('error'),
    'info' => flash('info'),
];
$formErrors = errors();
?>
<!doctype html>
<html lang="en" data-bs-theme="<?= $dark ? 'dark' : 'light' ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="<?= h(csrf_token()) ?>">
    <meta name="app-url" content="<?= h(url('/')) ?>">
    <meta name="description" content="<?= h($metaDescription ?? 'Create one shareable digital identity profile with QR, VCF contact saving and analytics.') ?>">
    <title><?= h($title ?? config('app.name')) ?></title>
    <link rel="canonical" href="<?= h(url(trim(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/', '/'))) ?>">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?= h(asset('css/app.css')) ?>" rel="stylesheet">
    <style>:root{--sp-accent:<?= h($theme['accent'] ?? '#4f46e5') ?>}</style>
</head>
<body>
<?php partial('partials/nav', ['authUser' => $authUser]); ?>

<main class="app-main">
    <div class="container py-3">
        <?php foreach ($alerts as $kind => $message): ?>
            <?php if ($message): ?>
                <div class="alert alert-<?= $kind === 'error' ? 'danger' : h($kind) ?> alert-dismissible fade show" role="alert">
                    <?= h($message) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
        <?php endforeach; ?>

        <?php if ($debugLink = flash('debug_reset_link')): ?>
            <div class="alert alert-warning">
                Local reset link: <a href="<?= h($debugLink) ?>"><?= h($debugLink) ?></a>
            </div>
        <?php endif; ?>

        <?php if ($formErrors): ?>
            <div class="alert alert-danger">
                <strong>Check these details:</strong>
                <ul class="mb-0">
                    <?php foreach ($formErrors as $messages): ?>
                        <?php foreach ((array) $messages as $message): ?>
                            <li><?= h($message) ?></li>
                        <?php endforeach; ?>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
    </div>

    <?= $content ?>
</main>

<?php partial('partials/footer'); ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/lucide@latest/dist/umd/lucide.min.js"></script>
<script src="<?= h(asset('js/app.js')) ?>"></script>
</body>
</html>
