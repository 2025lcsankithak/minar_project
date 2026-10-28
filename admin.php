<?php
require_once __DIR__ . '/auth.php';
require_role('admin');
require_once __DIR__ . '/layout.php';

$view = $_GET['view'] ?? 'dashboard';
$allowedViews = ['dashboard', 'people', 'courses', 'requests', 'assignments'];
if (!in_array($view, $allowedViews, true)) $view = 'dashboard';

try {
    $flash = handle_admin_action();
    $data = get_admin_data();
} catch (Throwable $exception) {
    page_start('Admin dashboard', 'Admin');
    echo '<div class="notice warning">' . e(user_error_message($exception)) . '</div>';
    page_end();
    exit;
}

page_start($view === 'dashboard' ? 'Admin dashboard' : ucfirst($view), 'Admin', $view);
?>

<?php if ($flash): ?>
    <div class="notice success"><?= e($flash) ?></div>
<?php endif; ?>

<?php if ($view === 'dashboard'): ?>
    <section class="stats">
        <article><small>Total students</small><b><?= $data['student_count'] ?></b><span>Across all sections</span></article>
        <article><small>Active mentors</small><b><?= $data['mentor_count'] ?></b><span>Available to assign</span></article>
        <article><small>Pending requests</small><b><?= $data['pending_count'] ?></b><span>Require review</span></article>
        <article><small>Courses offered</small><b><?= $data['course_count'] ?></b><span>Current catalog</span></article>
    </section>

    <section class="split">
        <div class="panel">
            <p class="eyebrow">NEEDS ATTENTION</p>
            <h2>Pending course requests</h2>
            <?php foreach (array_slice($data['pending_requests'], 0, 3) as $request): ?>
                <div class="request-row">
                    <div><strong><?= e($request['student_name']) ?></strong><small><?= e($request['subjects']) ?></small></div>
                    <span class="status pending">pending</span>
                </div>
            <?php endforeach; ?>
            <?php if (!$data['pending_requests']): ?><p class="empty">No pending requests.</p><?php endif; ?>
            <a class="button secondary" href="<?= BASE_URL ?>/admin.php?view=requests">Review requests</a>
        </div>
        <div class="panel">
            <p class="eyebrow">QUICK ACTIONS</p>
            <h2>Keep the portal organised</h2>
            <p class="muted"><?= count($data['unassigned']) ?> student(s) still need a mentor assignment.</p>
            <a class="button" href="<?= BASE_URL ?>/admin.php?view=assignments">Manage assignments</a>
            <a class="button secondary" href="<?= BASE_URL ?>/admin.php?view=courses">Manage courses</a>
        </div>
    </section>
<?php endif; ?>

<?php if ($view === 'requests'): ?>
    <section class="panel">
        <div class="panel-title"><div><p class="eyebrow">COURSE APPROVALS</p><h2>Student requests</h2></div><span class="badge"><?= count($data['requests']) ?> total</span></div>
        <div class="table-wrap"><table>
            <thead><tr><th>Student</th><th>Semester</th><th>Subjects</th><th>Credits</th><th>Document</th><th>Status</th><th>Decision</th></tr></thead>
            <tbody><?php foreach ($data['requests'] as $request): ?><tr>
                <td><strong><?= e($request['student_name']) ?></strong><small><?= e($request['section']) ?></small></td>
                <td><?= e($request['semester']) ?></td><td><?= e($request['subjects']) ?></td><td><?= $request['total_credits'] ?></td>
                <td><?php if (is_valid_document_url($request['receipt_path'] ?? '')): ?><a target="_blank" rel="noopener noreferrer" href="<?= e($request['receipt_path']) ?>">Open document</a><?php else: ?><span class="muted">Missing valid document link</span><?php endif; ?></td>
                <td><span class="status <?= e($request['status']) ?>"><?= e($request['status']) ?></span></td>
                <td><form method="post" class="inline"><?= csrf_field() ?><input type="hidden" name="action" value="request"><input type="hidden" name="id" value="<?= $request['id'] ?>"><button name="status" value="approved">Approve</button><button name="status" value="rejected">Reject</button><button name="status" value="pending">Pending</button></form></td>
            </tr><?php endforeach; ?></tbody>
        </table></div>
    </section>
