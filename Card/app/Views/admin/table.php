<section class="container pb-5">
    <div class="dashboard-header">
        <div>
            <span class="eyebrow">Admin</span>
            <h1 class="h3 mb-0"><?= h($title) ?></h1>
        </div>
        <a class="btn btn-outline-secondary" href="<?= h(url('admin')) ?>">Back</a>
    </div>

    <?php if (in_array($table, ['categories', 'plans', 'themes', 'notifications'], true)): ?>
        <div class="card mb-3">
            <div class="card-body">
                <h2 class="h5">Create or update</h2>
                <form class="row g-3" method="post" action="<?= h(url('admin/' . $table)) ?>">
                    <?= csrf_field() ?>
                    <?php if ($table === 'categories'): ?>
                        <div class="col-md-3"><input class="form-control" name="name" placeholder="Name" required></div>
                        <div class="col-md-3"><input class="form-control" name="slug" placeholder="slug" required></div>
                        <div class="col-md-6"><input class="form-control" name="description" placeholder="Description"></div>
                        <div class="col-12"><textarea class="form-control font-monospace" name="fields_json" rows="4" placeholder='[{"name":"field","label":"Field","type":"text"}]'>[]</textarea></div>
                    <?php elseif ($table === 'plans'): ?>
                        <div class="col-md-3"><input class="form-control" name="name" placeholder="Name" required></div>
                        <div class="col-md-3"><input class="form-control" name="slug" placeholder="slug" required></div>
                        <div class="col-md-2"><input class="form-control" type="number" step="0.01" name="price" placeholder="Price"></div>
                        <div class="col-md-2"><input class="form-control" name="currency" value="INR"></div>
                        <div class="col-md-2">
                            <select class="form-select" name="billing_type">
                                <option value="lifetime">Lifetime</option>
                                <option value="monthly">Monthly</option>
                                <option value="yearly">Yearly</option>
                                <option value="free">Free</option>
                            </select>
                        </div>
                        <div class="col-12"><textarea class="form-control font-monospace" name="features_json" rows="3" placeholder='["Feature"]'>[]</textarea></div>
                    <?php elseif ($table === 'themes'): ?>
                        <div class="col-md-4"><input class="form-control" name="name" placeholder="Name" required></div>
                        <div class="col-md-4"><input class="form-control" name="slug" placeholder="slug" required></div>
                        <div class="col-md-4"><input class="form-control form-control-color w-100" type="color" name="accent_color" value="#4f46e5"></div>
                    <?php elseif ($table === 'notifications'): ?>
                        <div class="col-md-2"><input class="form-control" name="user_id" placeholder="User ID"></div>
                        <div class="col-md-4"><input class="form-control" name="title" placeholder="Title" required></div>
                        <div class="col-md-2"><input class="form-control" name="type" value="info"></div>
                        <div class="col-md-4"><input class="form-control" name="body" placeholder="Message"></div>
                    <?php endif; ?>
                    <div class="col-12"><button class="btn btn-primary" type="submit">Save</button></div>
                </form>
            </div>
        </div>
    <?php endif; ?>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead>
                    <tr>
                        <?php foreach (array_keys($rows[0] ?? ['id' => '']) as $column): ?>
                            <th><?= h($column) ?></th>
                        <?php endforeach; ?>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($rows as $row): ?>
                        <tr>
                            <?php foreach ($row as $value): ?>
                                <td class="text-truncate" style="max-width: 320px"><?= h(is_scalar($value) ? (string) $value : json_encode($value)) ?></td>
                            <?php endforeach; ?>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</section>
