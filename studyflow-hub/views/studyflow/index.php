<div class="page-header">
    <div>
        <h1 class="page-title">Hệ thống StudyFlow</h1>
        <p class="page-subtitle">Khám phá các bộ sưu tập tài nguyên và sơ đồ tri thức được đóng gói bởi sinh viên.</p>
    </div>
    <div>
        <a href="/studyflows/create" class="btn btn-primary">
            <i class="fa-solid fa-plus"></i> Tạo StudyFlow mới
        </a>
    </div>
</div>

<div class="filters-bar">
    <form action="/studyflows" method="GET" class="filters-form">
        <div class="filter-search-group">
            <i class="fa-solid fa-magnifying-glass search-icon"></i>
            <input type="text" name="search" class="form-control filter-search-input" placeholder="Tìm kiếm StudyFlow..." value="<?= h($search) ?>">
        </div>

        <div class="filter-options-group">
            <div class="select-wrapper">
                <select name="sort_by" class="form-control filter-select">
                    <option value="created_at" <?= $sort_by === 'created_at' ? 'selected' : '' ?>>Mới nhất</option>
                    <option value="title" <?= $sort_by === 'title' ? 'selected' : '' ?>>Tên tiêu đề</option>
                    <option value="slug" <?= $sort_by === 'slug' ? 'selected' : '' ?>>Đường dẫn slug</option>
                </select>
            </div>

            <div class="select-wrapper">
                <select name="sort_dir" class="form-control filter-select">
                    <option value="desc" <?= $sort_dir === 'desc' ? 'selected' : '' ?>>Giảm dần</option>
                    <option value="asc" <?= $sort_dir === 'asc' ? 'selected' : '' ?>>Tăng dần</option>
                </select>
            </div>

            <button type="submit" class="btn btn-secondary filter-submit-btn">Lọc</button>
        </div>
    </form>
</div>

<div class="studyflow-grid">
    <?php if (!empty($flows)): ?>
        <?php foreach ($flows as $flow): ?>
            <div class="studyflow-card <?= $flow['is_pinned'] ? 'pinned' : '' ?>">
                <?php if ($flow['is_pinned']): ?>
                    <div class="pin-badge"><i class="fa-solid fa-thumbtack"></i> Pinned</div>
                <?php endif; ?>
                <div class="card-body">
                    <h3 class="card-title">
                        <a href="/studyflow/<?= h($flow['slug']) ?>"><?= h($flow['title']) ?></a>
                    </h3>
                    <p class="card-desc"><?= h(mb_strimwidth($flow['description'] ?? '', 0, 150, '...')) ?></p>
                    <div class="card-meta">
                        <span class="meta-item"><i class="fa-solid fa-calendar-day"></i> <?= date('d/m/Y', strtotime($flow['created_at'])) ?></span>
                        <span class="meta-item"><i class="fa-solid fa-user"></i> /<?= h($flow['slug']) ?></span>
                    </div>
                </div>
                <div class="card-footer">
                    <a href="/studyflow/<?= h($flow['slug']) ?>" class="btn btn-sm btn-outline btn-block">
                        Mở Workspace <i class="fa-solid fa-circle-play"></i>
                    </a>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="empty-state card-full">
            <i class="fa-solid fa-boxes-packing empty-icon"></i>
            <h3>Không tìm thấy StudyFlow nào</h3>
            <p>Vui lòng thử từ khóa hoặc bộ lọc khác.</p>
        </div>
    <?php endif; ?>
</div>

<?php if ($total_pages > 1): ?>
    <div class="pagination-container">
        <ul class="pagination">
            <!-- Prev link -->
            <?php if ($page > 1): ?>
                <li class="page-item">
                    <a href="/studyflows?search=<?= urlencode($search) ?>&sort_by=<?= h($sort_by) ?>&sort_dir=<?= h($sort_dir) ?>&page=<?= $page - 1 ?>" class="page-link"><i class="fa-solid fa-chevron-left"></i> Trước</a>
                </li>
            <?php endif; ?>

            <!-- Page numbers -->
            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                    <a href="/studyflows?search=<?= urlencode($search) ?>&sort_by=<?= h($sort_by) ?>&sort_dir=<?= h($sort_dir) ?>&page=<?= $i ?>" class="page-link"><?= $i ?></a>
                </li>
            <?php endfor; ?>

            <!-- Next link -->
            <?php if ($page < $total_pages): ?>
                <li class="page-item">
                    <a href="/studyflows?search=<?= urlencode($search) ?>&sort_by=<?= h($sort_by) ?>&sort_dir=<?= h($sort_dir) ?>&page=<?= $page + 1 ?>" class="page-link">Sau <i class="fa-solid fa-chevron-right"></i></a>
                </li>
            <?php endif; ?>
        </ul>
    </div>
<?php endif; ?>
