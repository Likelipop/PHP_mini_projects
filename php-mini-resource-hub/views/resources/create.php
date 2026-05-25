<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Resource - Student Learning Resource Hub</title>
    <link rel="stylesheet" href="/assets/style.css">
</head>
<body>
    <?php
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $isLoggedIn = isset($_SESSION['user_id']);
        $userName = $isLoggedIn ? htmlspecialchars($_SESSION['user_name']) : '';
        
        if (!$isLoggedIn) {
            header('Location: /login');
            exit;
        }
    ?>
    <header>
        <h1>📤 Share a Resource</h1>
        <nav>
            <a href="/">Home</a>
            <a href="/resources">Resources</a>
            <span class="nav-user">Hi, <?= $userName ?></span>
            <a href="/logout" class="nav-logout">Logout</a>
        </nav>
    </header>
    <main>
        <div style="max-width: 700px; margin: 0 auto;">
            <form action="/resources" method="POST" enctype="multipart/form-data" class="form-card">
                <h2 style="margin-top: 0; margin-bottom: 1.5rem; color: #0056b3;">Share Your Knowledge</h2>
                
                <div class="form-group">
                    <label for="title">📋 Resource Title *</label>
                    <input type="text" id="title" name="title" required placeholder="e.g., Introduction to Web Development">
                </div>

                <div class="form-group">
                    <label for="markdown_recommendation">📝 Description & Recommendations</label>
                    <textarea id="markdown_recommendation" name="markdown_recommendation" rows="8" placeholder="Describe the resource and share your thoughts...&#10;&#10;Supports Markdown:&#10;# Heading&#10;**bold** *italic* [link](url)&#10;- List item 1&#10;- List item 2"></textarea>
                    <small>You can use Markdown for formatting. Leave empty if you only want to share a file.</small>
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
