<?php

require_once __DIR__ . '/auth.php';

if (($_SESSION['role'] ?? '') === $portalRole) {
    header('Location: ' . BASE_URL . '/' . $portalRole . '.php');
    exit;
}

$error = $_SESSION['login_error'] ?? '';
unset($_SESSION['login_error']);
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($heading) ?> · Minar Mentorship</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/style.css?v=<?= filemtime(__DIR__ . '/assets/style.css') ?>">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/login.css?v=<?= filemtime(__DIR__ . '/assets/login.css') ?>">
</head>
<body class="landing <?= strtolower(e($portalRole)) ?>-portal">
    <section class="login-shell">
        <a class="brand" href="<?= BASE_URL ?>/index.php"><span>✦</span> Minar</a>
        <div class="login-card">
            <p class="eyebrow"><?= strtoupper($portalRole) ?> PORTAL</p>
            <h1><?= e($heading) ?></h1>
            <p class="intro"><?= e($description) ?></p>
            <?php if ($error): ?><div class="notice warning"><?= e($error) ?></div><?php endif; ?>
            <form method="post" action="<?= BASE_URL ?>/login.php" class="stack">
                <?= csrf_field() ?>
                <input type="hidden" name="role" value="<?= e($portalRole) ?>">
                <label>Username<input required name="username" placeholder="Enter your username"></label>
                <label>Password<input required type="password" name="password" placeholder="Enter your password"></label>
                <button>Sign in as <?= e(ucfirst($portalRole)) ?></button>
            </form>
            <p class="login-help">Use an account created by your portal administrator. For the bundled database, see the password reset instructions in the README.</p>
        </div>
    </section>
</body>
</html>
