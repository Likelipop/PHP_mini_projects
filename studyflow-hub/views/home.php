<div class="hero-section">
    <div class="hero-content">
        <h1 class="hero-title">Kiến thức không đơn thuần là tài liệu</h1>
        <p class="hero-subtitle">Liên kết Tài liệu + Ghi chú Markdown thành các hành trình học tập thông minh (StudyFlows) và chia sẻ cho cộng đồng.</p>
        <div class="hero-actions">
            <a href="/studyflows/create" class="btn btn-lg btn-primary">
                <i class="fa-solid fa-square-plus"></i> Tạo StudyFlow mới
            </a>
            <a href="/studyflows" class="btn btn-lg btn-secondary">
                <i class="fa-solid fa-magnifying-glass"></i> Khám phá tài nguyên
            </a>
        </div>
    </div>
    <div class="hero-stats">
        <div class="stat-card">
            <div class="stat-num">Asset-centric</div>
            <div class="stat-label">Triết lý thiết kế</div>
        </div>
        <div class="stat-card">
            <div class="stat-num">Tag Graph</div>
            <div class="stat-label">Hệ thần kinh tri thức</div>
        </div>
    </div>
</div>

<div class="section-container">
    <div class="section-header">
        <h2 class="section-title"><i class="fa-solid fa-fire icon-accent"></i> StudyFlows Nổi bật</h2>
        <a href="/studyflows" class="see-all-link">Xem tất cả <i class="fa-solid fa-arrow-right"></i></a>
    </div>

    <div class="studyflow-grid">
        <?php if (!empty($trending)): ?>
            <?php foreach ($trending as $flow): ?>
                <div class="studyflow-card <?= $flow['is_pinned'] ? 'pinned' : '' ?>">
                    <?php if ($flow['is_pinned']): ?>
                        <div class="pin-badge"><i class="fa-solid fa-thumbtack"></i> Pinned</div>
                    <?php endif; ?>
                    <div class="card-body">
                        <h3 class="card-title">
                            <a href="/studyflow/<?= h($flow['slug']) ?>"><?= h($flow['title']) ?></a>
                        </h3>
                        <p class="card-desc"><?= h(mb_strimwidth($flow['description'] ?? '', 0, 120, '...')) ?></p>
                        <div class="card-meta">
                            <span class="meta-item"><i class="fa-solid fa-calendar-day"></i> <?= date('d/m/Y', strtotime($flow['created_at'])) ?></span>
                            <span class="meta-item"><i class="fa-solid fa-lock-open"></i> <?= $flow['is_public'] ? 'Công khai' : 'Riêng tư' ?></span>
                        </div>
                    </div>
                    <div class="card-footer">
                        <a href="/studyflow/<?= h($flow['slug']) ?>" class="btn btn-sm btn-outline btn-block">
                            Vào Workspace <i class="fa-solid fa-chevron-right"></i>
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="empty-state card-full">
                <i class="fa-solid fa-layer-group empty-icon"></i>
                <p>Chưa có StudyFlow nào được tạo. Hãy tạo một StudyFlow mới để bắt đầu học tập!</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<div class="section-container">
    <div class="section-header">
        <h2 class="section-title"><i class="fa-solid fa-clock icon-accent"></i> Mới cập nhật</h2>
    </div>

    <div class="studyflow-list">
        <?php if (!empty($newest)): ?>
            <?php foreach ($newest as $flow): ?>
                <div class="studyflow-list-item">
                    <div class="item-icon-wrapper">
                        <i class="fa-solid fa-folder-open"></i>
                    </div>
                    <div class="item-details">
                        <h4 class="item-title">
                            <a href="/studyflow/<?= h($flow['slug']) ?>"><?= h($flow['title']) ?></a>
                        </h4>
                        <p class="item-desc"><?= h(mb_strimwidth($flow['description'] ?? '', 0, 150, '...')) ?></p>
                    </div>
                    <div class="item-meta">
                        <span class="badge badge-tag">/<?= h($flow['slug']) ?></span>
                        <span class="item-date"><?= date('d/m/Y', strtotime($flow['created_at'])) ?></span>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="empty-state">
                <p>Không tìm thấy bản cập nhật nào.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<div class="section-container">
    <div class="section-header">
        <h2 class="section-title"><i class="fa-solid fa-tags icon-accent"></i> Chủ đề phổ biến</h2>
    </div>
    <div class="popular-tags-container">
        <?php foreach ($popularTags as $tag): ?>
            <a href="/studyflows?search=<?= urlencode($tag['prefix']) ?>" class="popular-tag-card">
                <div class="tag-hash">#</div>
                <div class="tag-details">
                    <span class="tag-card-name"><?= h($tag['name']) ?></span>
                    <span class="tag-card-prefix"><?= h($tag['prefix']) ?></span>
                </div>
            </a>
        <?php endforeach; ?>
    </div>
</div>
