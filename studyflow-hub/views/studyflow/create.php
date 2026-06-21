<div class="auth-wrapper">
    <div class="auth-card wide-card">
        <div class="auth-header">
            <h2 class="auth-title">Khởi tạo StudyFlow mới</h2>
            <p class="auth-subtitle">Nhập tiêu đề và thông tin mô tả cơ bản của StudyFlow để bắt đầu xây dựng cây tri thức.</p>
        </div>

        <form action="/studyflows/create" method="POST" class="auth-form">
            <!-- CSRF protection -->
            <?= csrf_field() ?>
            
            <!-- Honeypot protection -->
            <?= honeypot_field() ?>

            <div class="form-row">
                <div class="form-group">
                    <label for="title" class="form-label">Tiêu đề StudyFlow <span class="required">*</span></label>
                    <input type="text" name="title" id="title" class="form-control <?= isset($errors['title']) ? 'is-invalid' : '' ?>" placeholder="Ví dụ: Deep Learning 2026, Machine Learning Cơ bản..." value="<?= h($old['title'] ?? '') ?>" required autofocus>
                    <?php if (isset($errors['title'])): ?>
                        <div class="invalid-feedback"><?= h($errors['title']) ?></div>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="slug" class="form-label">Đường dẫn slug (Tự động tạo nếu để trống)</label>
                    <input type="text" name="slug" id="slug" class="form-control <?= isset($errors['slug']) ? 'is-invalid' : '' ?>" placeholder="Ví dụ: deep-learning-2026" value="<?= h($old['slug'] ?? '') ?>">
                    <?php if (isset($errors['slug'])): ?>
                        <div class="invalid-feedback"><?= h($errors['slug']) ?></div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="form-group">
                <label for="description" class="form-label">Mô tả StudyFlow</label>
                <textarea name="description" id="description" class="form-control text-area" rows="4" placeholder="Tóm tắt nội dung học tập, lộ trình của StudyFlow này..."><?= h($old['description'] ?? '') ?></textarea>
            </div>

            <div class="checkbox-row">
                <div class="checkbox-group">
                    <input type="checkbox" name="is_pinned" id="is_pinned" value="1" <?= isset($old['is_pinned']) && $old['is_pinned'] ? 'checked' : '' ?>>
                    <label for="is_pinned">Ghim lên đầu trang chủ (Pinned)</label>
                </div>
                <div class="checkbox-group">
                    <input type="checkbox" name="is_public" id="is_public" value="1" <?= !isset($old['is_public']) || $old['is_public'] ? 'checked' : '' ?>>
                    <label for="is_public">Công khai cho cộng đồng (Public)</label>
                </div>
            </div>

            <div class="form-actions">
                <a href="/studyflows" class="btn btn-secondary">Hủy bỏ</a>
                <button type="submit" class="btn btn-primary">Bắt đầu Workspace</button>
            </div>
        </form>
    </div>
</div>
