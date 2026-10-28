<?php

require_once __DIR__ . '/config.php';

$ready = false;
$error = '';

try {
    $ready = (bool) db()->query('SELECT 1 FROM users LIMIT 1')->fetchColumn();
} catch (Throwable $exception) {
    error_log('Portal database check failed: ' . $exception->getMessage());
    $error = 'The database is unavailable. Check the application configuration and try again.';
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Minar Mentorship Portal</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/style.css">
</head>
<body class="landing">
    <section class="hero">
        <p class="eyebrow">MINAR / MENTORSHIP PORTAL</p>
        <h1>Guidance that keeps<br><em>students moving.</em></h1>
        <p class="intro">One connected space for academic mentoring, course choices, requests, and meetings.</p>

        <?php if (!$ready): ?>
            <div class="notice warning"><strong>Database setup required.</strong> Import <code>database/mentorship_portal.sql</code> into MySQL, then refresh this page. <?= e($error) ?></div>
        <?php endif; ?>

        <div class="portal-grid">
            <a href="<?= BASE_URL ?>/admin/" class="portal-card"><span>01</span><h2>Admin</h2><p>Manage people, courses, requests and mentor allocations.</p><b>Admin login →</b></a>
            <a href="<?= BASE_URL ?>/mentor/" class="portal-card"><span>02</span><h2>Mentor</h2><p>Support assigned students and review their course requests.</p><b>Mentor login →</b></a>
            <a href="<?= BASE_URL ?>/student/" class="portal-card"><span>03</span><h2>Student</h2><p>Select subjects, track approvals, and meet your mentor.</p><b>Student login →</b></a>
        </div>
    </section>
</body>
</html>
