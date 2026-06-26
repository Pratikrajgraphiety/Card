<?php $totals = $stats['totals'] ?? []; ?>
<section class="container pb-5">
    <div class="dashboard-header">
        <div>
            <span class="eyebrow">Dashboard</span>
            <h1 class="h3 mb-0">Hello, <?= h($user['name']) ?></h1>
        </div>
        <?php if ($profile): ?>
            <a class="btn btn-primary d-inline-flex align-items-center gap-2" href="<?= h(url($profile['username'])) ?>" target="_blank"><i data-lucide="external-link"></i><span>View Profile</span></a>
        <?php endif; ?>
    </div>

    <div class="row g-3">
        <?php foreach ([
            ['Profile Views', $totals['profile_views'] ?? 0, 'eye'],
            ['Unique Visitors', $totals['unique_visitors'] ?? 0, 'users'],
            ['QR Scans', $totals['qr_scans'] ?? 0, 'scan-line'],
            ['Contact Downloads', $totals['contact_downloads'] ?? 0, 'download'],
        ] as $card): ?>
            <div class="col-md-3 col-6">
                <div class="metric-card">
                    <i data-lucide="<?= h($card[2]) ?>"></i>
                    <span><?= h($card[0]) ?></span>
                    <strong><?= h($card[1]) ?></strong>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="row g-3 mt-2">
        <div class="col-lg-7">
            <div class="card h-100">
                <div class="card-body">
                    <h2 class="h5">Profile health</h2>
                    <?php if ($profile): ?>
                        <div class="profile-health">
                            <div class="avatar avatar-lg"><?= h(initials($profile['name'])) ?></div>
                            <div>
                                <h3 class="h5 mb-1"><?= h($profile['name']) ?></h3>
                                <p class="text-secondary mb-2"><?= h($profile['category_name']) ?> - <?= h(url($profile['username'])) ?></p>
                                <div class="progress">
                                    <?php
                                    $score = 35;
                                    $score += $profile['bio'] ? 15 : 0;
                                    $score += $profile['photo'] ? 15 : 0;
                                    $score += $profile['phone'] ? 10 : 0;
                                    $score += $profile['website'] ? 10 : 0;
                                    $score += ($totals['profile_views'] ?? 0) > 0 ? 15 : 0;
                                    $score = min(100, $score);
                                    ?>
                                    <div class="progress-bar" style="width: <?= $score ?>%"><?= $score ?>%</div>
                                </div>
                            </div>
                        </div>
                    <?php else: ?>
                        <p class="text-secondary mb-0">Create a profile to start tracking views and scans.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="col-lg-5">
            <div class="card h-100">
                <div class="card-body">
                    <h2 class="h5">Quick actions</h2>
                    <div class="quick-actions">
                        <a href="<?= h(url('dashboard/profile')) ?>"><i data-lucide="user-round-cog"></i><span>Edit profile</span></a>
                        <a href="<?= h(url('dashboard/qr')) ?>"><i data-lucide="qr-code"></i><span>Download QR</span></a>
                        <a href="<?= h(url('dashboard/analytics')) ?>"><i data-lucide="bar-chart-3"></i><span>Open analytics</span></a>
                        <a href="<?= h(url('dashboard/upgrade')) ?>"><i data-lucide="credit-card"></i><span>Upgrade plan</span></a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
