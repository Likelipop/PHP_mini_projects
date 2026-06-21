<div class="auth-wrapper">
    <div class="auth-card">
        <div class="auth-header">
            <h2 class="auth-title">Đăng ký tài khoản</h2>
            <p class="auth-subtitle">Trở thành thành viên để lưu trữ, liên kết ghi chú và đóng gói tài nguyên học tập.</p>
        </div>

        <form action="/register" method="POST" class="auth-form">
            <!-- CSRF protection -->
            <?= csrf_field() ?>
            
            <!-- Honeypot protection -->
            <?= honeypot_field() ?>

            <div class="form-group">
                <label for="username" class="form-label">Tên đăng nhập</label>
                <div class="input-wrapper">
                    <i class="fa-solid fa-user input-icon"></i>
                    <input type="text" name="username" id="username" class="form-control <?= isset($errors['username']) ? 'is-invalid' : '' ?>" placeholder="Nhập tên đăng nhập..." value="<?= h($old['username'] ?? '') ?>" required autofocus>
                </div>
                <?php if (isset($errors['username'])): ?>
                    <div class="invalid-feedback"><?= h($errors['username']) ?></div>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="email" class="form-label">Email</label>
                <div class="input-wrapper">
                    <i class="fa-solid fa-envelope input-icon"></i>
                    <input type="email" name="email" id="email" class="form-control <?= isset($errors['email']) ? 'is-invalid' : '' ?>" placeholder="Nhập địa chỉ email..." value="<?= h($old['email'] ?? '') ?>" required>
                </div>
                <?php if (isset($errors['email'])): ?>
                    <div class="invalid-feedback"><?= h($errors['email']) ?></div>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="password" class="form-label">Mật khẩu</label>
                <div class="input-wrapper">
                    <i class="fa-solid fa-lock input-icon"></i>
                    <input type="password" name="password" id="password" class="form-control <?= isset($errors['password']) ? 'is-invalid' : '' ?>" placeholder="Nhập mật khẩu (ít nhất 6 ký tự)..." required>
                </div>
                <?php if (isset($errors['password'])): ?>
                    <div class="invalid-feedback"><?= h($errors['password']) ?></div>
                <?php endif; ?>
            </div>

            <button type="submit" class="btn btn-primary btn-block">Đăng ký thành viên</button>
        </form>

        <div class="auth-footer">
            Đã có tài khoản? <a href="/login" class="auth-link">Đăng nhập</a>
        </div>
    </div>
</div>
