<section class="container py-5 auth-wrap">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card auth-card">
                <form class="card-body p-4 p-lg-5" method="post" action="<?= h(url('login')) ?>">
                    <?= csrf_field() ?>
                    <span class="eyebrow">Welcome back</span>
                    <h1 class="h3 mt-2">Sign in to SmartProfile</h1>
                    <div class="mt-4">
                        <label class="form-label">Email</label>
                        <input class="form-control" type="email" name="email" value="<?= h(old('email')) ?>" required>
                    </div>
                    <div class="mt-3">
                        <label class="form-label">Password</label>
                        <input class="form-control" type="password" name="password" required>
                    </div>
                    <button class="btn btn-primary w-100 mt-4" type="submit">Sign in</button>
                    <div class="d-flex justify-content-between small mt-3">
                        <a href="<?= h(url('forgot-password')) ?>">Forgot password?</a>
                        <a href="<?= h(url('register')) ?>">Create account</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>
