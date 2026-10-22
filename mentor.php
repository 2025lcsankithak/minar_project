<?php

require_once __DIR__ . '/auth.php';
require_role('mentor');
require_once __DIR__ . '/layout.php';

try {
    $flash = handle_mentor_action();
    $mentorId = (int) ($_SESSION['user_id'] ?? 0);
    $mentor = get_mentor($mentorId);
    if (!$mentor) throw new Exception('Mentor profile not found for this account.');
    $students = get_assigned_students($mentorId);
    $requests = get_mentor_requests($mentorId);
    $meetings = get_mentor_meetings($mentorId);
    $announcements = get_mentor_announcements($mentorId);
    $availability = get_mentor_availability($mentorId);
} catch (Throwable $exception) {
    page_start('Mentor dashboard', 'Mentor');
    echo '<div class="notice warning">' . e($exception->getMessage()) . '</div>';
    page_end();
    exit;
}

page_start('Mentor dashboard', 'Mentor');
$pendingRequests = count(array_filter($requests, fn(array $request) => $request['status'] === 'pending'));
$pendingMeetings = count(array_filter($meetings, fn(array $meeting) => $meeting['status'] === 'pending'));
?>

<?php if ($flash): ?><div class="notice success"><?= e($flash) ?></div><?php endif; ?>

<section class="welcome">
    <div><p class="eyebrow">WELCOME</p><h2><?= e($mentor['name']) ?></h2><p>Manage your availability and assigned student meetings.</p></div>
    <div class="contact-card"><span class="avatar"><?= e(strtoupper(substr($mentor['name'], 0, 1))) ?></span><div><strong><?= e($mentor['name']) ?></strong><small><?= e($mentor['department']) ?> &middot; <?= e($mentor['email']) ?></small></div></div>
</section>

<section class="stats" data-dashboard>
    <article><small>Assigned students</small><b><?= count($students) ?></b><span>Your mapped students</span></article>
    <article><small>Pending approvals</small><b><?= $pendingRequests ?></b><span>Course requests</span></article>
    <article><small>Meeting requests</small><b><?= $pendingMeetings ?></b><span>Awaiting your review</span></article>
    <article><small>Available times</small><b><?= count(array_filter($availability, fn(array $slot) => (int) $slot['is_active'] === 1)) ?></b><span>Open slots</span></article>
</section>

<section class="panel" id="students">
    <div class="panel-title"><div><p class="eyebrow">MY COHORT</p><h2>Assigned students</h2></div><span class="badge"><?= count($students) ?> students</span></div>
    <div class="student-cards">
        <?php foreach ($students as $student): ?>
            <button type="button" class="student-card student-card-button" data-student-dialog="student-detail-<?= (int) $student['id'] ?>"><span class="avatar large"><?= e(strtoupper(substr($student['name'], 0, 1))) ?></span><span class="student-card-name"><?= e($student['name']) ?></span><span class="student-card-summary"><?= e($student['section'] ?: 'Section not set') ?> &middot; Semester <?= e($student['semester'] ?: 'Not set') ?></span><span class="student-card-email"><?= e($student['email']) ?></span><span class="student-card-hint">View full profile</span></button>
            <dialog class="student-detail-dialog" id="student-detail-<?= (int) $student['id'] ?>" aria-labelledby="student-detail-title-<?= (int) $student['id'] ?>">
                <form method="dialog" class="student-dialog-close"><button type="submit" class="secondary">Close</button></form>
                <div class="student-detail-heading"><span class="avatar large"><?= e(strtoupper(substr($student['name'], 0, 1))) ?></span><div><p class="eyebrow">ASSIGNED STUDENT</p><h2 id="student-detail-title-<?= (int) $student['id'] ?>"><?= e($student['name']) ?></h2><p><?= e($student['department'] ?: 'Department not set') ?></p></div></div>
                <div class="student-detail-grid">
                    <div><small>Username</small><strong><?= e($student['username']) ?></strong></div><div><small>Email</small><strong><?= e($student['email']) ?></strong></div>
                    <div><small>Section</small><strong><?= e($student['section'] ?: 'Not provided') ?></strong></div><div><small>Semester</small><strong><?= e($student['semester'] ?: 'Not provided') ?></strong></div>
                    <div><small>Date of birth</small><strong><?= !empty($student['dob']) ? e(date('d M Y', strtotime($student['dob']))) : 'Not provided' ?></strong></div>
                    <div><small>Attendance</small><strong><?= $student['attendance'] !== null ? number_format((float) $student['attendance'], 1) . '%' : 'Not recorded' ?></strong></div>
                    <div><small>CGPA</small><strong><?= $student['cgpa'] !== null ? number_format((float) $student['cgpa'], 2) : 'Not recorded' ?></strong></div>
                    <div><small>Completed credits</small><strong><?= $student['completed_credits'] !== null ? e($student['completed_credits']) : 'Not recorded' ?></strong></div>
                </div>
            </dialog>
        <?php endforeach; ?>
        <?php if (!$students): ?><p class="empty">You do not have any assigned students yet.</p><?php endif; ?>
    </div>
