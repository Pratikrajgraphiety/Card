<section class="container pb-5">
    <div class="dashboard-header">
        <div>
            <span class="eyebrow">Admin</span>
            <h1 class="h3 mb-0">Settings</h1>
        </div>
        <a class="btn btn-outline-secondary" href="<?= h(url('admin')) ?>">Back</a>
    </div>

    <form class="card" method="post" action="<?= h(url('admin/settings')) ?>">
        <?= csrf_field() ?>
        <div class="card-body">
            <div class="row g-3">
                <?php foreach ($rows as $row): ?>
                    <?php
                    $key = $row['setting_key'] ?? $row['key'] ?? '';
                    $value = $row['setting_value'] ?? $row['value'] ?? '';
                    $type = $row['setting_type'] ?? $row['type'] ?? 'text';
                    $inputType = in_array($type, ['email', 'url', 'number', 'password'], true) ? $type : 'text';
                    ?>
                    <div class="col-md-6">
                        <label class="form-label"><?= h($key) ?></label>
                        <?php if ($type === 'textarea'): ?>
                            <textarea class="form-control" name="settings[<?= h($key) ?>]" rows="3"><?= h($value) ?></textarea>
                        <?php else: ?>
                            <input class="form-control" type="<?= h($inputType) ?>" name="settings[<?= h($key) ?>]" value="<?= h($value) ?>">
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
                <div class="col-md-6">
                    <label class="form-label">meta_pixel_id</label>
                    <input class="form-control" name="settings[meta_pixel_id]" placeholder="Optional">
                </div>
            </div>
            <button class="btn btn-primary mt-4" type="submit">Save Settings</button>
        </div>
    </form>
</section>
