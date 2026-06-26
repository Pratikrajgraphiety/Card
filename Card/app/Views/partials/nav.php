<nav class="navbar navbar-expand-lg sticky-top app-nav">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center gap-2" href="<?= h(url('/')) ?>">
            <span class="brand-mark">AH</span>
            <span><?= h(config('app.name')) ?></span>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav" aria-controls="mainNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="mainNav">
            <ul class="navbar-nav ms-auto align-items-lg-center gap-lg-1">
                <?php if ($authUser): ?>
                    <li class="nav-item"><a class="nav-link <?= active_class('dashboard') ?>" href="<?= h(url('dashboard')) ?>">Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?= h(url('dashboard/profile')) ?>">Profile</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?= h(url('dashboard/analytics')) ?>">Analytics</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?= h(url('dashboard/upgrade')) ?>">Upgrade</a></li>
                    <?php if (($authUser['role'] ?? '') === 'admin'): ?>
                        <li class="nav-item"><a class="nav-link <?= active_class('admin') ?>" href="<?= h(url('admin')) ?>">Admin</a></li>
                    <?php endif; ?>
                    <li class="nav-item">
                        <button class="btn btn-icon" data-dark-toggle title="Toggle dark mode"><i data-lucide="moon"></i></button>
                    </li>
                    <li class="nav-item">
                        <form method="post" action="<?= h(url('logout')) ?>">
                            <?= csrf_field() ?>
                            <button class="btn btn-outline-secondary btn-sm" type="submit">Sign out</button>
                        </form>
                    </li>
                <?php else: ?>
                    <li class="nav-item"><a class="nav-link" href="<?= h(url('/')) ?>#features">Features</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?= h(url('/')) ?>#categories">Categories</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?= h(url('/')) ?>#pricing">Pricing</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?= h(url('login')) ?>">Login</a></li>
                    <li class="nav-item"><a class="btn btn-primary btn-sm" href="<?= h(url('register')) ?>">Create Profile</a></li>
                    <li class="nav-item">
                        <button class="btn btn-icon" data-dark-toggle title="Toggle dark mode"><i data-lucide="moon"></i></button>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>
