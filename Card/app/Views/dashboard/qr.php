<section class="container pb-5">
    <div class="dashboard-header">
        <div>
            <span class="eyebrow">QR code</span>
            <h1 class="h3 mb-0">Share and track scans</h1>
        </div>
    </div>

    <?php if ($profile): ?>
        <div class="row g-3">
            <div class="col-lg-5">
                <div class="card qr-card">
                    <div class="card-body text-center">
                        <img class="qr-image" src="<?= h(url('qr/' . $profile['username'] . '.svg')) ?>" alt="QR code">
                        <p class="text-secondary mb-0"><?= h(url('scan/' . $profile['username'])) ?></p>
                    </div>
                </div>
            </div>
            <div class="col-lg-7">
                <div class="card h-100">
                    <div class="card-body">
                        <h2 class="h5">Actions</h2>
                        <div class="quick-actions">
                            <a href="<?= h(url('qr/' . $profile['username'] . '/download')) ?>" data-track="qr_download" data-profile-id="<?= h($profile['id']) ?>"><i data-lucide="download"></i><span>Download SVG</span></a>
                            <button type="button" data-print-qr><i data-lucide="printer"></i><span>Print</span></button>
                            <button type="button" data-share-url="<?= h(url('scan/' . $profile['username'])) ?>" data-track="share_click" data-profile-id="<?= h($profile['id']) ?>"><i data-lucide="share-2"></i><span>Share</span></button>
                        </div>
                        <p class="small text-secondary mt-3 mb-0">Scans route through `/scan/<?= h($profile['username']) ?>`, record a QR scan event, then redirect to the public profile.</p>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</section>
