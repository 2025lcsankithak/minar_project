<?php

require_once __DIR__ . '/auth.php';
require_role('student');
require_once __DIR__ . '/layout.php';

try {
    $flash = handle_student_action();
    $student = get_student_profile();
    $courses = get_semester_courses((int) $student['semester']);
    $requests = get_student_requests();
    $meetings = get_student_meetings();
} catch (Throwable $exception) {
    page_start('Student dashboard', 'Student');
    echo '<div class="notice warning">Import the SQL file first: ' . e($exception->getMessage()) . '</div>';
    page_end();
    exit;
}

page_start('Student dashboard', 'Student');
?>

<?php if ($flash): ?><div class="notice success"><?= e($flash) ?></div><?php endif; ?>

<section class="welcome" data-dashboard>
    <div><p class="eyebrow">WELCOME BACK</p><h2><?= e($student['name']) ?></h2><p><?= e($student['department']) ?> · <?= e($student['section']) ?> · Semester <?= e($student['semester']) ?></p></div>
    <div class="credit-badge"><small>LAST REQUEST</small><b><?= $requests[0]['total_credits'] ?? 0 ?></b><span>credits selected</span></div>
</section>

<section id="courses">
<div class="split">
    <div class="panel"><p class="eyebrow">SEMESTER <?= e($student['semester']) ?></p><h2>Select your subjects</h2><p class="muted">Select core and open elective subjects. Your total credits will be checked by your mentor/admin.</p>
        <form method="post"><input type="hidden" name="action" value="request"><div class="course-list">
            <?php foreach ($courses as $course): ?>
                <label class="course-choice"><input type="checkbox" name="courses[]" value="<?= $course['id'] ?>"><span class="course-copy"><span class="course-title"><strong><?= e($course['code']) ?></strong><span><?= e($course['name']) ?></span></span><small class="course-type"><?= e($course['type']) ?></small></span><b class="course-credits"><?= e($course['credits']) ?><small>credits</small></b></label>
            <?php endforeach; ?>
        </div><button>Submit for approval</button></form>
    </div>
    <div class="panel" id="mentor"><p class="eyebrow">YOUR MENTOR</p><span class="avatar large"><?= e(strtoupper(substr($student['mentor_name'] ?? 'M', 0, 1))) ?></span><h2><?= e($student['mentor_name'] ?? 'Not assigned') ?></h2><p><?= e($student['mentor_department'] ?? '') ?></p><p class="muted"><?= e($student['mentor_email'] ?? 'Your administrator will assign a mentor shortly.') ?></p><a class="button secondary" href="#meetings">View meetings</a></div>
</div>

<section class="panel" id="submissions"><p class="eyebrow">MY SUBMISSIONS</p><h2>Request status</h2>
    <?php if (!$requests): ?><p class="empty">You have not submitted any course requests.</p>
    <?php else: ?><div class="table-wrap"><table><thead><tr><th>Submitted</th><th>Subjects</th><th>Credits</th><th>Status</th></tr></thead><tbody>
        <?php foreach ($requests as $request): ?><tr><td><?= date('d M Y', strtotime($request['created_at'])) ?></td><td><?= e($request['subjects']) ?></td><td><?= $request['total_credits'] ?></td><td><span class="status <?= e($request['status']) ?>"><?= e($request['status']) ?></span></td></tr><?php endforeach; ?>
    </tbody></table></div><?php endif; ?>
</section>
</section>

<section class="panel" id="meetings"><p class="eyebrow">MEETINGS</p><h2>Meeting requests</h2>
        <?php if (!$meetings): ?><p class="empty">No meeting scheduled. Contact your mentor to request one.</p>
        <?php else: foreach ($meetings as $meeting): ?><div class="request-row"><div><strong><?= date('d M, h:i A', strtotime($meeting['meeting_at'])) ?></strong><small><?= e($meeting['mode']) ?> · <?= e($meeting['notes']) ?></small></div><span class="status approved"><?= e($meeting['status']) ?></span></div><?php endforeach; endif; ?>
    </section>

