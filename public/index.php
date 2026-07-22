<?php
declare(strict_types=1);

require dirname(__DIR__) . '/src/bootstrap.php';

if ($auth->check()) {
    redirect('dashboard.php');
}

$error = flash('error');
$remainingSeconds = 0;
$emailValue = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!$csrf->validate($_POST['csrf_token'] ?? null)) {
        http_response_code(419);
        $error = 'Your session expired. Refresh the page and try again.';
    } else {
        $emailValue = trim((string) ($_POST['email'] ?? ''));
        $email = filter_var($emailValue, FILTER_VALIDATE_EMAIL);
        $password = (string) ($_POST['password'] ?? '');

        if (!$email || $password === '') {
            $error = 'Enter a valid email address and password.';
        } else {
            $key = $rateLimiter->key($email);

            if ($rateLimiter->isBlocked($key)) {
                $remainingSeconds = $rateLimiter->remainingSeconds($key);
            } elseif ($auth->attempt($email, $password)) {
                $rateLimiter->clear($key);
                redirect('dashboard.php');
            } else {
                $rateLimiter->recordFailure($key);
                $remainingSeconds = $rateLimiter->remainingSeconds($key);

                if ($remainingSeconds === 0) {
                    $error = 'The email or password is incorrect.';
                }
            }
        }
    }
}

$isLocked = $remainingSeconds > 0;
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1"
    >

    <title>MJG Secure Sign In</title>

    <link
        rel="stylesheet"
        href="assets/css/app.css"
    >

    <script
        src="assets/js/lockout.js"
        defer
    ></script>
</head>

<body>

<main class="auth-shell">

    <section
        class="auth-card"
        aria-labelledby="login-title"
    >

        <span class="eyebrow">
            SECURE PHP AUTH
        </span>

        <h1 id="login-title">
            Welcome back
        </h1>

        <p class="muted">
            Sign in to access the protected dashboard.
        </p>

        <?php if ($isLocked): ?>

            <div
                class="alert"
                id="lockout-alert"
                role="alert"
                data-remaining-seconds="<?= $remainingSeconds ?>"
            >
                Too many attempts. Try again in
                <strong id="lockout-countdown">00:00</strong>.
            </div>

        <?php elseif ($error): ?>

            <div
                class="alert"
                role="alert"
            >
                <?= e($error) ?>
            </div>

        <?php endif; ?>

        <form
            method="post"
            action="index.php"
            autocomplete="on"
            id="login-form"
        >

            <?= $csrf->field() ?>

            <label for="email">
                Email address
            </label>

            <input
                id="email"
                name="email"
                type="email"
                maxlength="190"
                value="<?= e($emailValue) ?>"
                required
                autocomplete="email"
                <?= $isLocked ? 'disabled' : '' ?>
            >

            <label for="password">
                Password
            </label>

            <input
                id="password"
                name="password"
                type="password"
                required
                autocomplete="current-password"
                <?= $isLocked ? 'disabled' : '' ?>
            >

            <button
                id="login-button"
                type="submit"
                <?= $isLocked ? 'disabled' : '' ?>
            >
                Sign in securely
            </button>

        </form>

        <p class="developer-credit">
            Developed by Goitsemang Majaga
        </p>

    </section>

</main>

</body>
</html>