</section>

<section class="mentor-dashboard-sections">
    <div class="panel" id="requests"><p class="eyebrow">REVIEW QUEUE</p><h2>Subject requests</h2>
        <?php if (!$requests): ?><div class="meeting-empty"><strong>No course requests yet</strong><p>Requests from your assigned students will appear here.</p></div>
        <?php else: ?><div class="mentor-course-request-list">
            <?php foreach ($requests as $request): ?><article class="mentor-course-request"><div class="mentor-course-request-heading"><div><strong><?= e($request['student_name']) ?></strong><small><?= e($request['student_email']) ?> &middot; Semester <?= e($request['semester']) ?></small></div><span class="status <?= e($request['status']) ?>"><?= e($request['status']) ?></span></div><p class="mentor-course-request-total">Total: <strong><?= e($request['total_credits']) ?> credits</strong></p><div class="table-wrap mentor-course-table"><table><thead><tr><th>Code</th><th>Course</th><th>Credits</th></tr></thead><tbody><?php foreach ($request['courses'] as $course): ?><tr><td><?= e($course['code']) ?></td><td><?= e($course['name']) ?></td><td><?= e($course['credits']) ?></td></tr><?php endforeach; ?><?php if (!$request['courses']): ?><tr><td colspan="3" class="muted">No courses listed.</td></tr><?php endif; ?></tbody></table></div><div class="mentor-course-request-document"><?php if (is_valid_document_url($request['receipt_path'] ?? '')): ?><a href="<?= e($request['receipt_path']) ?>" target="_blank" rel="noopener noreferrer">Open supporting document</a><?php else: ?><span class="muted">No valid supporting document link</span><?php endif; ?></div>
                <?php if ($request['status'] === 'pending'): ?><form method="post" class="mentor-course-request-actions"><input type="hidden" name="action" value="update_course_request"><input type="hidden" name="request_id" value="<?= (int) $request['id'] ?>"><button type="submit" name="status" value="approved" <?= is_valid_document_url($request['receipt_path'] ?? '') ? '' : 'disabled title="A valid document link is required"' ?>>Approve</button><button class="secondary" type="submit" name="status" value="rejected">Deny</button></form>
                <?php elseif ($request['status'] === 'rejected'): ?><p class="mentor-course-request-note">Denied. The student can edit the selection or document link and send it for review again.</p><?php endif; ?></article><?php endforeach; ?>
        </div><?php endif; ?>
    </div>
    <div class="panel" id="availability"><p class="eyebrow">YOUR HOURS</p><h2>Set meeting availability</h2>
        <p class="muted">Students assigned to you can request an open time. A requested time is held until you approve or reject it.</p>
        <form method="post" class="stack"><input type="hidden" name="action" value="save_availability"><input type="hidden" name="availability_id" value="">
            <label>Start<input required type="datetime-local" name="starts_at"></label>
            <label>End<input required type="datetime-local" name="ends_at"></label>
            <label>Meeting mode<select name="mode"><option>Online</option><option>In person</option></select></label>
            <label>Description<textarea name="description" maxlength="500" placeholder="Add joining details or what students should prepare"></textarea></label>
            <button type="submit">Add available time</button>
        </form>

        <div class="availability-list"><h3>Manage your times</h3>
            <?php if (!$availability): ?><p class="empty">No availability times added yet.</p>
            <?php else: ?><div class="table-wrap"><table><thead><tr><th>Start</th><th>End</th><th>Mode</th><th>Description</th><th>Availability</th><th>Actions</th></tr></thead><tbody>
                <?php foreach ($availability as $slot): ?><tr><td><div class="availability-time"><span><?= date('D, d M Y', strtotime($slot['starts_at'])) ?></span><strong><?= date('h:i A', strtotime($slot['starts_at'])) ?></strong></div></td><td><div class="availability-time"><span><?= date('D, d M Y', strtotime($slot['ends_at'])) ?></span><strong><?= date('h:i A', strtotime($slot['ends_at'])) ?></strong></div></td><td><?= e($slot['mode']) ?></td><td><?= e($slot['description']) ?></td><td><span class="availability-state <?= $slot['meeting_status'] ? 'is-booked' : 'is-empty' ?>"><?= $slot['meeting_status'] ? 'Booked' : 'Empty' ?></span></td><td>
                    <?php if (!in_array($slot['meeting_status'], ['approved', 'confirmed'], true)): ?><button type="button" class="availability-edit-button" data-availability-dialog="availability-edit-<?= (int) $slot['id'] ?>">Edit</button><dialog class="availability-edit-dialog" id="availability-edit-<?= (int) $slot['id'] ?>" aria-labelledby="availability-title-<?= (int) $slot['id'] ?>"><form method="dialog" class="availability-dialog-close"><button type="submit" class="secondary">Close</button></form><p class="eyebrow">MENTOR AVAILABILITY</p><h3 id="availability-title-<?= (int) $slot['id'] ?>">Edit schedule</h3><?php if ($slot['meeting_status'] === 'pending'): ?><p class="availability-edit-note">Saving changes also updates the pending meeting request.</p><?php endif; ?><form method="post" class="stack"><input type="hidden" name="action" value="save_availability"><input type="hidden" name="availability_id" value="<?= (int) $slot['id'] ?>"><label>Start<input required type="datetime-local" name="starts_at" value="<?= e(date('Y-m-d\TH:i', strtotime($slot['starts_at']))) ?>"></label><label>End<input required type="datetime-local" name="ends_at" value="<?= e(date('Y-m-d\TH:i', strtotime($slot['ends_at']))) ?>"></label><label>Mode<select name="mode"><option <?= $slot['mode'] === 'Online' ? 'selected' : '' ?>>Online</option><option <?= $slot['mode'] === 'In person' ? 'selected' : '' ?>>In person</option></select></label><label>Description<textarea name="description" maxlength="500"><?= e($slot['description']) ?></textarea></label><button type="submit">Save changes</button></form><?php if (!$slot['meeting_status']): ?><form method="post" class="availability-toggle-form"><input type="hidden" name="action" value="toggle_availability"><input type="hidden" name="availability_id" value="<?= (int) $slot['id'] ?>"><input type="hidden" name="is_active" value="<?= (int) $slot['is_active'] ? 0 : 1 ?>"><button class="secondary" type="submit"><?= (int) $slot['is_active'] ? 'Close' : 'Reopen' ?> slot</button></form><?php elseif ($slot['meeting_status'] === 'pending'): ?><p class="meeting-status-note">This slot cannot be closed while its request is pending.</p><?php endif; ?></dialog><?php else: ?><span class="availability-locked" title="Approved and confirmed meetings cannot be rescheduled here.">Locked</span><?php endif; ?>
                </td></tr><?php endforeach; ?>
            </tbody></table></div><?php endif; ?>
        </div>
    </div>