<?php endif; ?>

<?php if ($view === 'assignments'): ?>
    <section class="split">
        <div class="panel"><p class="eyebrow">PENDING ALLOCATION</p><h2>Assign mentors</h2>
            <?php foreach ($data['unassigned'] as $student): ?>
                <?php $matchingMentors = array_filter($data['mentors'], fn($mentor) => mentor_matches_student($mentor, $student)); ?>
                <form method="post" class="inline" style="margin-bottom:10px"><?= csrf_field() ?><input type="hidden" name="action" value="assign"><input type="hidden" name="student_id" value="<?= $student['id'] ?>"><span class="assignment-student"><strong><?= e($student['name']) ?></strong><small>( <?= e($student['semester']) ?>th Sem <?= e($student['section']) ?> )</small></span>
                    <?php if ($matchingMentors): ?><select required name="mentor_id"><?php foreach ($matchingMentors as $mentor): ?><option value="<?= $mentor['id'] ?>"><?= e($mentor['name']) ?> - <?= e($mentor['section']) ?> - Sem <?= e($mentor['semester']) ?></option><?php endforeach; ?></select><button>Assign</button>
                    <?php else: ?><span class="muted">No mentor matches this section and semester. Set a mentor's section and semester in People.</span><?php endif; ?></form>
            <?php endforeach; ?>
            <?php if (!$data['unassigned']): ?><p class="empty">All students have a mentor.</p><?php endif; ?>
        </div>
        <div class="panel"><p class="eyebrow">CURRENT ALLOCATION</p><h2>Assigned mentors</h2>
            <?php foreach ($data['assigned'] as $student): ?><div class="request-row"><div><strong><?= e($student['name']) ?></strong><small><?= e($student['section']) ?> - Semester <?= e($student['semester']) ?></small></div><span class="badge"><?= e($student['mentor_name']) ?></span></div><?php endforeach; ?>
        </div>
    </section>
<?php endif; ?>

<?php if ($view === 'courses'): ?>
    <section class="panel"><p class="eyebrow">CATALOG</p><h2>Add or upload courses</h2>
        <form method="post" class="form-grid"><?= csrf_field() ?><input type="hidden" name="action" value="course"><label>Code<input required name="code"></label><label>Course title<input required name="name"></label><label>Credits<input required min="1" max="10" type="number" name="credits" value="3"></label><label>Semester<select name="semester"><?php semester_options(5); ?></select></label><label>Type<select name="type"><option>Core</option><option>Open Elective</option></select></label><button>Add course</button></form>
        <form method="post" enctype="multipart/form-data" class="stack" style="margin-top:18px"><?= csrf_field() ?><input type="hidden" name="action" value="course_upload"><label>Upload CSV (code, name, credits, semester, type)<input required type="file" name="course_file" accept=".csv"></label><button>Upload courses</button></form>
    </section>
    <section class="panel"><h2>Course list</h2><div class="table-wrap"><table>
        <thead><tr><th>Code</th><th>Course</th><th>Credits</th><th>Semester</th><th>Type</th><th>Edit</th></tr></thead>
        <tbody><?php foreach ($data['courses'] as $course): ?><tr>
            <td><?= e($course['code']) ?></td><td><?= e($course['name']) ?></td><td><?= $course['credits'] ?></td><td><?= $course['semester'] ?></td><td><?= e($course['type']) ?></td>
            <td><details><summary>Edit</summary><form method="post" class="stack"><?= csrf_field() ?><input type="hidden" name="action" value="update_course"><input type="hidden" name="id" value="<?= $course['id'] ?>"><input name="code" value="<?= e($course['code']) ?>"><input name="name" value="<?= e($course['name']) ?>"><input min="1" max="10" type="number" name="credits" value="<?= $course['credits'] ?>"><select name="semester"><?php semester_options((int) $course['semester']); ?></select><select name="type"><option <?= $course['type'] === 'Core' ? 'selected' : '' ?>>Core</option><option <?= $course['type'] === 'Open Elective' ? 'selected' : '' ?>>Open Elective</option></select><button>Save</button></form></details></td>
        </tr><?php endforeach; ?></tbody>
    </table></div></section>
