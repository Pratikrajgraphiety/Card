<?php
$categories = $categories ?? [];
$plans = $plans ?? [];
$profiles = $profiles ?? [];

$features = [
    ['Contact Saving', 'Let visitors save your phone, email, website and address as a vCard.', 'contact'],
    ['QR Sharing', 'Share your profile through a QR code for cards, shops, events and packaging.', 'qr-code'],
    ['Portfolio Sections', 'Add projects, products, services, videos, documents and gallery images.', 'briefcase-business'],
    ['Social Links', 'Keep LinkedIn, Instagram, YouTube, GitHub and custom links in one place.', 'share-2'],
    ['Analytics', 'Track profile views, QR scans, contact downloads and social clicks.', 'bar-chart-3'],
    ['Themes', 'Choose a clean look that fits a student, business, creator or professional.', 'palette'],
];

$audiences = [
    ['Student', 'Resume, certificates, skills, GitHub, LinkedIn and academic projects.', 'graduation-cap'],
    ['Business', 'Services, products, PDF catalog, WhatsApp, maps, gallery and reviews.', 'store'],
    ['Freelancer', 'Portfolio, pricing packages, project highlights and testimonials.', 'laptop'],
    ['Creator', 'Videos, channels, donation links, gallery and social links.', 'video'],
    ['Professional', 'Experience, qualifications, booking link and office address.', 'user-check'],
    ['Job Seeker', 'Resume, education, experience, skills and recruiter-friendly links.', 'file-user'],
];

$faqs = [
    ['What is AstitvaHub?', 'AstitvaHub is one public link for your contact details, QR code, portfolio, social links and analytics.'],
    ['Do visitors need an app?', 'No. Your profile opens in the browser on mobile, tablet and desktop.'],
    ['Does save contact work on phones?', 'Yes. It generates a standard vCard file that opens on Android and iPhone.'],
    ['Can I edit my page later?', 'Yes. Sign in to your dashboard and update your details, category, theme and media any time.'],
];

$showcaseImage = asset('images/card-showcase.png');
?>

<section class="home-hero surface-band home-hero-dark">
    <div class="container">
        <div class="row align-items-center g-4 g-lg-5">
            <div class="col-lg-6">
                <span class="eyebrow">Premium digital card builder</span>
                <h1>AstitvaHub</h1>
                <p class="lead text-secondary">Create a black-theme profile that feels like a modern mobile business card, with contact saving, QR sharing, social links and portfolio sections in one sharp public page.</p>
                <form class="username-check d-flex flex-column flex-sm-row gap-2" action="<?= h(url('register')) ?>" method="get">
                    <div class="input-group">
                        <span class="input-group-text"><?= h(parse_url(url('/'), PHP_URL_HOST) ?: 'localhost') ?>/</span>
                        <input class="form-control" name="username" placeholder="your-name" pattern="[A-Za-z0-9_-]{3,30}">
                    </div>
                    <button class="btn btn-primary d-inline-flex align-items-center justify-content-center gap-2" type="submit">
                        <i data-lucide="arrow-right"></i><span>Start Free</span>
                    </button>
                </form>
                <div class="home-trust-row" aria-label="AstitvaHub highlights">
                    <span><i data-lucide="shield-check"></i> Browser based</span>
                    <span><i data-lucide="smartphone"></i> Mobile ready</span>
                    <span><i data-lucide="scan-line"></i> QR enabled</span>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="hero-showcase hero-showcase-pro">
                    <div class="hero-device-stage">
                        <img class="hero-reference-img" src="<?= h($showcaseImage) ?>" alt="Mobile digital profile card previews">
                        <div class="hero-scanline" aria-hidden="true"></div>
                        <div class="hero-float-badge">
                            <i data-lucide="badge-check"></i>
                            <div>
                                <strong>Live card</strong>
                                <span>Ready to scan</span>
                            </div>
                        </div>
                        <div class="hero-action-dock" aria-label="Sample profile actions">
                            <span><i data-lucide="contact"></i> Save</span>
                            <span><i data-lucide="qr-code"></i> QR</span>
                            <span><i data-lucide="send"></i> Share</span>
                        </div>
                    </div>
                    <div class="hero-metrics" aria-label="Sample profile metrics">
                        <div><strong>12.4k</strong><span>Views</span></div>
                        <div><strong>3.2k</strong><span>Clicks</span></div>
                        <div><strong>68%</strong><span>Mobile</span></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="container py-5">
    <div class="home-stat-grid">
        <div class="home-stat">
            <strong>6</strong>
            <span>Profile categories</span>
        </div>
        <div class="home-stat">
            <strong>1</strong>
            <span>Shareable link</span>
        </div>
        <div class="home-stat">
            <strong>24/7</strong>
            <span>Public access</span>
        </div>
        <div class="home-stat">
            <strong>VCF</strong>
            <span>Contact download</span>
        </div>
    </div>
</section>

