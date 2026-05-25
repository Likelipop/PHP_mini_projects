<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Student Learning Resource Hub</title>
    <link rel="stylesheet" href="/assets/style.css">
</head>
<body>
    <header>
        <h1>Student Learning Resource Hub</h1>
        <nav>
            <a href="/">Home</a>
            <a href="/resources">Resources</a>
        </nav>
    </header>
    <main>
        <div class="auth-container">
            <div class="auth-card">
                <h2>Login</h2>
                
                <?php if (!empty($error)): ?>
                    <div class="alert alert-error">
                        <?= htmlspecialchars(urldecode($error)) ?>
                    </div>
                <?php endif; ?>

                <form action="/login" method="POST" class="form-card">
                    <div class="form-group">
                        <label for="email">Email:</label>
                        <input type="email" id="email" name="email" required autofocus>
                    </div>
                    <div class="form-group">
                        <label for="password">Password:</label>
                        <input type="password" id="password" name="password" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Login</button>
                </form>

                <p class="auth-link">
                    Don't have an account? <a href="/signup">Sign up here</a>
                </p>
            </div>
        </div>
    </main>
</body>
</html>
