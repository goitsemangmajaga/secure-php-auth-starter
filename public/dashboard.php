<?php
declare(strict_types=1);

require dirname(__DIR__) . '/src/bootstrap.php';
$auth->requireLogin();
$user = $auth->user();
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Protected Dashboard</title>
    <link rel="stylesheet" href="/assets/css/app.css">
</head>
<body>
<main class="dashboard-shell">
    <nav class="topbar">
        <strong>Secure PHP Auth</strong>
        <form method="post" action="/logout.php">
            <?= $csrf->field() ?>
            <button class="button-secondary" type="submit">Sign out</button>
        </form>
    </nav>

    <section class="dashboard-card">
        <span class="eyebrow">PROTECTED AREA</span>
        <h1>Hello, <?= e($user['name']) ?></h1>
        <p>Your authentication session is active and protected.</p>
        <dl>
            <div><dt>Email</dt><dd><?= e($user['email']) ?></dd></div>
            <div><dt>Role</dt><dd><?= e(ucfirst($user['role'])) ?></dd></div>
        </dl>
    </section>
</main>
</body>
</html>

