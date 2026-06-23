<nav class="navbar navbar-expand-lg border-bottom px-3 py-1.5 sticky-top" style="background-color: var(--nav-bg); font-size: 0.9rem;">
    <div class="container-fluid">
        <!-- Logo -->
        <a class="navbar-brand d-flex align-items-center gap-2 fw-bold text-body py-1" href="/">
            <i class="fa-solid fa-square-share-nodes text-primary fs-5"></i>
            <span style="font-family: var(--font-heading); font-size: 0.95rem; letter-spacing: -0.01em;">StudyFlow<span class="text-primary">Hub</span></span>
        </a>

        <!-- Hamburger toggle -->
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon" style="font-size: 0.8rem;"></span>
        </button>

        <!-- Navbar content -->
        <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link text-body-secondary small py-1 <?= $_SERVER['REQUEST_URI'] === '/studyflows' ? 'active fw-semibold' : '' ?>" href="/studyflows">
                        <i class="fa-solid fa-compass me-1"></i> Explore
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-body-secondary small py-1 <?= str_contains($_SERVER['REQUEST_URI'], 'trending') ? 'active fw-semibold' : '' ?>" href="/studyflows?sort_by=created_at&sort_dir=desc">
                        <i class="fa-solid fa-fire me-1"></i> Trending
                    </a>
                </li>
                <?php if (is_logged_in()): ?>
                    <li class="nav-item">
                        <a class="nav-link text-body-secondary small py-1" href="/studyflows?user_id=<?= $_SESSION['user_id'] ?>">
                            <i class="fa-solid fa-bookmark me-1"></i> My StudyFlows
                        </a>
                    </li>
                <?php endif; ?>
            </ul>

            <!-- Search box dropdown -->
            <div class="position-relative me-3 mb-2 mb-lg-0" style="min-width: 240px;">
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-body-tertiary border-end-0"><i class="fa-solid fa-magnifying-glass text-muted" style="font-size: 0.8rem;"></i></span>
                    <input type="text" class="form-control bg-body-tertiary border-start-0" placeholder="Search StudyFlow, tags..." style="font-size: 0.8rem;"
                           hx-get="/studyflows" hx-trigger="keyup changed delay:500ms" hx-target=".studyflow-grid" hx-select=".studyflow-grid">
                </div>
            </div>

            <!-- Right Alignments -->
            <div class="d-flex align-items-center gap-3">
                <!-- Theme Switch -->
                <button class="btn btn-link nav-link p-0 border-0" @click="toggleTheme($store.theme)" title="Toggle Theme">
                    <i class="fa-solid fs-6" :class="theme === 'dark' ? 'fa-sun text-warning' : 'fa-moon text-primary'"></i>
                </button>

                <!-- User profile/avatar dropdown -->
                <?php if (is_logged_in()): ?>
                    <!-- Notification Dropdown -->
                    <div class="dropdown me-1" x-data="{ hasNew: false }" x-init="window.addEventListener('newNotifications', () => hasNew = true)">
                        <button class="btn btn-link nav-link p-0 position-relative" id="navbarNotificationDropdown" data-bs-toggle="dropdown" aria-expanded="false" 
                                hx-get="/api/notifications" hx-trigger="click" hx-target="#notification-list" @click="hasNew = false" title="Thông báo">
                            <i class="fa-regular fa-bell fs-6 text-body"></i>
                            <span class="position-absolute top-0 start-100 translate-middle p-1 bg-danger border border-light rounded-circle" x-show="hasNew">
                                <span class="visually-hidden">New alerts</span>
                            </span>
                        </button>
                        <div class="dropdown-menu dropdown-menu-end shadow-sm p-0 border-secondary-subtle" aria-labelledby="navbarNotificationDropdown" style="width: 280px; font-size: 0.85rem; z-index: 1050;">
                            <div class="dropdown-header py-2 border-bottom bg-body-tertiary fw-bold text-body"><i class="fa-regular fa-bell me-1 text-primary"></i> Thông báo</div>
                            <div id="notification-list" class="list-group list-group-flush minimal-scroll" style="max-height: 250px; overflow-y: auto;">
                                <div class="p-3 text-center text-muted small"><span class="spinner-border spinner-border-sm me-2"></span>Đang tải thông báo...</div>
                            </div>
                        </div>
                    </div>

                    <div class="dropdown">
                        <a href="#" class="d-flex align-items-center text-decoration-none dropdown-toggle text-body" id="navbarUserDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                            <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center fw-bold me-1" style="width: 28px; height: 28px; font-size: 0.8rem;">
                                <?= strtoupper(substr($_SESSION['username'], 0, 1)) ?>
                            </div>
                            <span class="d-none d-md-inline ms-1 small"><?= h($_SESSION['username']) ?></span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end shadow-sm" aria-labelledby="navbarUserDropdown" style="font-size: 0.85rem;">
                            <li><a class="dropdown-item py-1.5" href="/profile"><i class="fa-solid fa-user-gear me-2 text-muted"></i> Trang cá nhân</a></li>
                            <li><hr class="dropdown-divider my-1"></li>
                            <li>
                                <form action="/logout" method="POST" class="px-3 py-1">
                                    <?= csrf_field() ?>
                                    <button type="submit" class="btn btn-xs btn-danger w-100 fw-semibold"><i class="fa-solid fa-right-from-bracket me-1"></i> Đăng xuất</button>
                                </form>
                            </li>
                        </ul>
                    </div>
                <?php else: ?>
                    <div class="d-flex gap-2">
                        <a href="/login" class="btn btn-xs btn-outline-secondary px-3">Đăng nhập</a>
                        <a href="/register" class="btn btn-xs btn-primary px-3">Đăng ký</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>