<section class="home-section" id="features">
    <div class="container">
        <div class="section-heading">
            <div>
                <span class="eyebrow">Features</span>
                <h2>Everything important on one page</h2>
            </div>
            <p>Simple tools for sharing who you are and how people can reach you.</p>
        </div>
        <div class="home-card-grid">
            <?php foreach ($features as $feature): ?>
                <article class="home-info-card">
                    <i data-lucide="<?= h($feature[2]) ?>"></i>
                    <h3><?= h($feature[0]) ?></h3>
                    <p><?= h($feature[1]) ?></p>
                </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="home-section home-muted-band" id="categories">
    <div class="container">
        <div class="section-heading">
            <div>
                <span class="eyebrow">Categories</span>
                <h2>Built for real use cases</h2>
            </div>
            <p>Start with a category and SmartProfile loads the right detail fields.</p>
        </div>
        <div class="home-audience-grid">
            <?php foreach ($audiences as $audience): ?>
                <article class="home-audience">
                    <i data-lucide="<?= h($audience[2]) ?>"></i>
                    <div>
                        <h3><?= h($audience[0]) ?></h3>
                        <p><?= h($audience[1]) ?></p>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
        <?php if ($categories): ?>
            <div class="category-pills mt-4">
                <?php foreach ($categories as $category): ?>
                    <span><?= h($category['name']) ?></span>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<section class="container py-5">
    <div class="row g-4 align-items-center">
        <div class="col-lg-5">
            <span class="eyebrow">Contact card</span>
            <h2 class="home-block-title">Save contact in one tap</h2>
            <p class="text-secondary">Visitors can open your public profile, tap save contact, and store your details without installing anything.</p>
            <div class="home-step-list">
                <span><i data-lucide="mouse-pointer-click"></i> Open profile</span>
                <span><i data-lucide="contact"></i> Download vCard</span>
                <span><i data-lucide="check-circle-2"></i> Save to phone</span>
            </div>
        </div>
        <div class="col-lg-7">
            <div class="home-workflow-panel">
                <div>
                    <i data-lucide="user-round"></i>
                    <strong>Profile</strong>
                    <span>Public detail page</span>
                </div>
                <i data-lucide="arrow-right"></i>
                <div>
                    <i data-lucide="qr-code"></i>
                    <strong>QR</strong>
                    <span>Scan and share</span>
                </div>
                <i data-lucide="arrow-right"></i>
                <div>
                    <i data-lucide="bar-chart-3"></i>
                    <strong>Analytics</strong>
                    <span>Know what works</span>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="home-section home-muted-band" id="pricing">
    <div class="container">
        <div class="section-heading">
            <div>
                <span class="eyebrow">Pricing</span>
                <h2>Simple plans</h2>
            </div>
            <p>Start free, then unlock more profile growth features when you need them.</p>
        </div>
        <div class="row g-3">
            <?php foreach ($plans as $plan): ?>
                <?php $planFeatures = json_decode((string) $plan['features_json'], true) ?: []; ?>
                <div class="col-md-4">
                    <article class="home-plan <?= ($plan['slug'] ?? '') === 'smart' ? 'is-popular' : '' ?>">
                        <?php if (($plan['slug'] ?? '') === 'smart'): ?><span class="plan-badge">Popular</span><?php endif; ?>
                        <h3><?= h($plan['name']) ?></h3>
                        <div class="plan-price"><?= h(money((float) $plan['price'], $plan['currency'])) ?></div>
                        <p class="text-secondary"><?= h(ucfirst((string) $plan['billing_type'])) ?></p>
                        <ul>
                            <?php foreach ($planFeatures as $feature): ?>
                                <li><i data-lucide="check"></i><span><?= h($feature) ?></span></li>
                            <?php endforeach; ?>
                        </ul>
                        <a class="btn <?= ($plan['slug'] ?? '') === 'smart' ? 'btn-primary' : 'btn-outline-primary' ?> w-100" href="<?= h(url('register?plan=' . urlencode((string) $plan['slug']))) ?>">Choose <?= h($plan['name']) ?></a>
                    </article>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<?php if ($profiles): ?>
<section class="container py-5">
    <div class="section-heading">
        <div>
            <span class="eyebrow">Recent profiles</span>
            <h2>Public pages on this install</h2>
        </div>
        <p>Open any profile to see the live detail page experience.</p>
    </div>
    <div class="row g-3">
        <?php foreach ($profiles as $item): ?>
            <?php $photo = uploaded_asset($item['photo'] ?? null); ?>
            <div class="col-md-4">
                <a class="profile-tile" href="<?= h(url($item['username'])) ?>">
                    <?php if ($photo): ?>
                        <img class="avatar" src="<?= h($photo) ?>" alt="<?= h($item['name']) ?>">
                    <?php else: ?>
                        <div class="avatar"><?= h(initials($item['name'])) ?></div>
                    <?php endif; ?>
                    <div>
                        <strong><?= h($item['name']) ?></strong>
                        <span><?= h($item['category_name']) ?></span>
                    </div>
                </a>
            </div>
        <?php endforeach; ?>
    </div>
</section>
<?php endif; ?>

<section class="home-section" id="faq">
    <div class="container">
        <div class="section-heading">
            <div>
                <span class="eyebrow">Questions</span>
                <h2>Clear answers</h2>
            </div>
            <p>Useful details before creating your profile.</p>
        </div>
        <div class="home-faq-list">
            <?php foreach ($faqs as $faq): ?>
                <details>
                    <summary><?= h($faq[0]) ?></summary>
                    <p><?= h($faq[1]) ?></p>
                </details>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="home-cta">
    <div class="container">
        <div class="home-cta-inner">
            <div>
                <span class="eyebrow">Ready</span>
                <h2>Create your SmartProfile today</h2>
                <p>Build a human-friendly public detail page that people can save, scan and share.</p>
            </div>
            <a class="btn btn-primary btn-lg d-inline-flex align-items-center gap-2" href="<?= h(url('register')) ?>">
                <i data-lucide="sparkles"></i><span>Create Profile</span>
            </a>
        </div>
    </div>
</section>
