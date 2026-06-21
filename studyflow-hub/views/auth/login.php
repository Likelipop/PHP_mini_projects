<div class="auth-wrapper">
    <div class="auth-card">
        <div class="auth-header">
            <h2 class="auth-title">Đăng nhập hệ thống</h2>
            <p class="auth-subtitle">Chào mừng trở lại! Vui lòng điền thông tin bên dưới để tiếp tục.</p>
        </div>

        <form action="/login" method="POST" class="auth-form">
            <!-- CSRF protection -->
            <?= csrf_field() ?>
            
            <!-- Honeypot protection -->
            <?= honeypot_field() ?>

            <div class="form-group">
                <label for="username" class="form-label">Tên đăng nhập</label>
                <div class="input-wrapper">
                    <i class="fa-solid fa-user input-icon"></i>
                    <input type="text" name="username" id="username" class="form-control" placeholder="Nhập tên đăng nhập..." value="<?= h($old['username'] ?? '') ?>" required autofocus>
                </div>
            </div>

            <div class="form-group">
                <label for="password" class="form-label">Mật khẩu</label>
                <div class="input-wrapper">
                    <i class="fa-solid fa-lock input-icon"></i>
                    <input type="password" name="password" id="password" class="form-control" placeholder="Nhập mật khẩu..." required>
                </div>
            </div>

            <button type="submit" class="btn btn-primary btn-block">Đăng nhập</button>
        </form>

        <div class="auth-footer">
            Chưa có tài khoản? <a href="/register" class="auth-link">Đăng ký ngay</a>
        </div>
    </div>
</div>
