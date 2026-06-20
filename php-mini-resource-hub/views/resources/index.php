<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resources - Student Learning Resource Hub</title>
    <link rel="stylesheet" href="/assets/style.css">
    <style>
        .inline-form { display: inline; }
        .link-button { 
            background: none; border: none; color: white; cursor: pointer; 
            font: inherit; padding: 0.5rem 1rem; text-decoration: none; 
            font-weight: 500; border-radius: 4px;
        }
        .link-button:hover { background: rgba(255,255,255,0.1); }
    </style>
</head>
<body>
    <?php
        $isLoggedIn = is_logged_in();
        $userName = $isLoggedIn ? h($_SESSION['user_name']) : '';
    ?>
    <header>
        <h1>📚 Resources</h1>
        <nav>
            <a href="/">Home</a>
            <a href="/resources">Resources</a>
            <?php if ($isLoggedIn): ?>
                <a href="/resources/create" class="btn btn-secondary">+ Add Resource</a>
                <span class="nav-user">Hi, <?= $userName ?></span>
                <form method="post" action="/logout" class="inline-form">
                    <button type="submit" class="link-button">Logout</button>
                </form>
            <?php else: ?>
                <a href="/login">Login</a>
                <a href="/signup" class="nav-signup">Sign Up</a>
            <?php endif; ?>
        </nav>
    </header>
    <main>
        <?php $success = flash_get('success'); if ($success): ?>
            <div class="alert alert-success"><?= h($success) ?></div>
        <?php endif; ?>
        <?php $error = flash_get('error'); if ($error): ?>
            <div class="alert alert-error"><?= h($error) ?></div>
        <?php endif; ?>

        <?php if (empty($resources)): ?>
            <div style="text-align: center; padding: 3rem 1rem;">
                <p style="font-size: 1.1rem; color: #666;">No resources shared yet.</p>
                <?php if ($isLoggedIn): ?>
                    <p><a href="/resources/create" class="btn btn-primary">Be the first to share!</a></p>
                <?php else: ?>
                    <p><a href="/signup" class="btn btn-primary">Sign up to share resources</a></p>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <div class="resource-list">
                <?php foreach ($resources as $resource): ?>
                    <div class="resource-card">
                        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1rem;">
                            <div>
                                <h3><?= h($resource['title']) ?></h3>
                                <p style="margin: 0.5rem 0 0 0; font-size: 0.9rem; color: #999;">
                                    <?php if (!empty($resource['author'])): ?>
                                        By <strong><?= h($resource['author']) ?></strong> • 
                                    <?php endif; ?>
                                    <?= date('M d, Y', strtotime($resource['created_at'])) ?>
                                </p>
                            </div>
                        </div>
                        
                        <div class="markdown-body">
                            <?= $resource['html_recommendation'] ?? '<p style="color: #999;">No description provided</p>' ?>
                        </div>
                        
                        <?php if (!empty($resource['download_url'])): ?>
                            <a href="<?= h($resource['download_url']) ?>" class="btn btn-primary" target="_blank" rel="noopener noreferrer" style="margin-top: 1rem;">
                                ⬇️ Download File
                            </a>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </main>

    <footer>
        <p>&copy; 2026 Student Learning Resource Hub. All rights reserved.</p>
    </footer>
</body>
</html>