<?php endif; ?>

<?php if ($view === 'people'): ?>
    <section class="panel"><p class="eyebrow">NEW ACCOUNT</p><h2>Create a student or mentor account</h2>
        <form method="post" class="form-grid"><?= csrf_field() ?><input type="hidden" name="action" value="create_user">
            <label>Role<select name="role" required><option value="student">Student</option><option value="mentor">Mentor</option></select></label>
            <label>Full name<input required name="name" autocomplete="name"></label>
            <label>Login username<input required name="username" autocomplete="username"></label>
            <label>Login email<input required type="email" name="email" autocomplete="email"></label>
            <label>Password<input required type="password" name="password" minlength="8" autocomplete="new-password"></label>
            <label>Department<input required name="department"></label>
            <label>Section<input required name="section" placeholder="e.g. 5th A"></label>
            <label>Semester<select required name="semester"><?php semester_options(1); ?></select></label>
            <button>Create account</button>
        </form>
    </section>
    <section class="panel"><div class="panel-title"><div><p class="eyebrow">DIRECTORY</p><h2>Users</h2></div><span class="badge"><?= count($data['users']) ?> users</span></div><div class="table-wrap"><table>
        <thead><tr><th>Name</th><th>Role</th><th>Username</th><th>Email</th><th>Section / Department</th><th>Mentor</th><th>Actions</th></tr></thead>
        <tbody><?php foreach ($data['users'] as $user): ?><tr>
            <td><strong><?= e($user['name']) ?></strong></td><td><?= e($user['role']) ?></td><td><?= e($user['username']) ?></td><td><?= e($user['email']) ?></td><td><?= e($user['section'] ?: $user['department']) ?></td><td><?= e($user['role'] === 'student' ? ($user['mentor_name'] ?: 'Pending') : '-') ?></td>
            <td><details><summary>Edit</summary><form method="post" class="stack"><?= csrf_field() ?><input type="hidden" name="action" value="update_user"><input type="hidden" name="id" value="<?= $user['id'] ?>"><input required name="name" value="<?= e($user['name']) ?>"><input required type="email" name="email" value="<?= e($user['email']) ?>"><input name="department" value="<?= e($user['department']) ?>" placeholder="Department"><input name="section" value="<?= e($user['section']) ?>" placeholder="Section"><select name="semester"><option value="">No semester</option><?php semester_options((int) $user['semester']); ?></select><button>Save</button></form></details>
                <?php if ($user['role'] !== 'admin'): ?><form method="post" onsubmit="return confirm('Delete this user and their related records?')" style="margin-top:8px"><?= csrf_field() ?><input type="hidden" name="action" value="delete_user"><input type="hidden" name="id" value="<?= $user['id'] ?>"><button class="secondary" type="submit">Delete user</button></form><?php endif; ?></td>
        </tr><?php endforeach; ?></tbody>
    </table></div></section>
<?php endif; ?>

<?php page_end(); ?>

<?php
function semester_options(int $selected = 0): void
{
    for ($semester = 1; $semester <= 8; $semester++) {
        $isSelected = $semester === $selected ? ' selected' : '';
        echo '<option value="' . $semester . '"' . $isSelected . '>' . $semester . '</option>';
    }
}

function valid_semester(int $semester): int
{
    if ($semester < 1 || $semester > 8) {
        throw new Exception('Semester must be between 1 and 8.');
    }
    return $semester;
}

function handle_admin_action(): string
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') return '';
    verify_csrf_token();
    return match ($_POST['action'] ?? '') {
        'course' => add_course(), 'update_course' => edit_course(), 'course_upload' => upload_courses(),
        'assign' => assign_mentor(), 'request' => update_request(), 'update_user' => edit_user(), 'create_user' => create_user(), 'delete_user' => delete_user(), default => '',
    };
}

