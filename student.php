<?php

require_once __DIR__ . '/auth.php';
require_role('student');
require_once __DIR__ . '/layout.php';

try {
    $flash = handle_student_action();
    $student = get_student_profile();
    if (!$student) throw new Exception('Student profile not found for this account.');
    $courses = get_semester_courses((int) $student['semester']);
    $requests = get_student_requests();
    $semesterRequests = array_values(array_filter($requests, fn($request) => (int) $request['semester'] === (int) $student['semester']));
    $semesterRequest = $semesterRequests[0] ?? null;
    $selectedCourses = $semesterRequest ? get_request_courses((int) $semesterRequest['id']) : [];
    $meetings = get_student_meetings();
    $availability = get_student_availability();
    $announcements = get_student_announcements();
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

<section class="panel student-announcements" id="student-announcements" data-dashboard>
    <div class="announcement-section-heading"><div><p class="eyebrow">FROM YOUR MENTOR</p><h2>Announcements</h2></div><span class="meeting-count"><?= count($announcements) ?> updates</span></div>
    <?php if (!$announcements): ?><div class="meeting-empty"><strong>No announcements yet</strong><p>Updates from your assigned mentor will appear here.</p></div>
    <?php else: ?><div class="student-announcement-list"><?php foreach ($announcements as $announcement): ?><article class="student-announcement-card"><div class="announcement-card-heading"><div><h3><?= e($announcement['title']) ?></h3><small>By <?= e($announcement['mentor_name']) ?> &middot; <?= date('d M Y, h:i A', strtotime($announcement['created_at'])) ?></small></div></div><p><?= nl2br(e($announcement['message'])) ?></p></article><?php endforeach; ?></div><?php endif; ?>
</section>

<section id="courses">
<div class="split">
    <div class="panel"><p class="eyebrow">SEMESTER <?= e($student['semester']) ?></p>
        <?php if ($semesterRequest): ?><h2>Your course selection</h2><article class="course-selection-summary"><div class="announcement-card-heading"><strong>Submitted <?= date('d M Y', strtotime($semesterRequest['created_at'])) ?></strong><span class="status <?= e($semesterRequest['status']) ?>"><?= e($semesterRequest['status']) ?></span></div><div class="table-wrap"><table><thead><tr><th>Code</th><th>Name</th><th>Sem</th><th>Credits</th></tr></thead><tbody><?php foreach ($selectedCourses as $course): ?><tr><td><?= e($course['code']) ?></td><td><?= e($course['name']) ?></td><td><?= e($course['semester']) ?></td><td><?= e($course['credits']) ?></td></tr><?php endforeach; ?></tbody></table></div><?php if (is_valid_document_url($semesterRequest['receipt_path'] ?? '')): ?><a class="course-document-link" href="<?= e($semesterRequest['receipt_path']) ?>" target="_blank" rel="noopener noreferrer">View submitted document</a><?php endif; ?></article>
        <?php if (in_array($semesterRequest['status'], ['pending', 'rejected'], true)): ?><?php if ($semesterRequest['status'] === 'rejected'): ?><p class="course-request-rejected-note">This request was denied. Update the selection or document link below, then re-request approval.</p><?php endif; ?><details class="course-request-edit"><summary><?= $semesterRequest['status'] === 'rejected' ? 'Re-request course selection' : 'Edit course selection or document link' ?></summary><form method="post" class="course-request-edit-form"><input type="hidden" name="action" value="edit_request"><input type="hidden" name="request_id" value="<?= (int) $semesterRequest['id'] ?>"><div class="course-list"><?php foreach ($courses as $course): ?><label class="course-choice"><input type="checkbox" name="courses[]" value="<?= (int) $course['id'] ?>" <?= in_array((int) $course['id'], array_map('intval', array_column($selectedCourses, 'id')), true) ? 'checked' : '' ?>><span class="course-copy"><span class="course-title"><strong><?= e($course['code']) ?></strong><span><?= e($course['name']) ?></span></span><small class="course-type"><?= e($course['type']) ?></small></span><b class="course-credits"><?= e($course['credits']) ?><small>credits</small></b></label><?php endforeach; ?></div><label class="course-document-field">Required supporting document link<input required type="url" name="document_url" maxlength="255" value="<?= e(is_valid_document_url($semesterRequest['receipt_path'] ?? '') ? $semesterRequest['receipt_path'] : '') ?>" placeholder="https://drive.google.com/..." autocomplete="url"><small>A valid shareable link is required to keep this request under review.</small></label><button type="submit"><?= $semesterRequest['status'] === 'rejected' ? 'Re-request approval' : 'Save changes' ?></button></form></details>
        <?php else: ?><p class="course-request-locked">This course selection is approved and cannot be edited.</p><?php endif; ?>
        <?php elseif ($courses): ?><h2>Select your subjects</h2><p class="muted">Select core and open elective subjects. Your total credits will be checked by your mentor/admin.</p>
        <form method="post"><input type="hidden" name="action" value="request"><div class="course-list">
            <?php foreach ($courses as $course): ?>
                <label class="course-choice"><input type="checkbox" name="courses[]" value="<?= $course['id'] ?>"><span class="course-copy"><span class="course-title"><strong><?= e($course['code']) ?></strong><span><?= e($course['name']) ?></span></span><small class="course-type"><?= e($course['type']) ?></small></span><b class="course-credits"><?= e($course['credits']) ?><small>credits</small></b></label>
            <?php endforeach; ?>
        </div><label class="course-document-field">Required supporting document link<input required type="url" name="document_url" maxlength="255" placeholder="https://drive.google.com/..." autocomplete="url"><small>Paste a shareable document link. Your request cannot be submitted without it.</small></label><button>Submit for approval</button></form>
        <?php else: ?><h2>Course selection</h2><div class="meeting-empty"><strong>No courses are listed for semester <?= e($student['semester']) ?> yet.</strong><p>Your selection option will appear when courses are added.</p></div>
        <?php endif; ?>
    </div>
    <?php if (!empty($student['mentor_id'])): ?><button type="button" class="panel mentor-profile-card" id="mentor" data-mentor-dialog="mentor-profile-dialog"><span class="mentor-profile-card-top"><span class="avatar large"><?= e(strtoupper(substr($student['mentor_name'], 0, 1))) ?></span><span><small class="eyebrow">YOUR MENTOR</small><strong><?= e($student['mentor_name']) ?></strong><span><?= e($student['mentor_department'] ?: 'Department not provided') ?></span></span></span><span class="mentor-profile-email"><?= e($student['mentor_email']) ?></span><span class="mentor-profile-hint">View mentor profile <span aria-hidden="true">&rarr;</span></span></button>
    <dialog class="student-detail-dialog" id="mentor-profile-dialog" aria-labelledby="mentor-profile-title"><form method="dialog" class="student-dialog-close"><button type="submit" class="secondary">Close</button></form><div class="student-detail-heading"><span class="avatar large"><?= e(strtoupper(substr($student['mentor_name'], 0, 1))) ?></span><div><p class="eyebrow">YOUR MENTOR</p><h2 id="mentor-profile-title"><?= e($student['mentor_name']) ?></h2><p><?= e($student['mentor_department'] ?: 'Department not provided') ?></p></div></div><div class="student-detail-grid"><div><small>Email</small><strong><a href="mailto:<?= e($student['mentor_email']) ?>"><?= e($student['mentor_email']) ?></a></strong></div><div><small>Username</small><strong><?= e($student['mentor_username'] ?: 'Not provided') ?></strong></div><div><small>Section</small><strong><?= e($student['mentor_section'] ?: 'Not provided') ?></strong></div><div><small>Semester</small><strong><?= e($student['mentor_semester'] ?: 'Not provided') ?></strong></div></div><a class="button secondary mentor-dialog-meetings" href="#meetings">View meeting times</a></dialog>
    <?php else: ?><div class="panel mentor-unassigned" id="mentor"><p class="eyebrow">YOUR MENTOR</p><span class="avatar large">M</span><h2>Not assigned</h2><p class="muted">Your administrator will assign a mentor shortly.</p></div><?php endif; ?>
</div>

</section>

<section class="panel meeting-panel" id="meetings">
    <div class="meeting-panel-heading"><div><p class="eyebrow">MENTOR SESSIONS</p><h2>Meeting requests</h2><p class="muted">Choose an available time and track your request here.</p></div></div>
    <div class="meeting-columns">
        <section class="meeting-group" aria-labelledby="availability-heading">
            <div class="meeting-group-heading"><div><p class="eyebrow">BOOK A SESSION</p><h3 id="availability-heading">Available times with <?= e($student['mentor_name'] ?: 'your mentor') ?></h3></div><span class="meeting-count"><?= $student['mentor_name'] ? count($availability) . ' open' : 'Not assigned' ?></span></div>
            <?php if (!$student['mentor_name']): ?><div class="meeting-empty"><strong>No mentor assigned yet</strong><p>Your administrator will assign a mentor before you can request a meeting.</p></div>
            <?php elseif (!$availability): ?><div class="meeting-empty"><strong>No open times right now</strong><p>Your mentor has not posted any available times. Check back later.</p></div>
            <?php else: ?><div class="meeting-slot-list">
                <?php foreach ($availability as $slot): ?><article class="meeting-slot-card">
                    <div class="meeting-slot-top"><div><span class="meeting-date"><?= date('D, d M Y', strtotime($slot['starts_at'])) ?></span><strong><?= date('h:i A', strtotime($slot['starts_at'])) ?> &ndash; <?= date('h:i A', strtotime($slot['ends_at'])) ?></strong></div><span class="meeting-mode"><?= e($slot['mode']) ?></span></div>
                    <?php if ($slot['description']): ?><p class="meeting-description"><?= e($slot['description']) ?></p><?php endif; ?>
                    <form method="post" class="meeting-book-form"><input type="hidden" name="action" value="book_meeting"><input type="hidden" name="availability_id" value="<?= (int) $slot['id'] ?>"><label>Your note <input name="notes" maxlength="500" placeholder="What would you like to discuss?"></label><button type="submit">Request this time</button></form>
                </article><?php endforeach; ?>
            </div><?php endif; ?>
        </section>
        <section class="meeting-group" aria-labelledby="requests-heading">
            <div class="meeting-group-heading"><div><p class="eyebrow">YOUR ACTIVITY</p><h3 id="requests-heading">Your requests</h3></div><span class="meeting-count"><?= count($meetings) ?> total</span></div>
            <?php if (!$meetings): ?><div class="meeting-empty"><strong>No meeting requests yet</strong><p>Your requests and their approval status will appear here.</p></div>
            <?php else: ?><div class="meeting-request-list">
                <?php foreach ($meetings as $meeting): ?><article class="meeting-request-card meeting-request-<?= e($meeting['status']) ?>">
                    <div class="meeting-request-top"><div><span class="meeting-date"><?= date('D, d M Y', strtotime($meeting['meeting_at'])) ?></span><strong><?= date('h:i A', strtotime($meeting['meeting_at'])) ?></strong></div><span class="status <?= e($meeting['status']) ?>"><?= e($meeting['status']) ?></span></div>
                    <div class="meeting-request-meta"><span><?= e($meeting['mode']) ?></span><span>Mentor: <?= e($meeting['mentor_name']) ?></span></div>
                    <?php if ($meeting['notes']): ?><p class="meeting-description"><strong>Your note:</strong> <?= e($meeting['notes']) ?></p><?php endif; ?>
                    <?php if ($meeting['status'] === 'pending'): ?><p class="meeting-status-note">Waiting for your mentor to review this request.</p><?php elseif ($meeting['status'] === 'approved'): ?><form method="post" class="meeting-confirm-form"><input type="hidden" name="action" value="confirm_meeting"><input type="hidden" name="meeting_id" value="<?= (int) $meeting['id'] ?>"><span>Your mentor approved this time.</span><button type="submit">Confirm meeting</button></form><?php elseif ($meeting['status'] === 'rejected'): ?><p class="meeting-status-note">This request was declined. You can choose another open time.</p><?php endif; ?>
                </article><?php endforeach; ?>
            </div><?php endif; ?>
        </section>
    </div>
</section>

<section class="panel" id="academic"><p class="eyebrow">ACADEMIC</p><h2>Academic info</h2>
    <div class="academic-detail-layout">
        <div class="academic-detail-group">
            <h3>Academic information</h3>
            <div class="academic">
                <span><strong>Attendance</strong><b><?= number_format((float) $student['attendance'], 1) ?>%</b></span>
                <span><strong>CGPA</strong><b><?= number_format((float) $student['cgpa'], 2) ?></b></span>
                <span><strong>Completed credits</strong><b><?= e($student['completed_credits']) ?></b></span>
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

<script>
document.querySelectorAll('[data-mentor-dialog]').forEach((card) => {
    card.addEventListener('click', () => document.getElementById(card.dataset.mentorDialog)?.showModal());
});
document.querySelector('.mentor-dialog-meetings')?.addEventListener('click', () => {
    document.getElementById('mentor-profile-dialog')?.close();
});
</script>
<?php page_end(); ?>

<?php
function handle_student_action(): string
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') return '';

    return match ($_POST['action'] ?? '') {
        'request' => submit_course_request(),
        'edit_request' => edit_course_request(),
        'update_profile' => update_student_profile(),
        'change_password' => change_student_password(),
        'book_meeting' => book_student_meeting(),
        'confirm_meeting' => confirm_student_meeting(),
        default => '',
    };
}

