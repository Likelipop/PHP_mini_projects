<div class="container py-4">
    <div class="row g-4">
        <!-- Sidebar categories -->
        <aside class="col-lg-3">
            <div class="card shadow-sm border border-secondary-subtle">
                <div class="card-header bg-body-tertiary fw-bold py-3">
                    <i class="fa-solid fa-list text-primary me-2"></i> Categories
                </div>
                <div class="list-group list-group-flush small" x-data="{ currentCat: 'all' }">
                    <a href="/studyflows" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center" :class="currentCat === 'all' ? 'active' : ''" @click="currentCat = 'all'">
                        <span><i class="fa-solid fa-layer-group me-2"></i> Tất cả</span>
                    </a>
                    <a href="/studyflows?search=artificial-intelligence" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center" :class="currentCat === 'ai' ? 'active' : ''" @click="currentCat = 'ai'">
                        <span><i class="fa-solid fa-brain me-2"></i> Artificial Intelligence</span>
                    </a>
                    <a href="/studyflows?search=web-development" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center" :class="currentCat === 'web' ? 'active' : ''" @click="currentCat = 'web'">
                        <span><i class="fa-solid fa-globe me-2"></i> Web Development</span>
                    </a>
                    <a href="/studyflows?search=database" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center" :class="currentCat === 'db' ? 'active' : ''" @click="currentCat = 'db'">
                        <span><i class="fa-solid fa-database me-2"></i> Database</span>
                    </a>
                    <a href="/studyflows?search=mathematics" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center" :class="currentCat === 'math' ? 'active' : ''" @click="currentCat = 'math'">
                        <span><i class="fa-solid fa-calculator me-2"></i> Mathematics</span>
                    </a>
                </div>
            </div>
        </aside>

        <!-- Main section -->
        <main class="col-lg-9">
            <div class="card shadow-sm border border-secondary-subtle mb-4">
                <div class="card-body">
                    <form action="/studyflows" method="GET" class="row g-2">
                        <div class="col-md-5">
                            <div class="input-group input-group-sm">
                                <span class="input-group-text bg-body-tertiary border-end-0"><i class="fa-solid fa-magnifying-glass"></i></span>
                                <input type="text" name="search" class="form-control bg-body-tertiary border-start-0" placeholder="Search studyflow title..." value="<?= h($search) ?>">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <select name="sort_by" class="form-select form-select-sm bg-body-tertiary">
                                <option value="created_at" <?= $sort_by === 'created_at' ? 'selected' : '' ?>>Mới nhất</option>
                                <option value="title" <?= $sort_by === 'title' ? 'selected' : '' ?>>Tiêu đề</option>
                                <option value="slug" <?= $sort_by === 'slug' ? 'selected' : '' ?>>Đường dẫn slug</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <select name="sort_dir" class="form-select form-select-sm bg-body-tertiary">
                                <option value="desc" <?= $sort_dir === 'desc' ? 'selected' : '' ?>>Giảm dần</option>
                                <option value="asc" <?= $sort_dir === 'asc' ? 'selected' : '' ?>>Tăng dần</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-sm btn-primary w-100"><i class="fa-solid fa-filter me-1"></i> Lọc</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Grid output -->
            <div class="row row-cols-1 row-cols-md-2 row-cols-xl-3 g-3 studyflow-grid mb-4">
                <?php if (!empty($flows)): ?>
                    <?php foreach ($flows as $flow): ?>
                        <div class="col">
                            <div class="card h-100 shadow-sm border border-secondary-subtle">
                                <div class="card-body d-flex flex-column">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span class="badge bg-light text-body border font-monospace xsmall">/<?= h($flow['slug']) ?></span>
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
                                        <?= h(mb_strimwidth($flow['description'] ?? '', 0, 90, '...')) ?>
                                    </p>
                                    <div class="d-flex justify-content-between align-items-center mt-3 pt-2 border-top border-light-subtle text-muted small">
                                        <span><i class="fa-solid fa-user me-1"></i> <?= h($flow['username'] ?? 'owner') ?></span>
                                        <span><i class="fa-regular fa-clock me-1"></i> <?= date('d/m/Y', strtotime($flow['created_at'])) ?></span>
                                    </div>
                                </div>
                                <div class="card-footer bg-transparent border-0 pt-0 pb-3 px-3">
                                    <a href="/studyflow/<?= h($flow['slug']) ?>" class="btn btn-xs btn-outline-primary w-100">Vào Workspace <i class="fa-solid fa-chevron-right ms-1"></i></a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="col-12 text-center py-5 text-muted card-full">
                        <i class="fa-solid fa-boxes-packing fs-1 mb-2"></i>
                        <p class="mb-0">Không tìm thấy StudyFlow nào phù hợp.</p>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Pagination Bootstrap structure -->
            <?php if ($total_pages > 1): ?>
                <nav aria-label="Page navigation" class="d-flex justify-content-center">
                    <ul class="pagination pagination-sm">
                        <!-- Prev link -->
                        <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                            <a class="page-link" href="/studyflows?search=<?= urlencode($search) ?>&sort_by=<?= h($sort_by) ?>&sort_dir=<?= h($sort_dir) ?>&page=<?= $page - 1 ?>"><i class="fa-solid fa-chevron-left"></i></a>
                        </li>

                        <!-- Page numbers -->
                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                            <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                                <a class="page-link" href="/studyflows?search=<?= urlencode($search) ?>&sort_by=<?= h($sort_by) ?>&sort_dir=<?= h($sort_dir) ?>&page=<?= $i ?>"><?= $i ?></a>
                            </li>
                        <?php endfor; ?>

                        <!-- Next link -->
                        <li class="page-item <?= $page >= $total_pages ? 'disabled' : '' ?>">
                            <a class="page-link" href="/studyflows?search=<?= urlencode($search) ?>&sort_by=<?= h($sort_by) ?>&sort_dir=<?= h($sort_dir) ?>&page=<?= $page + 1 ?>"><i class="fa-solid fa-chevron-right"></i></a>
                        </li>
                    </ul>
                </nav>
            <?php endif; ?>
        </main>
    </div>
</div>
