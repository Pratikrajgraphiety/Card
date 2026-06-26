<section class="container py-5 auth-wrap">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card auth-card">
                <form class="card-body p-4 p-lg-5" method="post" action="<?= h(url('forgot-password')) ?>">
                    <?= csrf_field() ?>
                    <span class="eyebrow">Password help</span>
                    <h1 class="h3 mt-2">Reset your password</h1>
                    <p class="text-secondary">Enter your account email and SmartProfile will send a reset link if mail is configured.</p>
                    <label class="form-label">Email</label>
                    <input class="form-control" type="email" name="email" required>
                    <button class="btn btn-primary w-100 mt-4" type="submit">Send reset link</button>
                </form>
            </div>
        </div>
    </div>
</section>
