<!-- Hero Section -->
<div class="hero-banner text-center py-5 mb-4 position-relative overflow-hidden" style="background: linear-gradient(135deg, var(--bg-card) 0%, rgba(9, 105, 218, 0.08) 100%); border-bottom: 1px solid var(--border-color);">
    <div class="container py-4 position-relative z-1">
        <h1 class="display-4 fw-bold" style="font-family: var(--font-heading);">Cánh Cửa Tri Thức StudyFlow</h1>
        <p class="lead text-body-secondary mx-auto mb-4" style="max-width: 720px;">
            Liên kết tài liệu học tập cùng ghi chú Markdown thành các sơ đồ tags có tính thừa kế. Chia sẻ lộ trình học tập tối ưu cho bạn và cộng đồng.
        </p>
        <div class="d-flex justify-content-center gap-3">
            <a href="/studyflows/create" class="btn btn-primary btn-lg shadow-sm">
                <i class="fa-solid fa-square-plus me-1"></i> Tạo StudyFlow mới
            </a>
            <a href="/studyflows" class="btn btn-outline-secondary btn-lg">
                <i class="fa-solid fa-magnifying-glass me-1"></i> Khám phá
            </a>
        </div>
    </div>
    <!-- Visual background blobs -->
    <div class="position-absolute rounded-circle bg-primary opacity-10 filter-blur" style="width: 250px; height: 250px; top: -50px; left: -50px; filter: blur(60px);"></div>
    <div class="position-absolute rounded-circle bg-info opacity-10 filter-blur" style="width: 300px; height: 300px; bottom: -100px; right: -50px; filter: blur(80px);"></div>
</div>

<div class="container pb-5">
    <div class="row g-4">
        <!-- Main contents (Left 8 cols) -->
        <div class="col-lg-8">
            <!-- Trending section -->
            <div class="mb-4">
                <div class="d-flex align-items-center justify-content-between mb-3 border-bottom pb-2">
                    <h3 class="h4 mb-0 fw-bold"><i class="fa-solid fa-fire text-danger me-2"></i>Trending StudyFlows</h3>
                    <a href="/studyflows" class="text-decoration-none small">Xem tất cả <i class="fa-solid fa-arrow-right"></i></a>
                </div>

                <div class="row row-cols-1 row-cols-md-2 g-3">
                    <?php if (!empty($trending)): ?>
                        <?php foreach ($trending as $flow): ?>
                            <div class="col">
                                <div class="card h-100 shadow-sm border border-secondary-subtle">
                                    <div class="card-body d-flex flex-column">
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle small text-truncate" style="max-width: 80%;">/<?= h($flow['slug']) ?></span>
                                            <?php if ($flow['is_pinned']): ?>
                                                <span class="text-warning small" title="Pinned"><i class="fa-solid fa-thumbtack"></i></span>
                                            <?php endif; ?>
                                        </div>
                                        <h5 class="card-title fw-bold">
                                            <a href="/studyflow/<?= h($flow['slug']) ?>" class="text-decoration-none text-body hover-primary">
                                                <?= h($flow['title']) ?>
                                            </a>
                                        </h5>
                                        <p class="card-text text-body-secondary small flex-grow-1">
                                            <?= h(mb_strimwidth($flow['description'] ?? '', 0, 110, '...')) ?>
                                        </p>
                                        <div class="d-flex justify-content-between align-items-center mt-3 pt-2 border-top border-light-subtle text-muted small">
                                            <span><i class="fa-solid fa-user me-1"></i> <?= h($flow['username'] ?? 'owner') ?></span>
                                            <span><i class="fa-regular fa-clock me-1"></i> <?= date('d/m/Y', strtotime($flow['created_at'])) ?></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="col-12">
                            <div class="text-center p-4 border border-dashed rounded text-muted">
                                <i class="fa-solid fa-layer-group fs-2 mb-2"></i>
                                <p class="mb-0">Chưa có StudyFlow nổi bật nào.</p>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Newest StudyFlows with Infinite Scroll -->
            <div class="mb-4">
                <div class="d-flex align-items-center justify-content-between mb-3 border-bottom pb-2">
                    <h3 class="h4 mb-0 fw-bold"><i class="fa-solid fa-clock text-primary me-2"></i>Mới cập nhật</h3>
                </div>

                <div class="list-group list-group-flush border rounded shadow-sm studyflow-list">
                    <?php if (!empty($newest)): ?>
                        <?php foreach ($newest as $flow): ?>
                            <a href="/studyflow/<?= h($flow['slug']) ?>" class="list-group-item list-group-item-action d-flex align-items-center gap-3 py-3 text-body">
                                <div class="bg-body-secondary p-2 rounded text-primary fs-5">
                                    <i class="fa-solid fa-folder-open"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="d-flex justify-content-between">
                                        <h6 class="mb-0 fw-bold"><?= h($flow['title']) ?></h6>
                                        <small class="text-muted"><?= date('d/m/Y', strtotime($flow['created_at'])) ?></small>
                                    </div>
                                    <p class="mb-0 text-muted small mt-1"><?= h(mb_strimwidth($flow['description'] ?? '', 0, 140, '...')) ?></p>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="p-3 text-center text-muted small">Không tìm thấy bản ghi nào.</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Sidebar options (Right 4 cols) -->
        <div class="col-lg-4">
            <!-- Popular tags -->
            <div class="card shadow-sm border border-secondary-subtle mb-4">
                <div class="card-header bg-body-tertiary fw-bold py-3">
                    <i class="fa-solid fa-tags text-primary me-2"></i> Chủ đề phổ biến
                </div>
                <div class="card-body">
                    <div class="d-flex flex-wrap gap-2">
                        <?php foreach ($popularTags as $tag): ?>
                            <a href="/studyflows?search=<?= urlencode($tag['prefix']) ?>" class="btn btn-sm btn-secondary d-flex align-items-center gap-1">
                                <span class="text-primary font-monospace">#</span>
                                <span class="fw-semibold"><?= h($tag['name']) ?></span>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- Recent activities -->
            <div class="card shadow-sm border border-secondary-subtle mb-4">
                <div class="card-header bg-body-tertiary fw-bold py-3">
                    <i class="fa-solid fa-bolt text-warning me-2"></i> Hoạt động gần đây
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush small">
                        <div class="list-group-item d-flex align-items-start gap-2 py-3 border-light-subtle">
                            <span class="text-success mt-0.5"><i class="fa-solid fa-circle-plus"></i></span>
                            <div>
                                <span><strong>Nguyễn Văn A</strong> đã tải lên <code>lecture1.pdf</code></span>
                                <div class="text-muted xsmall mt-0.5">3 giờ trước</div>
                            </div>
                        </div>
                        <div class="list-group-item d-flex align-items-start gap-2 py-3 border-light-subtle">
                            <span class="text-primary mt-0.5"><i class="fa-solid fa-pen"></i></span>
                            <div>
                                <span><strong>Trần Thị B</strong> đã cập nhật ghi chú <code>CNN Overview</code></span>
                                <div class="text-muted xsmall mt-0.5">5 giờ trước</div>
                            </div>
                        </div>
                        <div class="list-group-item d-flex align-items-start gap-2 py-3 border-light-subtle">
                            <span class="text-info mt-0.5"><i class="fa-solid fa-link"></i></span>
                            <div>
                                <span><strong>Lê Văn C</strong> đã tạo tag fragment trên <code>Transformer.pdf</code></span>
                                <div class="text-muted xsmall mt-0.5">1 ngày trước</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
