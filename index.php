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
    <meta name="theme-color" content="#24352e">
    <meta name="description" content="Mentor Bond brings students, mentors, and administrators together to plan courses, share guidance, and make progress visible.">
    <title>Mentor Bond | A clearer path forward</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/style.css?v=<?= filemtime(__DIR__ . '/assets/style.css') ?>">
</head>
<body id="top" class="landing bond-landing">
    <header class="bond-nav">
        <a class="bond-logo" href="<?= BASE_URL ?>/index.php" aria-label="MentorBond home"><img class="bond-mark" src="<?= BASE_URL ?>/assets/mentor-bond-emblem.png" alt=""> Mentor<span>Bond</span></a>
        <nav aria-label="Main navigation">
            <a href="#how-it-works">How it works</a>
            <a href="#community">Our community</a>
            <a class="bond-nav-cta" href="#portals">Enter the portal <span aria-hidden="true">↗</span></a>
        </nav>
    </header>

    <main class="bond-main">
        <?php if (!$ready): ?>
            <div class="notice warning bond-db-notice"><strong>Portal setup needed.</strong> Import <code>database/mentorship_portal.sql</code> into MySQL, then refresh this page. <?= e($error) ?></div>
        <?php endif; ?>

        <section class="bond-hero" aria-labelledby="hero-title">
            <div class="bond-hero-copy">
                <p class="bond-kicker"><span></span> A little guidance can change the whole journey</p>
                <h1 id="hero-title">Good things grow<br>with <em>someone beside you.</em></h1>
                <p class="bond-lede">A thoughtful space for students and mentors to turn questions into plans, and plans into progress.</p>
                <div class="bond-hero-actions">
                    <a class="bond-button" href="#portals">Find your way in <span aria-hidden="true">↓</span></a>
                    <a class="bond-text-link" href="#how-it-works">Discover the journey <span aria-hidden="true">↘</span></a>
                </div>
                <div class="bond-proof"><div class="bond-proof-avatars" aria-hidden="true"><i>S</i><i>M</i><i>A</i></div><p><strong>Three perspectives.</strong><br>One connected learning journey.</p></div>
            </div>

            <div class="bond-visual" aria-label="A student and mentor connected through course planning and meetings">
                <div class="bond-orbit bond-orbit-one"></div><div class="bond-orbit bond-orbit-two"></div>
                <div class="bond-visual-note"><span class="bond-note-icon">✦</span><div><small>THE MENTORING LOOP</small><strong>Listen. Plan. Grow.</strong></div></div>
                <div class="bond-node bond-node-student"><span class="bond-node-icon">S</span><div><small>STUDENT</small><strong>Your next step</strong></div><span class="bond-node-spark">✳</span></div>
                <div class="bond-node bond-node-mentor"><span class="bond-node-icon">M</span><div><small>MENTOR</small><strong>In your corner</strong></div><span class="bond-node-spark">↗</span></div>
                <div class="bond-node bond-node-plan"><span class="bond-node-icon">↗</span><div><small>SHARED PLAN</small><strong>Progress, together</strong></div><span class="bond-node-spark">✓</span></div>
                <span class="bond-float bond-float-one">COURSE PICKS <b>✓</b></span>
                <span class="bond-float bond-float-two">A MEETING, MADE <b>♡</b></span>
                <div class="bond-visual-caption"><span class="bond-caption-dot"></span> A clear path, shaped together</div>
            </div>
        </section>

        <section class="bond-principles" id="how-it-works" aria-label="How Mentor Bond supports learning">
            <article><span class="bond-step">01 / CONNECT</span><h2>Start with a person</h2><p>Students find their mentor and have one place to ask, share, and stay in touch.</p></article>
            <article><span class="bond-step">02 / CHOOSE</span><h2>Make a plan</h2><p>Explore courses, send choices for review, and keep every decision easy to follow.</p></article>
            <article><span class="bond-step">03 / GROW</span><h2>Keep momentum</h2><p>Coordinate meetings, follow announcements, and see the next step take shape.</p></article>
        </section>

        <section class="bond-portals" id="portals" aria-labelledby="portal-title">
            <div class="bond-section-heading">
                <div><p class="bond-kicker">YOUR SPACE IS READY</p><h2 id="portal-title">Choose your side of the bond.</h2></div>
                <p>Each role has the tools and view that fit its part in the journey.</p>
            </div>
            <div class="bond-portal-grid" id="community">
                <a href="<?= BASE_URL ?>/student/" class="bond-portal-card bond-student-card"><span class="bond-card-index">01</span><span class="bond-card-symbol">↗</span><h3>Student</h3><p>Shape your course choices, connect with your mentor, and keep track of meetings and progress.</p><b>Step into your space <span aria-hidden="true">→</span></b></a>
                <a href="<?= BASE_URL ?>/mentor/" class="bond-portal-card bond-mentor-card"><span class="bond-card-index">02</span><span class="bond-card-symbol">✳</span><h3>Mentor</h3><p>Support your assigned students, review their requests, share updates, and make time to meet.</p><b>Guide the journey <span aria-hidden="true">→</span></b></a>
                <a href="<?= BASE_URL ?>/admin/" class="bond-portal-card bond-admin-card"><span class="bond-card-index">03</span><span class="bond-card-symbol">⌘</span><h3>Administrator</h3><p>Bring the community together by managing accounts, courses, assignments, and requests.</p><b>Shape the community <span aria-hidden="true">→</span></b></a>
            </div>
        </section>

        <footer class="bond-footer"><a class="bond-logo" href="<?= BASE_URL ?>/index.php"><img class="bond-mark" src="<?= BASE_URL ?>/assets/mentor-bond-emblem.png" alt=""> Mentor<span>Bond</span></a><p>Better learning happens together.</p><a href="#top">Back to top ↑</a></footer>
    </main>
</body>
</html>