function submit_course_request(): string
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST' || ($_POST['action'] ?? '') !== 'request') return '';
    $studentId = current_student_id();
    $courseIds = array_map('intval', $_POST['courses'] ?? []);
    if (!$courseIds) throw new Exception('Choose at least one subject.');
    $courseIds = array_values(array_unique($courseIds));

    $placeholders = implode(',', array_fill(0, count($courseIds), '?'));
    db()->beginTransaction();
    try {
        // Lock the account row so simultaneous submissions for this student are serialized.
        $studentQuery = db()->prepare("SELECT semester FROM users WHERE id = ? AND role = 'student' FOR UPDATE");
        $studentQuery->execute([$studentId]);
        $semester = (int) $studentQuery->fetchColumn();
        if ($semester < 1 || $semester > 8) throw new Exception('Your account does not have a valid semester assigned.');
        $documentUrl = trim((string) ($_POST['document_url'] ?? ''));
        if (!is_valid_document_url($documentUrl)) throw new Exception('Add a valid document link before submitting your course selection.');
        $existingRequest = db()->prepare('SELECT id FROM course_requests WHERE student_id = ? AND semester = ? LIMIT 1');
        $existingRequest->execute([$studentId, $semester]);
        if ($existingRequest->fetchColumn()) throw new Exception("Your course selection for semester $semester has already been submitted.");

        $creditQuery = db()->prepare("SELECT COALESCE(SUM(credits), 0) FROM courses WHERE semester = ? AND id IN ($placeholders)");
        $creditQuery->execute(array_merge([$semester], $courseIds));
        $totalCredits = (int) $creditQuery->fetchColumn();
        if ($totalCredits === 0) throw new Exception("Select courses offered in semester $semester.");
        $validCount = db()->prepare("SELECT COUNT(*) FROM courses WHERE semester = ? AND id IN ($placeholders)");
        $validCount->execute(array_merge([$semester], $courseIds));
        if ((int) $validCount->fetchColumn() !== count($courseIds)) throw new Exception("Select courses offered in semester $semester only.");
        db()->prepare("INSERT INTO course_requests (student_id, semester, total_credits, status, receipt_path) VALUES (?, ?, ?, 'pending', ?)")->execute([$studentId, $semester, $totalCredits, $documentUrl]);
        $requestId = db()->lastInsertId();
        $linkCourse = db()->prepare('INSERT INTO request_courses (request_id, course_id) VALUES (?, ?)');
        foreach ($courseIds as $courseId) $linkCourse->execute([$requestId, $courseId]);
        db()->commit();
    } catch (Throwable $exception) { db()->rollBack(); throw $exception; }
    return 'Your subject request was submitted for approval.';
}

