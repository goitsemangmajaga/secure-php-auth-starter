<?php
declare(strict_types=1);

require dirname(__DIR__) . '/src/bootstrap.php';

if ($auth->check()) {
    redirect('/dashboard.php');
}

$error = flash('error');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!$csrf->validate($_POST['csrf_token'] ?? null)) {
        http_response_code(419);
        $error = 'Your session expired. Refresh the page and try again.';
    } else {
        $email = filter_var(trim((string) ($_POST['email'] ?? '')), FILTER_VALIDATE_EMAIL);
        $password = (string) ($_POST['password'] ?? '');

        if (!$email || $password === '') {
            $error = 'Enter a valid email address and password.';
        } else {
            $key = $rateLimiter->key($email);
            if ($rateLimiter->isBlocked($key)) {
                $error = 'Too many attempts. Please try again later.';
            } elseif ($auth->attempt($email, $password)) {
                $rateLimiter->clear($key);
                redirect('/dashboard.php');
            } else {
                $rateLimiter->recordFailure($key);
                $error = 'The email or password is incorrect.';
            }
        }
    }
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Secure Sign In</title>
    <link rel="stylesheet" href="/assets/css/app.css">
</head>
<body>
<main class="auth-shell">
    <section class="auth-card" aria-labelledby="login-title">
        <span class="eyebrow">SECURE PHP AUTH</span>
        <h1 id="login-title">Welcome back</h1>
        <p class="muted">Sign in to access the protected dashboard.</p>

        <?php if ($error): ?>
            <div class="alert" role="alert"><?= e($error) ?></div>
        <?php endif; ?>

        <form method="post" action="/index.php" autocomplete="on">
            <?= $csrf->field() ?>
            <label for="email">Email address</label>
            <input id="email" name="email" type="email" maxlength="190" required autocomplete="email">

            <label for="password">Password</label>
            <input id="password" name="password" type="password" required autocomplete="current-password">

            <button type="submit">Sign in securely</button>
        </form>
    </section>
</main>
</body>
</html>