</section>



<section class="panel meeting-review-panel" id="meetings"><div class="panel-title"><div><p class="eyebrow">MEETING INBOX</p><h2>Requested meetings</h2><p class="muted">Review requests from your assigned students.</p></div><span class="meeting-count"><?= $pendingMeetings ?> pending</span></div>
    <?php if (!$meetings): ?><div class="meeting-empty"><strong>No meeting requests yet</strong><p>Student requests will appear here when they book one of your open times.</p></div>
    <?php else: ?><div class="meeting-request-list">
        <?php foreach ($meetings as $meeting): ?><article class="mentor-meeting-card">
            <div class="meeting-request-top"><div><strong><?= e($meeting['student_name']) ?></strong><span class="meeting-date"><?= date('D, d M Y', strtotime($meeting['meeting_at'])) ?> at <?= date('h:i A', strtotime($meeting['meeting_at'])) ?></span></div><span class="status <?= e($meeting['status']) ?>"><?= e($meeting['status']) ?></span></div>
            <div class="meeting-request-meta"><span><?= e($meeting['mode']) ?></span><span><?= e($meeting['student_email']) ?></span></div>
            <?php if ($meeting['notes']): ?><p class="meeting-description"><strong>Student note:</strong> <?= e($meeting['notes']) ?></p><?php endif; ?>
            <?php if ($meeting['status'] === 'pending'): ?><form method="post" class="meeting-review-actions"><input type="hidden" name="meeting_id" value="<?= (int) $meeting['id'] ?>"><button name="action" value="approve_meeting">Approve request</button><button class="secondary" name="action" value="reject_meeting">Decline</button></form><?php elseif ($meeting['status'] === 'approved'): ?><p class="meeting-status-note">Approved. Waiting for student confirmation.</p><?php elseif ($meeting['status'] === 'confirmed'): ?><p class="meeting-status-note">This meeting is confirmed.</p><?php else: ?><p class="meeting-status-note">This request was declined.</p><?php endif; ?>
        </article><?php endforeach; ?>
    </div><?php endif; ?>