<section class="panel" id="academic"><p class="eyebrow">ACADEMIC</p><h2>Academic info</h2>
    <div class="academic-detail-layout">
        <div class="academic-detail-group">
            <h3>Academic information</h3>
            <div class="academic">
                <span><strong>Attendance</strong><b>88%</b></span>
                <span><strong>CGPA</strong><b>8.4</b></span>
                <span><strong>Results</strong><b>Available</b></span>
            </div>
        </div>
        <div class="academic-detail-group">
            <h3>Student profile</h3>
            <div class="academic">
                <span><strong>Full name</strong><b><?= e($student['name']) ?></b></span>
                <span><strong>Username</strong><b><?= e($student['username']) ?></b></span>
                <span><strong>Email</strong><b><?= e($student['email']) ?></b></span>
                <span><strong>Department</strong><b><?= e($student['department'] ?: 'Not provided') ?></b></span>
                <span><strong>Section</strong><b><?= e($student['section'] ?: 'Not provided') ?></b></span>
                <span><strong>Semester</strong><b><?= e($student['semester'] ?: 'Not provided') ?></b></span>
                <span><strong>Date of birth</strong><b><?= !empty($student['dob']) ? e(date('d M Y', strtotime($student['dob']))) : 'Not provided' ?></b></span>
            </div>
            <details class="profile-edit"><summary>Edit profile</summary>
                <form method="post" class="stack">
                    <input type="hidden" name="action" value="update_profile">
                    <label>Full name<input required maxlength="100" name="name" value="<?= e($student['name']) ?>" autocomplete="name"></label>
                    <label>Email<input required type="email" maxlength="120" name="email" value="<?= e($student['email']) ?>" autocomplete="email"></label>
                    <label>Date of birth<input type="date" name="dob" value="<?= e($student['dob'] ?? '') ?>" autocomplete="bday"></label>
                    <button type="submit">Save profile</button>
                </form>
            </details>
            <details class="profile-edit"><summary>Change password</summary>
                <form method="post" class="stack">
                    <input type="hidden" name="action" value="change_password">
                    <label>Current password<input required type="password" name="current_password" autocomplete="current-password"></label>
                    <label>New password<input required type="password" name="new_password" minlength="8" autocomplete="new-password"></label>
                    <label>Confirm new password<input required type="password" name="confirm_password" minlength="8" autocomplete="new-password"></label>
                    <button type="submit">Update password</button>
                </form>
            </details>
        </div>
    </div>
</section>

<?php page_end(); ?>

<?php
function handle_student_action(): string
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') return '';

    return match ($_POST['action'] ?? '') {
        'request' => submit_course_request(),
        'update_profile' => update_student_profile(),
        'change_password' => change_student_password(),
        default => '',
    };
}

function submit_course_request(): string
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST' || ($_POST['action'] ?? '') !== 'request') return '';
    $courseIds = array_map('intval', $_POST['courses'] ?? []);
    if (!$courseIds) throw new Exception('Choose at least one subject.');

    $placeholders = implode(',', array_fill(0, count($courseIds), '?'));
    $creditQuery = db()->prepare("SELECT COALESCE(SUM(credits), 0) FROM courses WHERE id IN ($placeholders)");
    $creditQuery->execute($courseIds);

    db()->beginTransaction();
    try {
        db()->prepare("INSERT INTO course_requests (student_id, total_credits, status, receipt_path) VALUES (?, ?, 'pending', ?)")->execute([current_student_id(), $creditQuery->fetchColumn(), 'uploads/receipt-placeholder.pdf']);
        $requestId = db()->lastInsertId();
        $linkCourse = db()->prepare('INSERT INTO request_courses (request_id, course_id) VALUES (?, ?)');
        foreach ($courseIds as $courseId) $linkCourse->execute([$requestId, $courseId]);
        db()->commit();
    } catch (Throwable $exception) { db()->rollBack(); throw $exception; }
    return 'Your subject request was submitted for approval.';
}

