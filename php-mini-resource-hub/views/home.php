<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title ?? 'Hub') ?></title>
    <link rel="stylesheet" href="/assets/style.css">
</head>
<body>
    <?php
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $isLoggedIn = isset($_SESSION['user_id']);
        $userName = $isLoggedIn ? htmlspecialchars($_SESSION['user_name']) : '';
    ?>
    <header>
        <h1>Student Learning Resource Hub</h1>
        <nav>
            <a href="/">Home</a>
            <a href="/resources">Resources</a>
            <?php if ($isLoggedIn): ?>
                <span class="nav-user">Hi, <?= $userName ?></span>
                <a href="/logout" class="nav-logout">Logout</a>
            <?php else: ?>
                <a href="/login">Login</a>
                <a href="/signup" class="nav-signup">Sign Up</a>
            <?php endif; ?>
        </nav>
    </header>
    <main>
        <section class="hero">
            <?php if ($isLoggedIn): ?>
                <h2>Welcome back, <?= $userName ?>! 👋</h2>
                <p>Share resources and collaborate with your peers.</p>
                <div class="hero-buttons">
                    <a href="/resources" class="btn btn-primary">View Resources</a>
                    <a href="/resources/create" class="btn btn-secondary">Share Resource</a>
                </div>
            <?php else: ?>
                <h2>Welcome to the Resource Hub!</h2>
                <p>A collaborative platform for sharing files and learning resources with your peers.</p>
                <div class="hero-buttons">
                    <a href="/signup" class="btn btn-primary">Get Started</a>
                    <a href="/login" class="btn btn-secondary">Sign In</a>
                </div>
            <?php endif; ?>
        </section>

        <?php if (isset($_GET['login']) && $_GET['login'] === 'success'): ?>
            <div class="alert alert-success">
                ✓ You have logged in successfully.
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['signup']) && $_GET['signup'] === 'success'): ?>
            <div class="alert alert-success">
                ✓ Account created successfully! Welcome aboard.
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['logout']) && $_GET['logout'] === 'success'): ?>
            <div class="alert alert-success">
                ✓ You have been logged out successfully.
            </div>
        <?php endif; ?>

        <section class="features">
            <h3>Features</h3>
            <div class="features-grid">
                <div class="feature-card">
                    <h4>📚 Share Resources</h4>
                    <p>Upload files and markdown-based recommendations for your peers to learn from.</p>
                </div>
                <div class="feature-card">
                    <h4>🤝 Collaborate</h4>
                    <p>Work together with classmates and build a shared knowledge base.</p>
                </div>
                <div class="feature-card">
                    <h4>☁️ Cloud Storage</h4>
                    <p>Secure cloud storage powered by MinIO for all your important files.</p>
                </div>
                <div class="feature-card">
                    <h4>⚡ Fast & Reliable</h4>
                    <p>Built with PostgreSQL and modern PHP for performance and reliability.</p>
                </div>
            </div>
        </section>
    </main>

    <footer>
        <p>&copy; 2026 Student Learning Resource Hub. All rights reserved.</p>
    </footer>
</body>
</html>
