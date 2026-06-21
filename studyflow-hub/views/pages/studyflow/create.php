<div class="container py-5 d-flex justify-content-center">
    <div class="card shadow border border-secondary-subtle p-4 w-100" style="max-width: 600px; background-color: var(--bg-card);">
        <div class="mb-4">
            <h3 class="fw-bold mb-1" style="font-family: var(--font-heading);">Tạo StudyFlow mới</h3>
            <p class="text-muted small">Cung cấp thông tin lộ trình và nhãn chủ đề để bắt đầu đóng gói học tập.</p>
        </div>

        <form action="/studyflows/create" method="POST">
            <!-- CSRF field -->
            <?= csrf_field() ?>

            <!-- Honeypot field -->
            <?= honeypot_field() ?>

            <!-- Title input -->
            <div class="mb-3">
                <label for="title" class="form-label small fw-semibold text-muted">Tiêu đề StudyFlow <span class="text-danger">*</span></label>
                <input type="text" name="title" id="title" class="form-control form-control-sm <?= isset($errors['title']) ? 'is-invalid' : '' ?>" placeholder="Ví dụ: Lập trình Web nâng cao 2026..." value="<?= h($old['title'] ?? '') ?>" required autofocus>
                <?php if (isset($errors['title'])): ?>
                    <div class="invalid-feedback small d-block"><?= h($errors['title']) ?></div>
                <?php endif; ?>
            </div>

            <!-- Slug input -->
            <div class="mb-3">
                <label for="slug" class="form-label small fw-semibold text-muted">Đường dẫn slug (Tùy chọn)</label>
                <input type="text" name="slug" id="slug" class="form-control form-control-sm <?= isset($errors['slug']) ? 'is-invalid' : '' ?>" placeholder="Ví dụ: web-advanced-2026 (để trống sẽ tự sinh)" value="<?= h($old['slug'] ?? '') ?>">
                <?php if (isset($errors['slug'])): ?>
                    <div class="invalid-feedback small d-block"><?= h($errors['slug']) ?></div>
                <?php endif; ?>
            </div>

            <!-- Description input -->
            <div class="mb-3">
                <label for="description" class="form-label small fw-semibold text-muted">Mô tả chi tiết StudyFlow</label>
                <textarea name="description" id="description" class="form-control form-control-sm" rows="4" placeholder="Viết vài dòng giới thiệu về tài nguyên hoặc lộ trình của StudyFlow này..."><?= h($old['description'] ?? '') ?></textarea>
            </div>

            <!-- Options -->
            <div class="row g-2 mb-4">
                <div class="col-6">
                    <div class="form-check form-switch small">
                        <input class="form-check-input" type="checkbox" name="is_public" id="is_public" value="1" <?= !isset($old['is_public']) || $old['is_public'] ? 'checked' : '' ?>>
                        <label class="form-check-label fw-semibold text-muted" for="is_public">Công khai (Public)</label>
                    </div>
                </div>
                <div class="col-6">
                    <div class="form-check form-switch small">
                        <input class="form-check-input" type="checkbox" name="is_pinned" id="is_pinned" value="1" <?= isset($old['is_pinned']) && $old['is_pinned'] ? 'checked' : '' ?>>
                        <label class="form-check-label fw-semibold text-muted" for="is_pinned">Ghim đầu trang (Pinned)</label>
                    </div>
                </div>
            </div>

            <!-- Submit buttons -->
            <div class="d-flex justify-content-end gap-2 border-top pt-3">
                <a href="/studyflows" class="btn btn-sm btn-outline-secondary">Hủy bỏ</a>
                <button type="submit" class="btn btn-sm btn-primary fw-semibold"><i class="fa-solid fa-square-plus me-1"></i> Bắt đầu</button>
            </div>
        </form>
    </div>
</div>
