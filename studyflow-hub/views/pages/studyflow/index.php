<div class="container py-4">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h2 fw-bold mb-1" style="font-family: var(--font-heading);">Thư mục StudyFlow</h1>
            <p class="text-muted small mb-0">Nơi đóng gói tài nguyên và lộ trình tự học của bạn.</p>
        </div>
        <a href="/studyflows/create" class="btn btn-sm btn-primary">
            <i class="fa-solid fa-plus me-1"></i> Tạo StudyFlow mới
        </a>
    </div>

    <!-- Filters bar -->
    <div class="card shadow-sm border border-secondary-subtle mb-4">
        <div class="card-body">
            <form action="/studyflows" method="GET" class="row g-2">
                <div class="col-md-6">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-body-tertiary border-end-0"><i class="fa-solid fa-magnifying-glass"></i></span>
                        <input type="text" name="search" class="form-control bg-body-tertiary border-start-0" placeholder="Search by name..." value="<?= h($search) ?>">
                    </div>
                </div>
                <div class="col-md-3">
                    <select name="sort_by" class="form-select form-select-sm bg-body-tertiary">
                        <option value="created_at" <?= $sort_by === 'created_at' ? 'selected' : '' ?>>Mới nhất</option>
                        <option value="title" <?= $sort_by === 'title' ? 'selected' : '' ?>>Tiêu đề</option>
                        <option value="slug" <?= $sort_by === 'slug' ? 'selected' : '' ?>>Đường dẫn slug</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <div class="d-flex gap-2">
                        <select name="sort_dir" class="form-select form-select-sm bg-body-tertiary">
                            <option value="desc" <?= $sort_dir === 'desc' ? 'selected' : '' ?>>Giảm dần</option>
                            <option value="asc" <?= $sort_dir === 'asc' ? 'selected' : '' ?>>Tăng dần</option>
                        </select>
                        <button type="submit" class="btn btn-sm btn-secondary"><i class="fa-solid fa-arrow-down-wide-short"></i></button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Cards grid -->
    <div class="studyflow-grid mb-4">
        <?php if (!empty($flows)): ?>
            <?php foreach ($flows as $flow): ?>
                <div>
                    <div class="card h-100 shadow-sm border border-secondary-subtle">
                        <div class="card-body d-flex flex-column">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="badge badge-slug badge-xs text-truncate" style="max-width: 80%;">/<?= h($flow['slug']) ?></span>
                                <?php if ($flow['is_pinned']): ?>
                                    <span class="text-warning small"><i class="fa-solid fa-thumbtack"></i></span>
                                <?php endif; ?>
                            </div>
                            <h5 class="card-title fw-bold fs-6">
                                <a href="/studyflow/<?= h($flow['slug']) ?>" class="text-decoration-none text-body hover-primary">
                                    <?= h($flow['title']) ?>
                                </a>
                            </h5>
                            <p class="card-text text-body-secondary small flex-grow-1">
                                <?= h(mb_strimwidth($flow['description'] ?? '', 0, 100, '...')) ?>
                            </p>
                            <div class="d-flex justify-content-between align-items-center mt-3 pt-2 border-top border-light-subtle text-muted small">
                                <span><i class="fa-solid fa-user me-1"></i> <?= h($flow['username'] ?? 'owner') ?></span>
                                <span><i class="fa-regular fa-clock me-1"></i> <?= date('d/m/Y', strtotime($flow['created_at'])) ?></span>
                            </div>
                        </div>
                        <div class="card-footer bg-transparent border-0 pt-0 pb-3 px-3">
                            <a href="/studyflow/<?= h($flow['slug']) ?>" class="btn btn-xs btn-outline-primary w-100">Mở Workspace <i class="fa-solid fa-chevron-right ms-1"></i></a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-12 text-center py-5 text-muted card-full">
                <i class="fa-solid fa-folder-open fs-1 mb-2"></i>
                <p class="mb-0">Chưa có StudyFlow nào được tạo.</p>
            </div>
        <?php endif; ?>
    </div>

    <!-- Pagination -->
    <?php 
        $userQuery = isset($user_id) && $user_id ? '&user_id=' . h((string)$user_id) : '';
    ?>
    <?php if ($total_pages > 1): ?>
        <nav aria-label="Page navigation" class="d-flex justify-content-center mt-4">
            <ul class="pagination pagination-sm">
                <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                    <a class="page-link" href="/studyflows?search=<?= urlencode($search) ?>&sort_by=<?= h($sort_by) ?>&sort_dir=<?= h($sort_dir) ?><?= $userQuery ?>&page=<?= $page - 1 ?>"><i class="fa-solid fa-chevron-left"></i></a>
                </li>
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                        <a class="page-link" href="/studyflows?search=<?= urlencode($search) ?>&sort_by=<?= h($sort_by) ?>&sort_dir=<?= h($sort_dir) ?><?= $userQuery ?>&page=<?= $i ?>"><?= $i ?></a>
                    </li>
                <?php endfor; ?>
                <li class="page-item <?= $page >= $total_pages ? 'disabled' : '' ?>">
                    <a class="page-link" href="/studyflows?search=<?= urlencode($search) ?>&sort_by=<?= h($sort_by) ?>&sort_dir=<?= h($sort_dir) ?><?= $userQuery ?>&page=<?= $page + 1 ?>"><i class="fa-solid fa-chevron-right"></i></a>
                </li>
            </ul>
        </nav>
    <?php endif; ?>
</div>
