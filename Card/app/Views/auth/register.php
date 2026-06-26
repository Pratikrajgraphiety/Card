<section class="container py-5 auth-wrap">
    <div class="row justify-content-center">
        <div class="col-xl-9">
            <div class="card auth-card">
                <div class="row g-0">
                    <div class="col-lg-5 auth-side">
                        <span class="eyebrow">Create account</span>
                        <h1 class="h2 mt-2">Launch your public profile link.</h1>
                        <p>Choose a category and SmartProfile adapts the profile fields for your use case.</p>
                    </div>
                    <div class="col-lg-7">
                        <form class="card-body p-4 p-lg-5" method="post" action="<?= h(url('register')) ?>">
                            <?= csrf_field() ?>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Name</label>
                                    <input class="form-control" name="name" value="<?= h(old('name')) ?>" required maxlength="120">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Email</label>
                                    <input class="form-control" type="email" name="email" value="<?= h(old('email')) ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Username</label>
                                    <input class="form-control" name="username" value="<?= h(old('username', $_GET['username'] ?? '')) ?>" required pattern="[A-Za-z0-9_-]{3,30}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Category</label>
                                    <select class="form-select" name="category_id" data-category-select data-fields-target="#categoryFields" required>
                                        <option value="">Select category</option>
                                        <?php foreach ($categories as $category): ?>
                                            <option value="<?= h($category['id']) ?>" data-slug="<?= h($category['slug']) ?>" <?= old('category_id') == $category['id'] ? 'selected' : '' ?>><?= h($category['name']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Password</label>
                                    <input class="form-control" type="password" name="password" required minlength="8">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Confirm Password</label>
                                    <input class="form-control" type="password" name="password_confirmation" required minlength="8">
                                </div>
                            </div>
                            <div id="categoryFields" class="dynamic-fields mt-4"></div>
                            <button class="btn btn-primary w-100 mt-4" type="submit">Create SmartProfile</button>
                            <p class="text-center text-secondary small mt-3 mb-0">Already registered? <a href="<?= h(url('login')) ?>">Sign in</a></p>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
