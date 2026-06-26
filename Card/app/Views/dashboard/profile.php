<?php
$meta = $profile['meta'] ?? [];
$themes = config('app.themes');
$socialRows = $lists['social_links'] ?: [['platform' => '', 'label' => '', 'url' => '']];
$skillRows = $lists['skills'] ?: [['name' => '', 'level' => 80]];
$educationRows = $lists['education'] ?: [['institution' => '', 'degree' => '', 'start_year' => '', 'end_year' => '', 'description' => '']];
$projectRows = $lists['projects'] ?: [['title' => '', 'url' => '', 'description' => '']];
$serviceRows = $lists['services'] ?: [['title' => '', 'description' => '', 'price_label' => '']];
$productRows = $lists['products'] ?: [['name' => '', 'description' => '', 'price' => '']];
$portfolioRows = $lists['portfolios'] ?: [['title' => '', 'url' => '', 'description' => '']];
$videoRows = $lists['videos'] ?: [['title' => '', 'url' => '', 'embed_url' => '']];
$testimonialRows = $lists['testimonials'] ?: [['author_name' => '', 'quote' => '', 'rating' => '5']];
?>
<section class="container pb-5">
    <div class="dashboard-header">
        <div>
            <span class="eyebrow">Profile editor</span>
            <h1 class="h3 mb-0">Build your SmartProfile</h1>
        </div>
        <a class="btn btn-outline-primary d-inline-flex align-items-center gap-2" href="<?= h(url($profile['username'])) ?>" target="_blank"><i data-lucide="external-link"></i><span>Preview</span></a>
    </div>

    <form method="post" action="<?= h(url('dashboard/profile')) ?>" enctype="multipart/form-data">
        <?= csrf_field() ?>
        <div class="row g-3">
            <div class="col-lg-8">
                <div class="card mb-3">
                    <div class="card-body">
                        <h2 class="h5">Core details</h2>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Username</label>
                                <input class="form-control" name="username" value="<?= h(old('username', $profile['username'])) ?>" required pattern="[A-Za-z0-9_-]{3,30}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Display Name</label>
                                <input class="form-control" name="display_name" value="<?= h(old('display_name', $profile['display_name'] ?: $profile['name'])) ?>" required maxlength="140">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Category</label>
                                <select class="form-select" name="category_id" data-category-select data-fields-target="#categoryFields" required>
                                    <?php foreach ($categories as $category): ?>
                                        <option value="<?= h($category['id']) ?>" data-slug="<?= h($category['slug']) ?>" <?= (int) $profile['category_id'] === (int) $category['id'] ? 'selected' : '' ?>><?= h($category['name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Phone</label>
                                <input class="form-control" name="phone" value="<?= h(old('phone', $profile['phone'])) ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Headline</label>
                                <input class="form-control" name="headline" value="<?= h(old('headline', $profile['headline'])) ?>" maxlength="190">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Public Email</label>
                                <input class="form-control" type="email" name="public_email" value="<?= h(old('public_email', $profile['public_email'] ?: $user['email'])) ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Website</label>
                                <input class="form-control" type="url" name="website" value="<?= h(old('website', $profile['website'])) ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Company</label>
                                <input class="form-control" name="company_name" value="<?= h(old('company_name', $profile['company_name'])) ?>">
                            </div>
                            <div class="col-12">
                                <label class="form-label">Address</label>
                                <input class="form-control" name="address" value="<?= h(old('address', $profile['address'])) ?>">
                            </div>
                            <div class="col-12">
                                <label class="form-label">Bio / About</label>
                                <textarea class="form-control" name="bio" rows="4"><?= h(old('bio', $profile['bio'])) ?></textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card mb-3">
                    <div class="card-body">
                        <h2 class="h5">Category fields</h2>
                        <div id="categoryFields" class="dynamic-fields">
                            <div class="row g-3">
                                <?php foreach ($fields as $field): ?>
                                    <div class="col-md-6">
                                        <label class="form-label"><?= h($field['label']) ?></label>
                                        <?php if (($field['type'] ?? 'text') === 'textarea'): ?>
                                            <textarea class="form-control" name="<?= h($field['name']) ?>" rows="3"><?= h(old($field['name'], $meta[$field['name']] ?? '')) ?></textarea>
                                        <?php elseif (($field['type'] ?? 'text') === 'file'): ?>
                                            <input class="form-control" type="file" name="<?= h($field['name']) ?>" accept="<?= h($field['accept'] ?? '') ?>">
                                            <?php if (!empty($meta[$field['name']])): ?><a class="small" href="<?= h(uploaded_asset($meta[$field['name']])) ?>" target="_blank">Current file</a><?php endif; ?>
                                        <?php else: ?>
                                            <input class="form-control" type="<?= h($field['type'] ?? 'text') ?>" name="<?= h($field['name']) ?>" value="<?= h(old($field['name'], $meta[$field['name']] ?? '')) ?>">
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card mb-3">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <h2 class="h5 mb-0">Social links</h2>
                            <button class="btn btn-sm btn-outline-primary" type="button" data-add-row="#socialRows"><i data-lucide="plus"></i></button>
                        </div>
                        <div id="socialRows" data-repeat-group>
                            <?php foreach ($socialRows as $i => $row): ?>
                                <div class="repeat-row row g-2 mt-2" data-repeat-row>
                                    <div class="col-md-3"><input class="form-control" name="social[<?= $i ?>][platform]" placeholder="Platform" value="<?= h($row['platform']) ?>"></div>
                                    <div class="col-md-3"><input class="form-control" name="social[<?= $i ?>][label]" placeholder="Label" value="<?= h($row['label']) ?>"></div>
                                    <div class="col-md-5"><input class="form-control" type="url" name="social[<?= $i ?>][url]" placeholder="https://..." value="<?= h($row['url']) ?>"></div>
                                    <div class="col-md-1"><button class="btn btn-outline-danger w-100" type="button" data-remove-row><i data-lucide="x"></i></button></div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <div class="card mb-3">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <h2 class="h5 mb-0">Skills</h2>
                            <button class="btn btn-sm btn-outline-primary" type="button" data-add-row="#skillRows"><i data-lucide="plus"></i></button>
                        </div>
                        <div id="skillRows" data-repeat-group>
                            <?php foreach ($skillRows as $i => $row): ?>
                                <div class="repeat-row row g-2 mt-2" data-repeat-row>
                                    <div class="col-md-8"><input class="form-control" name="skills[<?= $i ?>][name]" placeholder="Skill" value="<?= h($row['name']) ?>"></div>
                                    <div class="col-md-3"><input class="form-control" type="number" min="0" max="100" name="skills[<?= $i ?>][level]" value="<?= h($row['level']) ?>"></div>
                                    <div class="col-md-1"><button class="btn btn-outline-danger w-100" type="button" data-remove-row><i data-lucide="x"></i></button></div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <?php
                $sections = [
                    'education' => ['title' => 'Education', 'rows' => $educationRows, 'fields' => ['institution', 'degree', 'start_year', 'end_year', 'description']],
                    'projects' => ['title' => 'Projects', 'rows' => $projectRows, 'fields' => ['title', 'url', 'description']],
                    'services' => ['title' => 'Services', 'rows' => $serviceRows, 'fields' => ['title', 'description', 'price_label']],
                    'products' => ['title' => 'Products', 'rows' => $productRows, 'fields' => ['name', 'description', 'price']],
                    'portfolios' => ['title' => 'Portfolio', 'rows' => $portfolioRows, 'fields' => ['title', 'url', 'description']],
                    'videos' => ['title' => 'Videos', 'rows' => $videoRows, 'fields' => ['title', 'url', 'embed_url']],
                    'testimonials' => ['title' => 'Testimonials', 'rows' => $testimonialRows, 'fields' => ['author_name', 'quote', 'rating']],
                ];
                ?>
                <?php foreach ($sections as $name => $section): ?>
                    <div class="card mb-3">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <h2 class="h5 mb-0"><?= h($section['title']) ?></h2>
                                <button class="btn btn-sm btn-outline-primary" type="button" data-add-row="#<?= h($name) ?>Rows"><i data-lucide="plus"></i></button>
                            </div>
                            <div id="<?= h($name) ?>Rows" data-repeat-group>
                                <?php foreach ($section['rows'] as $i => $row): ?>
                                    <div class="repeat-row compact-repeat row g-2 mt-2" data-repeat-row>
                                        <?php foreach ($section['fields'] as $field): ?>
                                            <div class="col-md">
                                                <input class="form-control" name="<?= h($name) ?>[<?= $i ?>][<?= h($field) ?>]" placeholder="<?= h(ucwords(str_replace('_', ' ', $field))) ?>" value="<?= h($row[$field] ?? '') ?>">
                                            </div>
                                        <?php endforeach; ?>
                                        <div class="col-md-auto"><button class="btn btn-outline-danger" type="button" data-remove-row><i data-lucide="x"></i></button></div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>

                <div class="card mb-3">
                    <div class="card-body">
                        <h2 class="h5">Gallery</h2>
                        <div class="row g-3">
                            <div class="col-md-7"><input class="form-control" type="file" name="gallery_image" accept="image/*"></div>
                            <div class="col-md-5"><input class="form-control" name="gallery_caption" placeholder="Caption"></div>
                        </div>
                        <?php if (!empty($lists['gallery'])): ?>
                            <div class="gallery-strip mt-3">
                                <?php foreach ($lists['gallery'] as $image): ?>
                                    <img src="<?= h(uploaded_asset($image['image_path'])) ?>" alt="<?= h($image['caption'] ?? 'Gallery image') ?>">
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="sticky-panel">
                    <div class="card mb-3">
                        <div class="card-body">
                            <h2 class="h5">Media</h2>
                            <label class="form-label">Profile Photo</label>
                            <input class="form-control" type="file" name="profile_photo" accept="image/*">
                            <label class="form-label mt-3">Cover Image</label>
                            <input class="form-control" type="file" name="cover_image" accept="image/*">
                        </div>
                    </div>

                    <div class="card mb-3">
                        <div class="card-body">
                            <h2 class="h5">Theme</h2>
                            <div class="theme-grid">
                                <?php foreach ($themes as $slug => $theme): ?>
                                    <label>
                                        <input type="radio" name="theme_slug" value="<?= h($slug) ?>" <?= $profile['theme_slug'] === $slug ? 'checked' : '' ?>>
                                        <span style="--swatch:<?= h($theme['accent']) ?>"></span>
                                        <?= h($theme['name']) ?>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                            <label class="form-check form-switch mt-3">
                                <input class="form-check-input" type="checkbox" name="dark_mode" value="1" <?= $profile['dark_mode'] ? 'checked' : '' ?>>
                                <span class="form-check-label">Profile dark mode</span>
                            </label>
                        </div>
                    </div>

                    <div class="card mb-3">
                        <div class="card-body">
                            <h2 class="h5">SEO and domain</h2>
                            <label class="form-label">Custom Domain</label>
                            <input class="form-control" type="url" name="custom_domain" value="<?= h($profile['custom_domain']) ?>" placeholder="https://me.example.com">
                            <label class="form-label mt-3">SEO Title</label>
                            <input class="form-control" name="seo_title" value="<?= h($profile['seo_title']) ?>">
                            <label class="form-label mt-3">SEO Description</label>
                            <textarea class="form-control" name="seo_description" rows="3"><?= h($profile['seo_description']) ?></textarea>
                        </div>
                    </div>

                    <button class="btn btn-primary w-100 btn-lg" type="submit">Save Profile</button>
                </div>
            </div>
        </div>
    </form>
</section>
