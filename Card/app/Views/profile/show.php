<?php
$lists = $lists ?? [];
$meta = $profile['meta'] ?? [];
$cover = uploaded_asset($profile['cover_image']);
$photo = uploaded_asset($profile['photo'] ?? $profile['profile_photo'] ?? null);
$fields = \App\Models\Category::fieldsFor(\App\Models\Category::find((int) $profile['category_id']));
$socialIcons = [
    'github' => 'github',
    'instagram' => 'instagram',
    'youtube' => 'youtube',
    'linkedin' => 'linkedin',
    'facebook' => 'facebook',
    'twitter' => 'twitter',
    'x' => 'twitter',
    'whatsapp' => 'message-circle',
    'website' => 'globe',
];
?>
<section class="public-profile">
    <div class="profile-cover" style="<?= $cover ? 'background-image:url(' . h($cover) . ')' : '' ?>"></div>
    <div class="container">
        <div class="public-card">
            <div class="public-head">
                <?php if ($photo): ?>
                    <img class="public-avatar" src="<?= h($photo) ?>" alt="<?= h($profile['name']) ?>">
                <?php else: ?>
                    <div class="public-avatar initials"><?= h(initials($profile['name'])) ?></div>
                <?php endif; ?>
                <div class="public-title">
                    <span class="eyebrow"><?= h($profile['category_name']) ?></span>
                    <h1><?= h($profile['name']) ?></h1>
                    <?php if ($profile['company_name']): ?><p><?= h($profile['company_name']) ?></p><?php endif; ?>
                </div>
            </div>

            <?php if ($profile['bio']): ?>
                <p class="profile-bio"><?= nl2br(h($profile['bio'])) ?></p>
            <?php endif; ?>

            <div class="profile-actions">
                <a class="btn btn-primary" href="<?= h(url('contact/' . $profile['username'] . '.vcf')) ?>"><i data-lucide="contact"></i><span>Save Contact</span></a>
                <button class="btn btn-outline-primary" type="button" data-share-url="<?= h(url($profile['username'])) ?>" data-track="share_click" data-profile-id="<?= h($profile['id']) ?>"><i data-lucide="share-2"></i><span>Share</span></button>
                <?php if (($meta['whatsapp'] ?? null) || $profile['phone']): ?>
                    <a class="btn btn-outline-success" href="<?= h(url('wa/' . $profile['username'])) ?>"><i data-lucide="message-circle"></i><span>WhatsApp</span></a>
                <?php endif; ?>
                <button class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#qrModal" type="button"><i data-lucide="qr-code"></i><span>QR</span></button>
            </div>

            <?php if (!empty($lists['social_links'])): ?>
                <div class="social-grid">
                    <?php foreach ($lists['social_links'] as $link): ?>
                        <?php $icon = $socialIcons[strtolower((string) ($link['platform'] ?? ''))] ?? 'link'; ?>
                        <a href="<?= h(url('go/' . $profile['username'] . '/social/' . $link['id'])) ?>" target="_blank" rel="noopener">
                            <i data-lucide="<?= h($icon) ?>"></i>
                            <span><?= h($link['label']) ?></span>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <div class="row g-3 mt-1">
            <div class="col-lg-5">
                <div class="profile-section">
                    <h2>Contact</h2>
                    <ul class="detail-list">
                        <?php if ($profile['phone']): ?><li><i data-lucide="phone"></i><span><?= h($profile['phone']) ?></span></li><?php endif; ?>
                        <?php if ($profile['public_email']): ?><li><i data-lucide="mail"></i><span><?= h($profile['public_email']) ?></span></li><?php endif; ?>
                        <?php if ($profile['website']): ?><li><i data-lucide="globe"></i><a href="<?= h($profile['website']) ?>" target="_blank" rel="noopener"><?= h($profile['website']) ?></a></li><?php endif; ?>
                        <?php if ($profile['address']): ?><li><i data-lucide="map-pin"></i><span><?= h($profile['address']) ?></span></li><?php endif; ?>
                    </ul>
                </div>

                <div class="profile-section">
                    <h2><?= h($profile['category_name']) ?> Details</h2>
                    <div class="meta-list">
                        <?php foreach ($fields as $field): ?>
                            <?php
                            $value = $meta[$field['name']] ?? '';
                            if (!$value) {
                                continue;
                            }
                            ?>
                            <div>
                                <span><?= h($field['label']) ?></span>
                                <?php if (($field['type'] ?? '') === 'url'): ?>
                                    <a href="<?= h($value) ?>" target="_blank" rel="noopener"><?= h($value) ?></a>
                                <?php elseif (($field['type'] ?? '') === 'file'): ?>
                                    <a href="<?= h(uploaded_asset($value)) ?>" target="_blank" rel="noopener">Open file</a>
                                <?php else: ?>
                                    <strong><?= nl2br(h($value)) ?></strong>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <div class="col-lg-7">
                <?php if (!empty($lists['skills'])): ?>
                    <div class="profile-section">
                        <h2>Skills</h2>
                        <?php foreach ($lists['skills'] as $skill): ?>
                            <div class="skill-line">
                                <span><?= h($skill['name']) ?></span>
                                <div class="progress"><div class="progress-bar" style="width: <?= (int) $skill['level'] ?>%"></div></div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <?php foreach ([
                    'services' => 'Services',
                    'products' => 'Products',
                    'projects' => 'Projects',
                    'portfolios' => 'Portfolio',
                    'education' => 'Education',
                ] as $key => $label): ?>
                    <?php if (!empty($lists[$key])): ?>
                        <div class="profile-section">
                            <h2><?= h($label) ?></h2>
                            <div class="item-stack">
                                <?php foreach ($lists[$key] as $item): ?>
                                    <article>
                                        <h3><?= h($item['title'] ?? $item['name'] ?? $item['institution'] ?? $item['degree'] ?? 'Item') ?></h3>
                                        <?php if (!empty($item['price_label'])): ?><span class="price"><?= h($item['price_label']) ?></span><?php endif; ?>
                                        <?php if (!empty($item['price'])): ?><span class="price"><?= h(($item['currency'] ?? '') ? $item['currency'] . ' ' . $item['price'] : $item['price']) ?></span><?php endif; ?>
                                        <?php if (!empty($item['degree'])): ?><p><?= h($item['degree']) ?> <?= h(trim(($item['start_year'] ?? '') . ' - ' . ($item['end_year'] ?? ''))) ?></p><?php endif; ?>
                                        <?php if (!empty($item['description'])): ?><p><?= nl2br(h($item['description'])) ?></p><?php endif; ?>
                                        <?php if (!empty($item['url'])): ?><a href="<?= h($item['url']) ?>" target="_blank" rel="noopener">Open link</a><?php endif; ?>
                                    </article>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>

                <?php if (!empty($lists['videos'])): ?>
                    <div class="profile-section">
                        <h2>Videos</h2>
                        <div class="row g-3">
                            <?php foreach ($lists['videos'] as $video): ?>
                                <div class="col-md-6">
                                    <div class="video-box">
                                        <?php if ($video['embed_url']): ?>
                                            <iframe src="<?= h($video['embed_url']) ?>" loading="lazy" allowfullscreen></iframe>
                                        <?php else: ?>
                                            <a href="<?= h($video['url']) ?>" target="_blank" rel="noopener"><?= h($video['title'] ?: $video['url']) ?></a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if (!empty($lists['gallery'])): ?>
                    <div class="profile-section">
                        <h2>Gallery</h2>
                        <div class="public-gallery">
                            <?php foreach ($lists['gallery'] as $image): ?>
                                <figure>
                                    <img src="<?= h(uploaded_asset($image['image_path'])) ?>" alt="<?= h($image['caption'] ?: 'Gallery image') ?>">
                                    <?php if ($image['caption']): ?><figcaption><?= h($image['caption']) ?></figcaption><?php endif; ?>
                                </figure>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if (!empty($lists['testimonials'])): ?>
                    <div class="profile-section">
                        <h2>Testimonials</h2>
                        <div class="item-stack">
                            <?php foreach ($lists['testimonials'] as $testimonial): ?>
                                <article>
                                    <h3><?= h($testimonial['author_name'] ?: 'Client') ?></h3>
                                    <p><?= nl2br(h($testimonial['quote'])) ?></p>
                                    <?php if ($testimonial['rating']): ?><span class="rating"><?= str_repeat('*', (int) $testimonial['rating']) ?></span><?php endif; ?>
                                </article>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<div class="modal fade" id="qrModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title h5">Scan Profile</h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
                <img class="qr-image" src="<?= h(url('qr/' . $profile['username'] . '.svg')) ?>" alt="QR code">
                <div class="d-flex gap-2 justify-content-center mt-3">
                    <a class="btn btn-outline-primary" href="<?= h(url('qr/' . $profile['username'] . '/download')) ?>"><i data-lucide="download"></i><span>Download</span></a>
                    <button class="btn btn-outline-secondary" type="button" data-print-qr><i data-lucide="printer"></i><span>Print</span></button>
                </div>
            </div>
        </div>
    </div>
</div>
