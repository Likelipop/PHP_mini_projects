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
        <?php $success = flash_get('success'); if ($success): ?>
            <div class="alert alert-success"><?= h($success) ?></div>
        <?php endif; ?>
        <?php $error = flash_get('error'); if ($error): ?>
            <div class="alert alert-error"><?= h($error) ?></div>
        <?php endif; ?>

        <div class="auth-container">
            <div class="auth-card">
                <h2>Login</h2>
                
                <?php $errors = flash_get('errors', []); $old = flash_get('old', []); ?>

                <?php if (!empty($errors['_global'])): ?>
                    <div class="alert alert-error">
                        <?= h($errors['_global']) ?>
                    </div>
                <?php endif; ?>

                <form action="/login" method="POST" class="form-card">
                    <div class="form-group">
                        <label for="email">Email:</label>
                        <input type="email" id="email" name="email" value="<?= h($old['email'] ?? '') ?>" autofocus>
                        <?php if (!empty($errors['email'])): ?><div class="error-text" style="color:red;font-size:0.9em;margin-top:4px;"><?= h($errors['email']) ?></div><?php endif; ?>
                    </div>
                    <div class="form-group">
                        <label for="password">Password:</label>
                        <input type="password" id="password" name="password">
                        <?php if (!empty($errors['password'])): ?><div class="error-text" style="color:red;font-size:0.9em;margin-top:4px;"><?= h($errors['password']) ?></div><?php endif; ?>
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
