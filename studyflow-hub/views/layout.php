<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($title) ? h($title) . ' - StudyFlow Hub' : 'StudyFlow Hub - AI-powered Learning Workspace' ?></title>
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Outfit:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- FontAwesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Main stylesheet -->
    <link rel="stylesheet" href="/css/style.css">
</head>
<body>
    <header class="app-header">
        <div class="header-container">
            <a href="/" class="app-logo">
                <i class="fa-solid fa-brain logo-icon"></i>
                <span class="logo-text">StudyFlow<span class="logo-accent">Hub</span></span>
            </a>
            
            <nav class="app-nav">
                <a href="/" class="nav-link <?= $_SERVER['REQUEST_URI'] === '/' ? 'active' : '' ?>">
                    <i class="fa-solid fa-house"></i> Trang chủ
                </a>
                <a href="/studyflows" class="nav-link <?= str_starts_with($_SERVER['REQUEST_URI'], '/studyflows') ? 'active' : '' ?>">
                    <i class="fa-solid fa-compass"></i> Khám phá
                </a>
            </nav>

            <div class="header-auth">
                <?php if (is_logged_in()): ?>
                    <span class="user-badge">
                        <i class="fa-solid fa-circle-user"></i> <?= h($_SESSION['username']) ?>
                    </span>
                    <form action="/logout" method="POST" class="logout-form inline-form">
                        <?= csrf_field() ?>
                        <button type="submit" class="btn btn-sm btn-outline">
                            <i class="fa-solid fa-right-from-bracket"></i> Đăng xuất
                        </button>
                    </form>
                <?php else: ?>
                    <a href="/login" class="btn btn-sm btn-outline">Đăng nhập</a>
                    <a href="/register" class="btn btn-sm btn-primary">Đăng ký</a>
                <?php endif; ?>
            </div>
        </div>
    </header>

    <main class="app-content">
        <div class="content-container">
            <!-- Flash Notifications -->
            <?php if (isset($success) && $success): ?>
                <div class="alert alert-success">
                    <i class="fa-solid fa-circle-check alert-icon"></i>
                    <div class="alert-content"><?= h($success) ?></div>
                </div>
            <?php endif; ?>

            <?php if (isset($error) && $error): ?>
                <div class="alert alert-danger">
                    <i class="fa-solid fa-circle-exclamation alert-icon"></i>
                    <div class="alert-content"><?= h($error) ?></div>
                </div>
            <?php endif; ?>

            <!-- Page-specific content injection -->
            <?php require $viewPath; ?>
        </div>
    </main>

    <footer class="app-footer">
        <div class="footer-container">
            <p>&copy; 2026 StudyFlow Hub. Được xây dựng với triết lý Asset-centric & AI Knowledge Graph.</p>
        </div>
    </footer>

    <!-- Main Javascript -->
    <script src="/js/app.js"></script>
</body>
</html>
