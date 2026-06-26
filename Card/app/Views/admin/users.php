<section class="container pb-5">
    <div class="dashboard-header">
        <div>
            <span class="eyebrow">Admin</span>
            <h1 class="h3 mb-0">Manage Users</h1>
        </div>
        <a class="btn btn-outline-secondary" href="<?= h(url('admin')) ?>">Back</a>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead><tr><th>User</th><th>Profile</th><th>Role</th><th>Status</th><th></th></tr></thead>
                    <tbody>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td><strong><?= h($user['name']) ?></strong><br><span class="text-secondary small"><?= h($user['email']) ?></span></td>
                            <td><?= $user['username'] ? '<a href="' . h(url($user['username'])) . '" target="_blank">' . h($user['username']) . '</a>' : '-' ?><br><span class="small text-secondary"><?= h($user['category_name']) ?></span></td>
                            <td colspan="3">
                                <form class="row g-2 align-items-center" method="post" action="<?= h(url('admin/users')) ?>">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="id" value="<?= h($user['id']) ?>">
                                    <div class="col-md-3">
                                        <select class="form-select form-select-sm" name="role">
                                            <option value="user" <?= $user['role'] === 'user' ? 'selected' : '' ?>>User</option>
                                            <option value="admin" <?= $user['role'] === 'admin' ? 'selected' : '' ?>>Admin</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <select class="form-select form-select-sm" name="status">
                                            <option value="active" <?= $user['status'] === 'active' ? 'selected' : '' ?>>Active</option>
                                            <option value="blocked" <?= $user['status'] === 'blocked' ? 'selected' : '' ?>>Blocked</option>
                                            <option value="pending" <?= $user['status'] === 'pending' ? 'selected' : '' ?>>Pending</option>
                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        <button class="btn btn-sm btn-primary" type="submit">Save</button>
                                    </div>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</section>
