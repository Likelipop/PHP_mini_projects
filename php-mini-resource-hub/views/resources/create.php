<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Resource - Student Learning Resource Hub</title>
    <link rel="stylesheet" href="/assets/style.css">
    <style>
        .honeypot { display: none; }
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
        
        if (!$isLoggedIn) {
            redirect('/login');
        }
    ?>
    <header>
        <h1>📤 Share a Resource</h1>
        <nav>
            <a href="/">Home</a>
            <a href="/resources">Resources</a>
            <span class="nav-user">Hi, <?= $userName ?></span>
            <form method="post" action="/logout" class="inline-form">
                <button type="submit" class="link-button">Logout</button>
            </form>
        </nav>
    </header>
    <main>
        <?php $success = flash_get('success'); if ($success): ?>
            <div class="alert alert-success"><?= h($success) ?></div>
        <?php endif; ?>
        <?php $error = flash_get('error'); if ($error): ?>
            <div class="alert alert-error"><?= h($error) ?></div>
        <?php endif; ?>

        <?php $errors = flash_get('errors', []); $old = flash_get('old', []); ?>

        <?php if (!empty($errors['_global'])): ?>
            <div class="alert alert-error">
                <?= h($errors['_global']) ?>
            </div>
        <?php endif; ?>

        <div style="max-width: 700px; margin: 0 auto;">
            <form action="/resources" method="POST" enctype="multipart/form-data" class="form-card">
                <h2 style="margin-top: 0; margin-bottom: 1.5rem; color: #0056b3;">Share Your Knowledge</h2>
                
                <div class="honeypot">
                    <label>Website</label>
                    <input name="website" tabindex="-1" autocomplete="off">
                </div>

                <div class="form-group">
                    <label for="title">📋 Resource Title *</label>
                    <input type="text" id="title" name="title" value="<?= h($old['title'] ?? '') ?>" placeholder="e.g., Introduction to Web Development">
                    <?php if (!empty($errors['title'])): ?><div class="error-text" style="color:red;font-size:0.9em;margin-top:4px;"><?= h($errors['title']) ?></div><?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="markdown_recommendation">📝 Description & Recommendations</label>
                    <textarea id="markdown_recommendation" name="markdown_recommendation" rows="8" placeholder="Describe the resource and share your thoughts..."><?= h($old['markdown_recommendation'] ?? '') ?></textarea>
                    <small>You can use Markdown for formatting. Leave empty if you only want to share a file.</small>
                    <?php if (!empty($errors['markdown_recommendation'])): ?><div class="error-text" style="color:red;font-size:0.9em;margin-top:4px;"><?= h($errors['markdown_recommendation']) ?></div><?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="file">📎 Attach File (Optional)</label>
                    <input type="file" id="file" name="file">
                    <small>Maximum file size depends on your server configuration.</small>
                </div>

                <div style="display: flex; gap: 1rem;">
                    <button type="submit" class="btn btn-primary" style="flex: 1;">✓ Share Resource</button>
                    <a href="/resources" class="btn btn-secondary" style="flex: 1; text-align: center; text-decoration: none;">← Cancel</a>
                </div>
            </form>

            <div style="margin-top: 2rem; padding: 1.5rem; background: #f0f7ff; border-radius: 8px; border-left: 4px solid #0056b3;">
                <h4 style="margin-top: 0;">💡 Tips for sharing great resources:</h4>
                <ul style="margin: 0.5rem 0;">
                    <li>Be descriptive and clear about what the resource covers</li>
                    <li>Share your personal insights or why you found it helpful</li>
                    <li>Attach relevant files (PDFs, code snippets, etc.)</li>
                    <li>Use proper formatting to make it easy to read</li>
                </ul>
            </div>
        </div>
    </main>

    <footer>
        <p>&copy; 2026 Student Learning Resource Hub. All rights reserved.</p>
    </footer>
</body>
</html>