</section>

<section class="panel" id="announcements" data-dashboard><div class="panel-title"><div><p class="eyebrow">STUDENT DASHBOARD</p><h2>Announcements</h2><p class="muted">Posts are visible to students currently assigned to you.</p></div></div>
    <form method="post" class="stack announcement-form"><input type="hidden" name="action" value="create_announcement"><label>Title<input required maxlength="120" name="title" placeholder="For example, schedule update"></label><label>Message<textarea required maxlength="3000" name="message" placeholder="Write an update for your assigned students"></textarea></label><button type="submit">Publish announcement</button></form>
    <h3 class="announcement-list-title">Published announcements</h3>
    <?php if (!$announcements): ?><p class="empty">You have not published any announcements.</p>
    <?php else: ?><div class="announcement-list"><?php foreach ($announcements as $announcement): ?><article class="announcement-card"><div class="announcement-card-heading"><div><h3><?= e($announcement['title']) ?></h3><small><?= date('d M Y, h:i A', strtotime($announcement['created_at'])) ?></small></div><form method="post"><input type="hidden" name="action" value="delete_announcement"><input type="hidden" name="announcement_id" value="<?= (int) $announcement['id'] ?>"><button class="secondary" type="submit">Remove</button></form></div><p><?= nl2br(e($announcement['message'])) ?></p></article><?php endforeach; ?></div><?php endif; ?>
</section>

<script>
document.querySelectorAll('[data-student-dialog]').forEach((button) => {
    button.addEventListener('click', () => document.getElementById(button.dataset.studentDialog)?.showModal());
});
document.querySelectorAll('[data-availability-dialog]').forEach((button) => {
    button.addEventListener('click', () => document.getElementById(button.dataset.availabilityDialog)?.showModal());
});
</script>
<?php page_end(); ?>

