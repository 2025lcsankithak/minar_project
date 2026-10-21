<?php

require_once __DIR__ . '/auth.php';
require_role('mentor');
require_once __DIR__ . '/layout.php';

const MENTOR_ID = 2; // Replace with the logged-in mentor ID after authentication is added.

try {
    $flash = save_meeting();
    $mentor = get_mentor();
    $students = get_assigned_students();
    $requests = get_mentor_requests();
    $meetings = get_mentor_meetings();
} catch (Throwable $exception) {
    page_start('Mentor dashboard', 'Mentor');
    echo '<div class="notice warning">Import the SQL file first: ' . e($exception->getMessage()) . '</div>';
    page_end();
    exit;
}

page_start('Mentor dashboard', 'Mentor');
$pendingRequests = count(array_filter($requests, fn(array $request) => $request['status'] === 'pending'));
?>

<?php if ($flash): ?><div class="notice success"><?= e($flash) ?></div><?php endif; ?>

<section class="welcome">
    <div><p class="eyebrow">GOOD MORNING, <?= e(explode(' ', $mentor['name'])[1] ?? $mentor['name']) ?></p><h2>Your guidance matters.</h2><p>Keep an eye on the students and requests that need you today.</p></div>
    <div class="contact-card"><span class="avatar">AM</span><div><strong><?= e($mentor['name']) ?></strong><small><?= e($mentor['department']) ?> · <?= e($mentor['email']) ?></small></div></div>
</section>

<section class="stats">
    <article><small>Assigned students</small><b><?= count($students) ?></b><span>Across <?= count(array_unique(array_column($students, 'section'))) ?> section(s)</span></article>
    <article><small>Pending approvals</small><b><?= $pendingRequests ?></b><span>Course requests</span></article>
    <article><small>Upcoming meetings</small><b><?= count($meetings) ?></b><span>Scheduled sessions</span></article>
</section>

<section class="panel" id="students">
    <div class="panel-title"><div><p class="eyebrow">MY COHORT</p><h2>Assigned students</h2></div><span class="badge"><?= count($students) ?> students</span></div>
    <div class="student-cards">
        <?php foreach ($students as $student): ?>
            <article class="student-card"><span class="avatar large"><?= e(strtoupper(substr($student['name'], 0, 1))) ?></span><h3><?= e($student['name']) ?></h3><p><?= e($student['section']) ?> · Semester <?= e($student['semester']) ?></p><small><?= e($student['email']) ?></small></article>
        <?php endforeach; ?>
    </div>
</section>

<section class="split">
    <div class="panel" id="requests"><p class="eyebrow">REVIEW QUEUE</p><h2>Subject requests</h2>
        <?php foreach ($requests as $request): ?>
            <div class="request-row"><div><strong><?= e($request['student_name']) ?></strong><small><?= e($request['subjects']) ?> · <?= $request['total_credits'] ?> credits</small></div><span class="status <?= e($request['status']) ?>"><?= e($request['status']) ?></span></div>
        <?php endforeach; ?>
    </div>
    <div class="panel" id="meetings"><p class="eyebrow">SCHEDULE</p><h2>Book a meeting</h2>
        <form method="post" class="stack"><input type="hidden" name="action" value="meeting">
            <label>Student<select name="student_id"><?php foreach ($students as $student): ?><option value="<?= $student['id'] ?>"><?= e($student['name']) ?></option><?php endforeach; ?></select></label>
            <label>Date & time<input required type="datetime-local" name="meeting_at"></label><label>Mode<select name="mode"><option>Online</option><option>In person</option></select></label><label>Note<textarea name="notes" placeholder="What will you discuss?"></textarea></label><button>Schedule meeting</button>
        </form>
    </div>
</section>

<section class="panel" id="announcements"><p class="eyebrow">UPCOMING MEETINGS</p><h2>Your schedule</h2>
    <?php if (!$meetings): ?><p class="empty">No meetings scheduled yet.</p>
    <?php else: ?><div class="table-wrap"><table><thead><tr><th>Student</th><th>Date & time</th><th>Mode</th><th>Note</th></tr></thead><tbody>
        <?php foreach ($meetings as $meeting): ?><tr><td><?= e($meeting['student_name']) ?></td><td><?= date('d M Y, h:i A', strtotime($meeting['meeting_at'])) ?></td><td><?= e($meeting['mode']) ?></td><td><?= e($meeting['notes']) ?></td></tr><?php endforeach; ?>
    </tbody></table></div><?php endif; ?>
</section>

<?php page_end(); ?>

<?php
function save_meeting(): string
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST' || ($_POST['action'] ?? '') !== 'meeting') return '';
    db()->prepare('INSERT INTO meetings (student_id, mentor_id, meeting_at, mode, notes, status) VALUES (?, ?, ?, ?, ?, ?)')->execute([$_POST['student_id'], MENTOR_ID, $_POST['meeting_at'], $_POST['mode'], $_POST['notes'], 'scheduled']);
    return 'Meeting scheduled and student notified.';
}
function get_mentor(): array { return db()->query('SELECT * FROM users WHERE id = ' . MENTOR_ID)->fetch(); }
function get_assigned_students(): array { return db()->query('SELECT s.*, a.id AS assignment_id FROM mentor_assignments a JOIN users s ON s.id = a.student_id WHERE a.mentor_id = ' . MENTOR_ID)->fetchAll(); }
function get_mentor_requests(): array { return db()->query("SELECT r.*, u.name AS student_name, GROUP_CONCAT(c.code SEPARATOR ', ') AS subjects FROM course_requests r JOIN users u ON u.id = r.student_id JOIN mentor_assignments a ON a.student_id = u.id LEFT JOIN request_courses rc ON rc.request_id = r.id LEFT JOIN courses c ON c.id = rc.course_id WHERE a.mentor_id = " . MENTOR_ID . ' GROUP BY r.id ORDER BY r.created_at DESC')->fetchAll(); }
function get_mentor_meetings(): array { return db()->query('SELECT mt.*, u.name AS student_name FROM meetings mt JOIN users u ON u.id = mt.student_id WHERE mt.mentor_id = ' . MENTOR_ID . ' ORDER BY mt.meeting_at')->fetchAll(); }
