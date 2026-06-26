<?php $totals = $stats['totals'] ?? []; ?>
<section class="container pb-5">
    <div class="dashboard-header">
        <div>
            <span class="eyebrow">Analytics</span>
            <h1 class="h3 mb-0">Profile performance</h1>
        </div>
    </div>

    <div class="row g-3">
        <?php foreach ([
            ['Profile Views', $totals['profile_views'] ?? 0, 'eye'],
            ['Unique Visitors', $totals['unique_visitors'] ?? 0, 'users'],
            ['QR Scans', $totals['qr_scans'] ?? 0, 'qr-code'],
            ['Social Clicks', $totals['social_clicks'] ?? 0, 'mouse-pointer-click'],
            ['WhatsApp Clicks', $totals['whatsapp_clicks'] ?? 0, 'message-circle'],
            ['Contact Downloads', $totals['contact_downloads'] ?? 0, 'download'],
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
            <h2 class="h5">Last 30 days</h2>
            <canvas id="analyticsChart" height="110" data-series="<?= h(json_encode($stats['series'] ?? [])) ?>"></canvas>
        </div>
    </div>

    <div class="card mt-3">
        <div class="card-body">
            <h2 class="h5">Recent events</h2>
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead><tr><th>Event</th><th>Label</th><th>Referer</th><th>Time</th></tr></thead>
                    <tbody>
                    <?php foreach ($events as $event): ?>
                        <tr>
                            <td><?= h($event['event_type']) ?></td>
                            <td><?= h($event['event_label']) ?></td>
                            <td class="text-truncate" style="max-width: 240px"><?= h($event['referer']) ?></td>
                            <td><?= h($event['occurred_at']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</section>
