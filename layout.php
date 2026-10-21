<?php

require_once __DIR__ . '/config.php';

/** Render the shared sidebar and page header for every role dashboard. */
function page_start(string $title, string $role, string $active = 'dashboard'): void
{
    $profile = [
        'Admin' => ['A', 'System Administrator'],
        'Mentor' => ['M', 'Dr. Arjun Mehta'],
        'Student' => ['S', 'Aarav Sharma'],
    ][$role];
    ?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($title) ?> · Minar Mentorship</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/style.css?v=<?= filemtime(__DIR__ . '/assets/style.css') ?>">
</head>
<body class="<?= strtolower(e($role)) ?>-portal">
    <style>.sidebar-footer{display:none}.logout-link{margin-left:8px;color:#bd4e2c;font-size:12px;font-weight:800;text-decoration:none}.logout-link:hover{text-decoration:underline}</style>
    <aside class="sidebar">
        <a class="brand" href="<?= BASE_URL ?>/index.php"><span>✦</span> Minar</a>
        <p class="role-label"><?= e($role) ?> portal</p>

        <nav>
            <a class="<?= $active === 'dashboard' ? 'active' : '' ?>" href="<?= BASE_URL ?>/<?= strtolower($role) ?>.php">▦ Dashboard</a>
            <?php foreach (role_navigation($role) as $link): ?>
                <a class="<?= $active === $link['id'] ? 'active' : '' ?>" href="<?= $role === 'Admin' ? BASE_URL . '/admin.php?view=' . rawurlencode($link['id']) : '#' . e($link['id']) ?>"><?= e($link['label']) ?></a>
            <?php endforeach; ?>
        </nav>

        <div class="sidebar-footer"><a href="<?= BASE_URL ?>/index.php">⇥ Switch portal</a></div>
    </aside>

    <main>
        <header>
            <div>
                <p class="eyebrow">MINAR / MENTORSHIP</p>
                <h1><?= e($title) ?></h1>
            </div>
            <div class="user-chip" id="profile">
                <span class="avatar"><?= $profile[0] ?></span>
                <div><strong><?= e($_SESSION['name'] ?? $profile[1]) ?></strong><small><?= e($role) ?></small></div>
                <a class="logout-link" href="<?= BASE_URL ?>/logout.php">Log out</a>
            </div>
        </header>
<?php
}

function role_navigation(string $role): array
{
    $navigation = [
        'Admin' => [
            ['id' => 'people', 'label' => 'People'],
            ['id' => 'courses', 'label' => 'Courses'],
            ['id' => 'requests', 'label' => 'Requests'],
            ['id' => 'assignments', 'label' => 'Assignments'],
        ],
        'Mentor' => [
            ['id' => 'students', 'label' => 'My students'],
            ['id' => 'requests', 'label' => 'Requests'],
            ['id' => 'meetings', 'label' => 'Meetings'],
            ['id' => 'announcements', 'label' => 'Announcements'],
        ],
        'Student' => [
            ['id' => 'courses', 'label' => 'Course selection'],
            ['id' => 'mentor', 'label' => 'My mentor'],
            ['id' => 'meetings', 'label' => 'Meetings'],
            ['id' => 'academic', 'label' => 'Academic'],
        ],
    ];

    return $navigation[$role] ?? [];
}

function page_end(): void
{
    ?>
    </main>
    <script>
        (() => {
            const body = document.body;
            if (!body.classList.contains('student-portal') && !body.classList.contains('mentor-portal')) return;

            const main = document.querySelector('main');
            const navLinks = [...document.querySelectorAll('.sidebar nav a[href^="#"]')];
            const pageSections = [...main.children].filter((element) => element.matches('section'));

            function showSelectedView() {
                const viewId = decodeURIComponent(window.location.hash.slice(1));
                const target = viewId ? document.getElementById(viewId) : null;
                const selectedLink = navLinks.find((link) => link.getAttribute('href') === `#${viewId}`);

                navLinks.forEach((link) => link.classList.toggle('active', Boolean(selectedLink && link === selectedLink)));
                const dashboardLink = document.querySelector('.sidebar nav a[href$="student.php"], .sidebar nav a[href$="mentor.php"]');
                if (dashboardLink) dashboardLink.classList.toggle('active', !selectedLink);
                const pageHeading = main.querySelector('header h1');
                if (pageHeading) {
                    pageHeading.textContent = selectedLink
                        ? selectedLink.textContent.trim()
                        : body.classList.contains('student-portal') ? 'Student dashboard' : 'Mentor dashboard';
                }

                if (!selectedLink || !target) {
                    const isStudent = body.classList.contains('student-portal');
                    pageSections.forEach((section) => {
                        section.hidden = isStudent && !section.hasAttribute('data-dashboard');
                    });
                    main.querySelectorAll('.panel[hidden]').forEach((panel) => {
                        if (!pageSections.includes(panel)) panel.hidden = false;
                    });
                    return;
                }

                const activeSection = target.closest('section');
                pageSections.forEach((section) => { section.hidden = section !== activeSection; });
                main.querySelectorAll('.panel[hidden]').forEach((panel) => {
                    if (!pageSections.includes(panel)) panel.hidden = false;
                });

                if (target === activeSection) {
                    navLinks.forEach((link) => {
                        const nestedId = link.getAttribute('href').slice(1);
                        const nestedTarget = document.getElementById(nestedId);
                        if (nestedTarget && nestedTarget !== target && activeSection.contains(nestedTarget)) nestedTarget.hidden = true;
                    });
                } else {
                    activeSection.querySelectorAll('.panel').forEach((panel) => {
                        panel.hidden = panel !== target;
                    });
                }
            }

            window.addEventListener('hashchange', showSelectedView);
            showSelectedView();
        })();
    </script>
</body>
</html>
<?php
}
