<nav class="navbar navbar-expand-lg border-bottom px-3 py-2 sticky-top" style="background-color: var(--nav-bg);">
    <div class="container-fluid">
        <!-- Logo -->
        <a class="navbar-brand d-flex align-items-center gap-2 fw-bold text-body" href="/">
            <i class="fa-solid fa-square-share-nodes text-primary fs-3"></i>
            <span style="font-family: var(--font-heading);">StudyFlow<span class="text-primary">Hub</span></span>
        </a>

        <!-- Hamburger toggle -->
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <!-- Navbar content -->
        <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link text-body-secondary <?= $_SERVER['REQUEST_URI'] === '/studyflows' ? 'active fw-semibold' : '' ?>" href="/studyflows">
                        <i class="fa-solid fa-compass me-1"></i> Explore
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-body-secondary <?= str_contains($_SERVER['REQUEST_URI'], 'trending') ? 'active fw-semibold' : '' ?>" href="/studyflows?sort_by=created_at&sort_dir=desc">
                        <i class="fa-solid fa-fire me-1"></i> Trending
                    </a>
                </li>
                <?php if (is_logged_in()): ?>
                    <li class="nav-item">
                        <a class="nav-link text-body-secondary" href="/studyflows?user_id=<?= $_SESSION['user_id'] ?>">
                            <i class="fa-solid fa-bookmark me-1"></i> My StudyFlows
                        </a>
                    </li>
                <?php endif; ?>
            </ul>

            <!-- Search box dropdown -->
            <div class="position-relative me-3 mb-2 mb-lg-0" style="min-width: 280px;">
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-body-tertiary border-end-0"><i class="fa-solid fa-magnifying-glass text-muted"></i></span>
                    <input type="text" class="form-control bg-body-tertiary border-start-0" placeholder="Search StudyFlow, tags..." 
                           hx-get="/studyflows" hx-trigger="keyup changed delay:500ms" hx-target=".studyflow-grid" hx-select=".studyflow-grid">
                </div>
            </div>

            <!-- Right Alignments -->
            <div class="d-flex align-items-center gap-3">
                <!-- Theme Switch -->
                <button class="btn btn-link nav-link p-0 border-0" @click="toggleTheme($store.theme)" title="Toggle Theme">
                    <i class="fa-solid fs-5" :class="theme === 'dark' ? 'fa-sun text-warning' : 'fa-moon text-primary'"></i>
                </button>

                <!-- Notifications Bell -->
                <?php if (is_logged_in()): ?>
                    <div class="dropdown">
                        <button class="btn btn-link nav-link p-0 position-relative" id="notificationsDropdown" data-bs-toggle="dropdown" aria-expanded="false"
                                hx-get="/api/notifications" hx-trigger="load, every 30s" hx-swap="innerHTML">
                            <i class="fa-regular fa-bell fs-5 text-body"></i>
                            <span class="position-absolute top-0 start-100 translate-middle p-1 bg-danger border border-light rounded-circle" id="notif-badge" style="display: none;"></span>
                        </button>
                        <div class="dropdown-menu dropdown-menu-end p-0 shadow-lg" aria-labelledby="notificationsDropdown" style="width: 320px; max-height: 400px; overflow-y: auto;">
                            <div class="p-2 border-bottom fw-bold d-flex justify-content-between align-items-center bg-body-tertiary">
                                <span class="small">Thông báo gần đây</span>
                                <i class="fa-solid fa-check-double text-muted small cursor-pointer" title="Đánh dấu đọc tất cả"></i>
                            </div>
                            <div class="list-group list-group-flush small" id="notifications-list">
                                <div class="list-group-item text-center text-muted py-3">Không có thông báo mới</div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- User profile/avatar dropdown -->
                <?php if (is_logged_in()): ?>
                    <div class="dropdown">
                        <a href="#" class="d-flex align-items-center text-decoration-none dropdown-toggle text-body" id="navbarUserDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                            <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center fw-bold me-1" style="width: 32px; height: 32px; font-size: 0.9rem;">
                                <?= strtoupper(substr($_SESSION['username'], 0, 1)) ?>
                            </div>
                            <span class="d-none d-md-inline ms-1 small"><?= h($_SESSION['username']) ?></span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end shadow-sm" aria-labelledby="navbarUserDropdown">
                            <li><a class="dropdown-item small" href="/profile"><i class="fa-solid fa-user-gear me-2 text-muted"></i> Trang cá nhân</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form action="/logout" method="POST" class="px-3 py-1">
                                    <?= csrf_field() ?>
                                    <button type="submit" class="btn btn-sm btn-danger w-100"><i class="fa-solid fa-right-from-bracket me-1"></i> Đăng xuất</button>
                                </form>
                            </li>
                        </ul>
                    </div>
                <?php else: ?>
                    <div class="d-flex gap-2">
                        <a href="/login" class="btn btn-sm btn-outline-secondary">Đăng nhập</a>
                        <a href="/register" class="btn btn-sm btn-primary">Đăng ký</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>
