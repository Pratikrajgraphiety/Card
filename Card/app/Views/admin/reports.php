<section class="container pb-5">
    <div class="dashboard-header">
        <div>
            <span class="eyebrow">Admin</span>
            <h1 class="h3 mb-0">Reports</h1>
        </div>
        <a class="btn btn-outline-secondary" href="<?= h(url('admin')) ?>">Back</a>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead><tr><th>User</th><th>Username</th><th>Event</th><th>Total</th></tr></thead>
                    <tbody>
                    <?php foreach ($reports as $row): ?>
                        <tr>
                            <td><?= h($row['name']) ?></td>
                            <td><a href="<?= h(url($row['username'])) ?>" target="_blank"><?= h($row['username']) ?></a></td>
                            <td><?= h($row['event_type']) ?></td>
                            <td><?= h($row['total']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</section>
