<div class="container d-flex justify-content-center align-items-center" style="min-height: 75vh;">
    <div class="card shadow border border-secondary-subtle p-4" style="width: 100%; max-width: 400px; background-color: var(--bg-card);">
        <div class="text-center mb-4">
            <i class="fa-solid fa-square-share-nodes text-primary fs-1 mb-2"></i>
            <h3 class="fw-bold mb-1" style="font-family: var(--font-heading);">Đăng nhập</h3>
            <p class="text-muted small">Chào mừng bạn quay lại với StudyFlow Hub</p>
        </div>

        <?php if (isset($error) && $error !== ''): ?>
            <div class="alert alert-danger alert-dismissible fade show small py-2 px-3 mb-3" role="alert">
                <i class="fa-solid fa-triangle-exclamation me-1"></i> <?= h($error) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close" style="padding: 0.75rem 1rem; font-size: 0.5rem;"></button>
            </div>
        <?php endif; ?>

        <form action="/login" method="POST">
            <!-- CSRF field -->
            <?= csrf_field() ?>

            <!-- Honeypot field -->
            <?= honeypot_field() ?>

            <!-- Username input -->
            <div class="mb-3">
                <label for="username" class="form-label small fw-semibold text-muted">Tên đăng nhập</label>
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-body-tertiary"><i class="fa-regular fa-user text-muted"></i></span>
                    <input type="text" name="username" id="username" class="form-control" placeholder="Nhập username..." value="<?= h($old['username'] ?? '') ?>" required autofocus>
                </div>
            </div>

            <!-- Password input -->
            <div class="mb-4">
                <label for="password" class="form-label small fw-semibold text-muted">Mật khẩu</label>
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-body-tertiary"><i class="fa-solid fa-lock text-muted"></i></span>
                    <input type="password" name="password" id="password" class="form-control" placeholder="Nhập password..." required>
                </div>
            </div>

            <!-- Submit button -->
            <button type="submit" class="btn btn-sm btn-primary w-100 fw-semibold"><i class="fa-solid fa-right-to-bracket me-1"></i> Tiếp tục</button>
        </form>

        <div class="text-center mt-3 pt-3 border-top border-light-subtle small">
            <span class="text-muted">Chưa có tài khoản?</span>
            <a href="/register" class="text-decoration-none fw-bold ms-1">Đăng ký ngay</a>
        </div>
    </div>
</div>
