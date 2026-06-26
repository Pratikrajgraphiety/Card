<section class="container pb-5">
    <div class="dashboard-header">
        <div>
            <span class="eyebrow">Admin</span>
            <h1 class="h3 mb-0">SmartProfile Control Center</h1>
        </div>
    </div>

    <div class="admin-tabs">
        <?php foreach (['users', 'categories', 'plans', 'themes', 'payments', 'notifications', 'reports', 'settings'] as $item): ?>
            <a href="<?= h(url('admin/' . $item)) ?>"><?= h(ucwords($item)) ?></a>
        <?php endforeach; ?>
    </div>

    <div class="row g-3">
        <?php foreach ([
            ['Users', $stats['users'] ?? 0, 'users'],
            ['Profiles', $stats['profiles'] ?? 0, 'id-card'],
            ['Payments', $stats['payments'] ?? 0, 'shopping-bag'],
            ['Revenue', money((float) ($stats['revenue'] ?? 0)), 'receipt'],
            ['Views', $stats['profile_views'] ?? 0, 'eye'],
            ['QR Scans', $stats['qr_scans'] ?? 0, 'scan-line'],
        ] as $card): ?>
            <div class="col-md-2 col-6">
                <div class="metric-card">
                    <i data-lucide="<?= h($card[2]) ?>"></i>
                    <span><?= h($card[0]) ?></span>
                    <strong><?= h($card[1]) ?></strong>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="card mt-3">
        <div class="card-body">
            <h2 class="h5">Recent users</h2>
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead><tr><th>Name</th><th>Email</th><th>Username</th><th>Category</th><th>Status</th></tr></thead>
                    <tbody>
                    <?php foreach ($recentUsers as $user): ?>
                        <tr>
                            <td><?= h($user['name']) ?></td>
                            <td><?= h($user['email']) ?></td>
                            <td><?= h($user['username']) ?></td>
                            <td><?= h($user['category_name']) ?></td>
                            <td><span class="badge text-bg-secondary"><?= h($user['status']) ?></span></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</section>