function edit_course_request(): string
{
    $studentId = current_student_id();
    $requestId = filter_var($_POST['request_id'] ?? null, FILTER_VALIDATE_INT);
    $courseIds = array_values(array_unique(array_filter(array_map('intval', $_POST['courses'] ?? []))));
    $documentUrl = trim((string) ($_POST['document_url'] ?? ''));
    if (!$requestId) throw new Exception('Choose a valid course request to edit.');
    if (!$courseIds) throw new Exception('Choose at least one subject.');
    if (!is_valid_document_url($documentUrl)) throw new Exception('Add a valid document link before saving your course selection.');

    $placeholders = implode(',', array_fill(0, count($courseIds), '?'));
    db()->beginTransaction();
    try {
        $studentQuery = db()->prepare("SELECT semester FROM users WHERE id = ? AND role = 'student' FOR UPDATE");
        $studentQuery->execute([$studentId]);
        $currentSemester = (int) $studentQuery->fetchColumn();
        $requestQuery = db()->prepare('SELECT semester, status FROM course_requests WHERE id = ? AND student_id = ? FOR UPDATE');
        $requestQuery->execute([$requestId, $studentId]);
        $request = $requestQuery->fetch();
        if (!$request) throw new Exception('Course request not found.');
        if ((int) $request['semester'] !== $currentSemester) throw new Exception('Only your current semester course selection can be edited.');
        if (!in_array($request['status'], ['pending', 'rejected'], true)) throw new Exception('This course selection is locked after approval.');

        $creditsQuery = db()->prepare("SELECT COALESCE(SUM(credits), 0) FROM courses WHERE semester = ? AND id IN ($placeholders)");
        $creditsQuery->execute(array_merge([$currentSemester], $courseIds));
        $totalCredits = (int) $creditsQuery->fetchColumn();
        $validCount = db()->prepare("SELECT COUNT(*) FROM courses WHERE semester = ? AND id IN ($placeholders)");
        $validCount->execute(array_merge([$currentSemester], $courseIds));
        if ($totalCredits < 1 || (int) $validCount->fetchColumn() !== count($courseIds)) throw new Exception("Select courses offered in semester $currentSemester only.");

        db()->prepare("UPDATE course_requests SET total_credits = ?, receipt_path = ?, status = 'pending' WHERE id = ?")->execute([$totalCredits, $documentUrl, $requestId]);
        db()->prepare('DELETE FROM request_courses WHERE request_id = ?')->execute([$requestId]);
        $linkCourse = db()->prepare('INSERT INTO request_courses (request_id, course_id) VALUES (?, ?)');
        foreach ($courseIds as $courseId) $linkCourse->execute([$requestId, $courseId]);
        db()->commit();
    } catch (Throwable $exception) { db()->rollBack(); throw $exception; }

    return 'Your course selection and document link were updated and sent for review.';
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
function get_student_profile(): array { $query = db()->prepare('SELECT u.id, u.name, u.username, u.email, u.department, u.section, u.semester, u.dob, s.attendance, s.cgpa, s.completed_credits, m.id AS mentor_id, m.name AS mentor_name, m.username AS mentor_username, m.email AS mentor_email, m.department AS mentor_department, m.section AS mentor_section, m.semester AS mentor_semester FROM users u LEFT JOIN students s ON s.user_id = u.id LEFT JOIN mentor_assignments a ON a.student_id = u.id LEFT JOIN users m ON m.id = a.mentor_id WHERE u.id = ? AND u.role = \'student\''); $query->execute([current_student_id()]); return $query->fetch() ?: []; }
function get_semester_courses(int $semester): array { return db()->query('SELECT * FROM courses WHERE semester = ' . $semester . ' ORDER BY type, code')->fetchAll(); }
function get_student_requests(): array { $query = db()->prepare("SELECT r.*, GROUP_CONCAT(CONCAT(c.code, ' · ', c.name) SEPARATOR ' | ') AS subjects FROM course_requests r LEFT JOIN request_courses rc ON rc.request_id = r.id LEFT JOIN courses c ON c.id = rc.course_id WHERE r.student_id = ? GROUP BY r.id ORDER BY r.semester DESC, r.created_at DESC"); $query->execute([current_student_id()]); return $query->fetchAll(); }
function get_request_courses(int $requestId): array { $query = db()->prepare('SELECT c.id, c.code, c.name, c.semester, c.credits FROM request_courses rc JOIN courses c ON c.id = rc.course_id WHERE rc.request_id = ? ORDER BY c.type, c.code'); $query->execute([$requestId]); return $query->fetchAll(); }
function get_student_meetings(): array { $query = db()->prepare('SELECT mt.*, m.name AS mentor_name FROM meetings mt JOIN users m ON m.id = mt.mentor_id WHERE mt.student_id = ? ORDER BY mt.meeting_at DESC'); $query->execute([current_student_id()]); return $query->fetchAll(); }
function get_student_availability(): array { $query = db()->prepare("SELECT av.* FROM mentor_availability av JOIN mentor_assignments ma ON ma.mentor_id = av.mentor_id WHERE ma.student_id = ? AND av.is_active = 1 AND av.starts_at > NOW() AND NOT EXISTS (SELECT 1 FROM meetings mt WHERE mt.availability_id = av.id AND mt.status IN ('pending','approved','confirmed')) ORDER BY av.starts_at"); $query->execute([current_student_id()]); return $query->fetchAll(); }
function get_student_announcements(): array { $query = db()->prepare('SELECT an.*, m.name AS mentor_name FROM announcements an JOIN mentor_assignments ma ON ma.mentor_id = an.mentor_id JOIN users m ON m.id = an.mentor_id WHERE ma.student_id = ? ORDER BY an.created_at DESC LIMIT 10'); $query->execute([current_student_id()]); return $query->fetchAll(); }
function book_student_meeting(): string
{
    $availabilityId = filter_var($_POST['availability_id'] ?? null, FILTER_VALIDATE_INT);
    if (!$availabilityId) throw new Exception('Choose a valid available time.');
    db()->beginTransaction();
    try {
        $slotQuery = db()->prepare('SELECT av.* FROM mentor_availability av JOIN mentor_assignments ma ON ma.mentor_id = av.mentor_id WHERE av.id = ? AND ma.student_id = ? AND av.is_active = 1 AND av.starts_at > NOW() FOR UPDATE');
        $slotQuery->execute([$availabilityId, current_student_id()]);
        $slot = $slotQuery->fetch();
        if (!$slot) throw new Exception('That time is no longer available.');
        $taken = db()->prepare("SELECT id FROM meetings WHERE availability_id = ? AND status IN ('pending','approved','confirmed') FOR UPDATE");
        $taken->execute([$availabilityId]);
        if ($taken->fetch()) throw new Exception('That time has already been requested.');
        $insert = db()->prepare("INSERT INTO meetings (availability_id, student_id, mentor_id, meeting_at, mode, notes, status) VALUES (?, ?, ?, ?, ?, ?, 'pending')");
        $insert->execute([$availabilityId, current_student_id(), $slot['mentor_id'], $slot['starts_at'], $slot['mode'], trim($_POST['notes'] ?? '')]);
        db()->commit();
    } catch (Throwable $exception) { db()->rollBack(); throw $exception; }
    return 'Meeting request sent to your mentor for approval.';
}
function confirm_student_meeting(): string
{
    $query = db()->prepare("UPDATE meetings SET status = 'confirmed' WHERE id = ? AND student_id = ? AND status = 'approved'");
    $query->execute([(int) ($_POST['meeting_id'] ?? 0), current_student_id()]);
    if (!$query->rowCount()) throw new Exception('Only an approved meeting request can be confirmed.');
    return 'Your meeting is confirmed.';
}