function create_user(): string
{
    $role = $_POST['role'] ?? '';
    if (!in_array($role, ['student', 'mentor'], true)) throw new Exception('Choose a student or mentor role.');

    $name = trim($_POST['name'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $department = trim($_POST['department'] ?? '');
    $section = trim($_POST['section'] ?? '');
    if ($name === '' || $username === '' || $department === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception('Enter a name, username, valid email, and department.');
    }
    if (strlen($password) < 8) throw new Exception('Password must be at least 8 characters.');
    $semester = valid_semester((int) ($_POST['semester'] ?? 0));
    if ($section === '') throw new Exception('Enter a section for this account.');

    try {
        db()->prepare('INSERT INTO users(role,name,username,email,password,department,section,semester) VALUES(?,?,?,?,?,?,?,?)')->execute([
            $role, $name, $username, $email, password_hash($password, PASSWORD_DEFAULT), $department,
            $section, $semester,
        ]);
    } catch (mysqli_sql_exception $exception) {
        if ($exception->getCode() === '23000') throw new Exception('That username or email is already in use.');
        throw $exception;
    }
    return ucfirst($role) . ' account created.';
}

function add_course(): string
{
    db()->prepare('INSERT INTO courses(code,name,credits,semester,type) VALUES(?,?,?,?,?)')->execute([
        trim($_POST['code']), trim($_POST['name']), (int) $_POST['credits'], valid_semester((int) $_POST['semester']), $_POST['type'],
    ]);
    return 'Course added.';
}

function edit_course(): string
{
    db()->prepare('UPDATE courses SET code=?,name=?,credits=?,semester=?,type=? WHERE id=?')->execute([
        trim($_POST['code']), trim($_POST['name']), (int) $_POST['credits'], valid_semester((int) $_POST['semester']), $_POST['type'], (int) $_POST['id'],
    ]);
    return 'Course updated.';
}

function edit_user(): string
{
    $semester = $_POST['semester'] === '' ? null : valid_semester((int) $_POST['semester']);
    $userId = (int) $_POST['id'];
    $roleQuery = db()->prepare('SELECT role FROM users WHERE id=?');
    $roleQuery->execute([$userId]);
    $role = $roleQuery->fetchColumn();
    if (in_array($role, ['student', 'mentor'], true) && (trim($_POST['section'] ?? '') === '' || $semester === null)) {
        throw new Exception('Students and mentors need both a section and semester.');
    }
    db()->prepare('UPDATE users SET name=?,email=?,department=?,section=?,semester=? WHERE id=?')->execute([
        trim($_POST['name']), trim($_POST['email']), trim($_POST['department']) ?: null,
        trim($_POST['section']) ?: null, $semester, $userId,
    ]);
    return 'User updated.';
}

function delete_user(): string
{
    $userId = (int) ($_POST['id'] ?? 0);
    $statement = db()->prepare("DELETE FROM users WHERE id=? AND role <> 'admin'");
    $statement->execute([$userId]);
    if (!$statement->rowCount()) throw new Exception('User not found or the admin account cannot be deleted.');
    return 'User deleted.';
}

function assign_mentor(): string
{
    $studentQuery = db()->prepare("SELECT * FROM users WHERE id=? AND role='student'");
    $studentQuery->execute([(int) ($_POST['student_id'] ?? 0)]);
    $student = $studentQuery->fetch();
    $mentorQuery = db()->prepare("SELECT * FROM users WHERE id=? AND role='mentor'");
    $mentorQuery->execute([(int) ($_POST['mentor_id'] ?? 0)]);
    $mentor = $mentorQuery->fetch();
    if (!$student || !$mentor || !mentor_matches_student($mentor, $student)) {
        throw new Exception('The mentor must match the student\'s section and semester.');
    }
    db()->prepare('INSERT INTO mentor_assignments(student_id,mentor_id) VALUES(?,?) ON DUPLICATE KEY UPDATE mentor_id=VALUES(mentor_id)')->execute([(int) $student['id'], (int) $mentor['id']]);
    return 'Mentor assigned.';
}

function mentor_matches_student(array $mentor, array $student): bool
{
    return (int) ($mentor['semester'] ?? 0) === (int) ($student['semester'] ?? 0)
        && strtolower(trim((string) ($mentor['section'] ?? ''))) === strtolower(trim((string) ($student['section'] ?? '')));
}

function update_request(): string
{
    $status = $_POST['status'] ?? '';
    if (!in_array($status, ['approved', 'rejected', 'pending'], true)) throw new Exception('Invalid status.');
    $requestId = (int) ($_POST['id'] ?? 0);
    if ($status === 'approved') {
        $documentQuery = db()->prepare('SELECT receipt_path FROM course_requests WHERE id = ?');
        $documentQuery->execute([$requestId]);
        if (!is_valid_document_url($documentQuery->fetchColumn() ?: '')) throw new Exception('This request cannot be approved without a valid document link.');
    }
    db()->prepare('UPDATE course_requests SET status=? WHERE id=?')->execute([$status, $requestId]);
    return 'Request updated.';
}
function upload_courses(): string
{
    if (empty($_FILES['course_file']['tmp_name']) || $_FILES['course_file']['error'] !== UPLOAD_ERR_OK) {
        throw new Exception('Choose a CSV file.');
    }

    $file = fopen($_FILES['course_file']['tmp_name'], 'r');
    if (!$file) throw new Exception('The uploaded CSV could not be opened.');

    $query = db()->prepare('INSERT INTO courses(code,name,credits,semester,type) VALUES(?,?,?,?,?) ON DUPLICATE KEY UPDATE name=VALUES(name),credits=VALUES(credits),semester=VALUES(semester),type=VALUES(type)');
    $count = 0;
    try {
        while (($row = fgetcsv($file)) !== false) {
            if (count($row) < 5 || strtolower(trim($row[0])) === 'code') continue;
            $query->execute([
                trim($row[0]), trim($row[1]), (int) $row[2], valid_semester((int) $row[3]),
                trim($row[4]) === 'Open Elective' ? 'Open Elective' : 'Core',
            ]);
            $count++;
        }
    } finally {
        fclose($file);
    }

    if (!$count) throw new Exception('No valid course rows found.');
    return "$count course(s) uploaded.";
}

function get_admin_data(): array
{
    $students = db()->query("SELECT s.*,m.name mentor_name FROM users s LEFT JOIN mentor_assignments a ON a.student_id=s.id LEFT JOIN users m ON m.id=a.mentor_id WHERE s.role='student' ORDER BY s.section,s.name")->fetchAll();
    $requests = db()->query("SELECT r.*,u.name student_name,u.section,GROUP_CONCAT(c.code SEPARATOR ', ') subjects FROM course_requests r JOIN users u ON u.id=r.student_id LEFT JOIN request_courses rc ON rc.request_id=r.id LEFT JOIN courses c ON c.id=rc.course_id GROUP BY r.id ORDER BY r.created_at DESC")->fetchAll();

    return [
        'student_count' => countOf('users', 'student'),
        'mentor_count' => countOf('users', 'mentor'),
        'pending_count' => countOf('course_requests', 'pending'),
        'course_count' => countOf('courses'),
        'mentors' => db()->query("SELECT * FROM users WHERE role='mentor' ORDER BY name")->fetchAll(),
        'unassigned' => array_filter($students, fn($student) => !$student['mentor_name']),
        'assigned' => array_filter($students, fn($student) => $student['mentor_name']),
        'courses' => db()->query('SELECT * FROM courses ORDER BY semester,code')->fetchAll(),
        'users' => db()->query("SELECT u.*,m.name mentor_name FROM users u LEFT JOIN mentor_assignments a ON a.student_id=u.id LEFT JOIN users m ON m.id=a.mentor_id ORDER BY u.role,u.name")->fetchAll(),
        'requests' => $requests,
        'pending_requests' => array_filter($requests, fn($request) => $request['status'] === 'pending'),
    ];
}