function update_student_profile(): string
{
    $studentId = current_student_id();
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $dob = trim($_POST['dob'] ?? '');

    if ($name === '' || strlen($name) > 100) throw new Exception('Enter a name up to 100 characters.');
    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 120) throw new Exception('Enter a valid email address.');
    if ($dob !== '') {
        $birthDate = DateTimeImmutable::createFromFormat('!Y-m-d', $dob);
        $dateErrors = DateTimeImmutable::getLastErrors();
        if (!$birthDate || $birthDate->format('Y-m-d') !== $dob || ($dateErrors && ($dateErrors['warning_count'] || $dateErrors['error_count']))) {
            throw new Exception('Enter a valid date of birth.');
        }
    }

    $duplicateEmail = db()->prepare('SELECT COUNT(*) FROM users WHERE email = ? AND id <> ?');
    $duplicateEmail->execute([$email, $studentId]);
    if ((int) $duplicateEmail->fetchColumn() > 0) throw new Exception('That email address is already in use.');

    $update = db()->prepare("UPDATE users SET name = ?, email = ?, dob = ? WHERE id = ? AND role = 'student'");
    $update->execute([$name, $email, $dob !== '' ? $dob : null, $studentId]);
    $_SESSION['name'] = $name;

    return 'Your profile was updated.';
}

function change_student_password(): string
{
    $studentId = current_student_id();
    $currentPassword = $_POST['current_password'] ?? '';
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    if (strlen($newPassword) < 8) throw new Exception('The new password must be at least 8 characters.');
    if ($newPassword !== $confirmPassword) throw new Exception('The new passwords do not match.');

    $lookup = db()->prepare("SELECT password FROM users WHERE id = ? AND role = 'student'");
    $lookup->execute([$studentId]);
    $passwordHash = $lookup->fetchColumn();
    if (!$passwordHash || !password_verify($currentPassword, $passwordHash)) throw new Exception('The current password is incorrect.');

    $update = db()->prepare("UPDATE users SET password = ? WHERE id = ? AND role = 'student'");
    $update->execute([password_hash($newPassword, PASSWORD_DEFAULT), $studentId]);

    return 'Your password was changed.';
}

function current_student_id(): int { return (int) ($_SESSION['user_id'] ?? 0); }
function get_student_profile(): array { $query = db()->prepare('SELECT s.name, s.username, s.email, s.department, s.section, s.semester, s.dob, m.name AS mentor_name, m.email AS mentor_email, m.department AS mentor_department FROM users s LEFT JOIN mentor_assignments a ON a.student_id = s.id LEFT JOIN users m ON m.id = a.mentor_id WHERE s.id = ? AND s.role = \'student\''); $query->execute([current_student_id()]); return $query->fetch() ?: []; }
function get_semester_courses(int $semester): array { return db()->query('SELECT * FROM courses WHERE semester = ' . $semester . ' ORDER BY type, code')->fetchAll(); }
function get_student_requests(): array { $query = db()->prepare("SELECT r.*, GROUP_CONCAT(CONCAT(c.code, ' · ', c.name) SEPARATOR ' | ') AS subjects FROM course_requests r LEFT JOIN request_courses rc ON rc.request_id = r.id LEFT JOIN courses c ON c.id = rc.course_id WHERE r.student_id = ? GROUP BY r.id ORDER BY r.created_at DESC"); $query->execute([current_student_id()]); return $query->fetchAll(); }
function get_student_meetings(): array { $query = db()->prepare('SELECT * FROM meetings WHERE student_id = ? ORDER BY meeting_at'); $query->execute([current_student_id()]); return $query->fetchAll(); }
