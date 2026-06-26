<section class="container py-5 auth-wrap">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card auth-card">
                <form class="card-body p-4 p-lg-5" method="post" action="<?= h(url('reset-password')) ?>">
                    <?= csrf_field() ?>
                    <input type="hidden" name="token" value="<?= h($token) ?>">
                    <span class="eyebrow">Security</span>
                    <h1 class="h3 mt-2">Choose a new password</h1>
                    <label class="form-label mt-3">Password</label>
                    <input class="form-control" type="password" name="password" required minlength="8">
                    <label class="form-label mt-3">Confirm Password</label>
                    <input class="form-control" type="password" name="password_confirmation" required minlength="8">
                    <button class="btn btn-primary w-100 mt-4" type="submit">Update password</button>
                </form>
            </div>
        </div>
    </div>
</section>