<?php
function handle_mentor_action(): string
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') return '';
    return match ($_POST['action'] ?? '') {
        'save_availability' => save_mentor_availability(),
        'toggle_availability' => toggle_mentor_availability(),
        'approve_meeting' => update_mentor_meeting('approved'),
        'reject_meeting' => update_mentor_meeting('rejected'),
        'update_course_request' => update_mentor_course_request(),
        'create_announcement' => create_mentor_announcement(),
        'delete_announcement' => delete_mentor_announcement(),
        default => '',
    };
}
function current_mentor_id(): int { return (int) ($_SESSION['user_id'] ?? 0); }
function get_mentor(int $id): array { $query = db()->prepare("SELECT * FROM users WHERE id = ? AND role = 'mentor'"); $query->execute([$id]); return $query->fetch() ?: []; }
function get_assigned_students(int $id): array { $query = db()->prepare('SELECT s.*, a.id AS assignment_id, st.attendance, st.cgpa, st.completed_credits FROM mentor_assignments a JOIN users s ON s.id = a.student_id LEFT JOIN students st ON st.user_id = s.id WHERE a.mentor_id = ? ORDER BY s.name'); $query->execute([$id]); return $query->fetchAll(); }
function get_mentor_requests(int $id): array
{
    $query = db()->prepare("SELECT r.*, u.name AS student_name, u.email AS student_email FROM course_requests r JOIN users u ON u.id = r.student_id JOIN mentor_assignments a ON a.student_id = u.id WHERE a.mentor_id = ? ORDER BY (r.status = 'pending') DESC, r.created_at DESC");
    $query->execute([$id]);
    $requests = $query->fetchAll();
    if (!$requests) return [];

    $requestIds = array_map('intval', array_column($requests, 'id'));
    $placeholders = implode(',', array_fill(0, count($requestIds), '?'));
    $courseQuery = db()->prepare("SELECT rc.request_id, c.code, c.name, c.credits FROM request_courses rc JOIN courses c ON c.id = rc.course_id WHERE rc.request_id IN ($placeholders) ORDER BY c.type, c.code");
    $courseQuery->execute($requestIds);
    $coursesByRequest = [];
    foreach ($courseQuery->fetchAll() as $course) $coursesByRequest[(int) $course['request_id']][] = $course;
    foreach ($requests as &$request) $request['courses'] = $coursesByRequest[(int) $request['id']] ?? [];
    unset($request);
    return $requests;
}
function get_mentor_meetings(int $id): array { $query = db()->prepare('SELECT mt.*, u.name AS student_name, u.email AS student_email FROM meetings mt JOIN users u ON u.id = mt.student_id WHERE mt.mentor_id = ? ORDER BY (mt.status = \'pending\') DESC, mt.meeting_at DESC'); $query->execute([$id]); return $query->fetchAll(); }
function get_mentor_availability(int $id): array { $query = db()->prepare("SELECT av.*, (SELECT mt.status FROM meetings mt WHERE mt.availability_id = av.id AND mt.status IN ('pending','approved','confirmed') ORDER BY FIELD(mt.status, 'confirmed', 'approved', 'pending') LIMIT 1) AS meeting_status FROM mentor_availability av WHERE av.mentor_id = ? ORDER BY av.starts_at DESC"); $query->execute([$id]); return $query->fetchAll(); }
function availability_datetime(string $key): string
{
    $value = trim($_POST[$key] ?? '');
    $date = DateTimeImmutable::createFromFormat('!Y-m-d\TH:i', $value);
    $errors = DateTimeImmutable::getLastErrors();
    if (!$date || $date->format('Y-m-d\TH:i') !== $value || ($errors && ($errors['warning_count'] || $errors['error_count']))) throw new Exception('Enter a valid start and end time.');
    return $date->format('Y-m-d H:i:00');
}
function save_mentor_availability(): string
{
    $mentorId = current_mentor_id();
    $id = filter_var($_POST['availability_id'] ?? null, FILTER_VALIDATE_INT) ?: 0;
    $start = availability_datetime('starts_at');
    $end = availability_datetime('ends_at');
    $mode = $_POST['mode'] ?? '';
    $description = trim($_POST['description'] ?? '');
    if (strtotime($start) <= time() || strtotime($end) <= strtotime($start)) throw new Exception('Choose a future time with an end after its start.');
    if (!in_array($mode, ['Online', 'In person'], true)) throw new Exception('Choose a valid meeting mode.');
    if (strlen($description) > 500) throw new Exception('Description must be 500 characters or fewer.');
    if ($id) {
        db()->beginTransaction();
        try {
            $owned = db()->prepare('SELECT id FROM mentor_availability WHERE id = ? AND mentor_id = ? FOR UPDATE');
            $owned->execute([$id, $mentorId]);
            if (!$owned->fetch()) throw new Exception('Availability time was not found.');
            $bookings = db()->prepare("SELECT status FROM meetings WHERE availability_id = ? AND mentor_id = ? AND status IN ('pending','approved','confirmed') FOR UPDATE");
            $bookings->execute([$id, $mentorId]);
            $bookingStatuses = $bookings->fetchAll(PDO::FETCH_COLUMN);
            if (array_intersect($bookingStatuses, ['approved', 'confirmed'])) throw new Exception('Approved or confirmed meeting times cannot be edited.');
            $overlap = db()->prepare('SELECT COUNT(*) FROM mentor_availability WHERE mentor_id = ? AND (? < ends_at AND ? > starts_at) AND id <> ?');
            $overlap->execute([$mentorId, $start, $end, $id]);
            if ((int) $overlap->fetchColumn()) throw new Exception('This time overlaps another availability slot.');
            db()->prepare('UPDATE mentor_availability SET starts_at = ?, ends_at = ?, mode = ?, description = ? WHERE id = ? AND mentor_id = ?')->execute([$start, $end, $mode, $description, $id, $mentorId]);
            db()->prepare("UPDATE meetings SET meeting_at = ?, mode = ? WHERE availability_id = ? AND mentor_id = ? AND status = 'pending'")->execute([$start, $mode, $id, $mentorId]);
            db()->commit();
        } catch (Throwable $exception) { db()->rollBack(); throw $exception; }
        return 'Availability time updated.';
    }
    $overlap = db()->prepare('SELECT COUNT(*) FROM mentor_availability WHERE mentor_id = ? AND (? < ends_at AND ? > starts_at)');
    $overlap->execute([$mentorId, $start, $end]);
    if ((int) $overlap->fetchColumn()) throw new Exception('This time overlaps another availability slot.');
    db()->prepare('INSERT INTO mentor_availability (mentor_id, starts_at, ends_at, mode, description) VALUES (?, ?, ?, ?, ?)')->execute([$mentorId, $start, $end, $mode, $description]);
    return 'Availability time added.';
}
function toggle_mentor_availability(): string
{
    $id = (int) ($_POST['availability_id'] ?? 0);
    $active = (int) ($_POST['is_active'] ?? 0) === 1 ? 1 : 0;
    $booked = db()->prepare("SELECT COUNT(*) FROM meetings mt JOIN mentor_availability av ON av.id = mt.availability_id WHERE av.id = ? AND av.mentor_id = ? AND mt.status IN ('pending','approved','confirmed')");
    $booked->execute([$id, current_mentor_id()]);
    if ((int) $booked->fetchColumn()) throw new Exception('A time with a meeting request cannot be closed.');
    $update = db()->prepare('UPDATE mentor_availability SET is_active = ? WHERE id = ? AND mentor_id = ?');
    $update->execute([$active, $id, current_mentor_id()]);
    if (!$update->rowCount()) throw new Exception('Availability time was not found or was unchanged.');
    return $active ? 'Availability time reopened.' : 'Availability time closed.';
}

