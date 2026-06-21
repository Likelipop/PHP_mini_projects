<div class="container d-flex justify-content-center align-items-center" style="min-height: 75vh;">
    <div class="card shadow border border-secondary-subtle p-4" style="width: 100%; max-width: 420px; background-color: var(--bg-card);">
        <div class="text-center mb-4">
            <i class="fa-solid fa-user-plus text-primary fs-1 mb-2"></i>
            <h3 class="fw-bold mb-1" style="font-family: var(--font-heading);">Đăng ký</h3>
            <p class="text-muted small">Khởi tạo tài khoản StudyFlow Hub</p>
        </div>

        <form action="/register" method="POST">
            <!-- CSRF field -->
            <?= csrf_field() ?>

            <!-- Honeypot field -->
            <?= honeypot_field() ?>

            <!-- Username input -->
            <div class="mb-3">
                <label for="username" class="form-label small fw-semibold text-muted">Tên đăng nhập</label>
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-body-tertiary"><i class="fa-regular fa-user text-muted"></i></span>
                    <input type="text" name="username" id="username" class="form-control <?= isset($errors['username']) ? 'is-invalid' : '' ?>" placeholder="Nhập username..." value="<?= h($old['username'] ?? '') ?>" required autofocus>
                </div>
                <div class="form-text text-muted" style="font-size: 0.75rem;">Chỉ gồm chữ thường, số, dấu chấm (.), dấu gạch dưới (_). Độ dài 3-30 ký tự.</div>
                <?php if (isset($errors['username'])): ?>
                    <div class="invalid-feedback small d-block"><?= h($errors['username']) ?></div>
                <?php endif; ?>
            </div>

            <!-- Email input -->
            <div class="mb-3">
                <label for="email" class="form-label small fw-semibold text-muted">Email</label>
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-body-tertiary"><i class="fa-regular fa-envelope text-muted"></i></span>
                    <input type="email" name="email" id="email" class="form-control <?= isset($errors['email']) ? 'is-invalid' : '' ?>" placeholder="Nhập email..." value="<?= h($old['email'] ?? '') ?>" required>
                </div>
                <?php if (isset($errors['email'])): ?>
                    <div class="invalid-feedback small d-block"><?= h($errors['email']) ?></div>
                <?php endif; ?>
            </div>

            <!-- Password input -->
            <div class="mb-3">
                <label for="password" class="form-label small fw-semibold text-muted">Mật khẩu</label>
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-body-tertiary"><i class="fa-solid fa-lock text-muted"></i></span>
                    <input type="password" name="password" id="password" class="form-control <?= isset($errors['password']) ? 'is-invalid' : '' ?>" placeholder="Tối thiểu 6 ký tự..." required>
                </div>
                <?php if (isset($errors['password'])): ?>
                    <div class="invalid-feedback small d-block"><?= h($errors['password']) ?></div>
                <?php endif; ?>
            </div>

            <!-- Confirm Password input -->
            <div class="mb-4">
                <label for="confirm_password" class="form-label small fw-semibold text-muted">Xác nhận mật khẩu</label>
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-body-tertiary"><i class="fa-solid fa-lock text-muted"></i></span>
                    <input type="password" name="confirm_password" id="confirm_password" class="form-control <?= isset($errors['confirm_password']) ? 'is-invalid' : '' ?>" placeholder="Nhập lại mật khẩu..." required>
                </div>
                <?php if (isset($errors['confirm_password'])): ?>
                    <div class="invalid-feedback small d-block"><?= h($errors['confirm_password']) ?></div>
                <?php endif; ?>
            </div>

            <!-- Submit button -->
            <button type="submit" class="btn btn-sm btn-primary w-100 fw-semibold"><i class="fa-solid fa-check me-1"></i> Tạo tài khoản</button>
        </form>

        <div class="text-center mt-3 pt-3 border-top border-light-subtle small">
            <span class="text-muted">Đã có tài khoản?</span>
            <a href="/login" class="text-decoration-none fw-bold ms-1">Đăng nhập</a>
        </div>
    </div>
</div>