function get_mentor_announcements(int $id): array { $query = db()->prepare('SELECT * FROM announcements WHERE mentor_id = ? ORDER BY created_at DESC'); $query->execute([$id]); return $query->fetchAll(); }
function create_mentor_announcement(): string
{
    $title = trim($_POST['title'] ?? '');
    $message = trim($_POST['message'] ?? '');
    if ($title === '' || strlen($title) > 120) throw new Exception('Enter an announcement title up to 120 characters.');
    if ($message === '' || strlen($message) > 3000) throw new Exception('Enter an announcement message up to 3000 characters.');
    if (!get_assigned_students(current_mentor_id())) throw new Exception('You need an assigned student before publishing an announcement.');
    db()->prepare('INSERT INTO announcements (mentor_id, title, message) VALUES (?, ?, ?)')->execute([current_mentor_id(), $title, $message]);
    return 'Announcement published to your assigned students.';
}
function delete_mentor_announcement(): string
{
    $query = db()->prepare('DELETE FROM announcements WHERE id = ? AND mentor_id = ?');
    $query->execute([(int) ($_POST['announcement_id'] ?? 0), current_mentor_id()]);
    if (!$query->rowCount()) throw new Exception('Announcement was not found.');
    return 'Announcement removed.';
}
function update_mentor_meeting(string $status): string
{
    $query = db()->prepare("UPDATE meetings mt JOIN mentor_assignments ma ON ma.student_id = mt.student_id AND ma.mentor_id = mt.mentor_id SET mt.status = ? WHERE mt.id = ? AND mt.mentor_id = ? AND mt.status = 'pending'");
    $query->execute([$status, (int) ($_POST['meeting_id'] ?? 0), current_mentor_id()]);
    if (!$query->rowCount()) throw new Exception('This pending meeting request was not found in your assigned students.');
    return $status === 'approved' ? 'Meeting approved. The student can now confirm it.' : 'Meeting request rejected.';
}
function update_mentor_course_request(): string
{
    $requestId = filter_var($_POST['request_id'] ?? null, FILTER_VALIDATE_INT);
    $status = $_POST['status'] ?? '';
    if (!$requestId || !in_array($status, ['approved', 'rejected'], true)) throw new Exception('Choose a valid course request decision.');

    db()->beginTransaction();
    try {
        $requestQuery = db()->prepare("SELECT cr.status, cr.receipt_path FROM course_requests cr JOIN mentor_assignments ma ON ma.student_id = cr.student_id WHERE cr.id = ? AND ma.mentor_id = ? FOR UPDATE");
        $requestQuery->execute([$requestId, current_mentor_id()]);
        $request = $requestQuery->fetch();
        if (!$request) throw new Exception('This course request does not belong to one of your assigned students.');
        if ($request['status'] !== 'pending') throw new Exception('Only pending course requests can be approved or denied.');
        if ($status === 'approved' && !is_valid_document_url($request['receipt_path'] ?? '')) throw new Exception('This request cannot be approved without a valid supporting document link.');

        db()->prepare("UPDATE course_requests SET status = ? WHERE id = ? AND status = 'pending'")->execute([$status, $requestId]);
        db()->commit();
    } catch (Throwable $exception) { db()->rollBack(); throw $exception; }

    return $status === 'approved' ? 'Course selection approved.' : 'Course selection denied. The student can edit and submit it again.';
}
