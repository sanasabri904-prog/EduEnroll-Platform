<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';
requireAdmin(); // only admins can edit enrollments

$db = getDB();
$id = (int) ($_GET['id'] ?? 0);

// fetch enrollment by id
$stmt = $db->prepare('SELECT * FROM enrollments WHERE id = ?');
$stmt->execute([$id]);
$enrollment = $stmt->fetch();
if (!$enrollment) {
    setFlash('error', 'Enrollment not found.');
    header('Location: enrollments.php');
    exit;
}

// fetch courses for dropdown
$courses = $db->query('SELECT id, course_code, title, capacity FROM courses ORDER BY course_code')->fetchAll();

$errors = [];
$old = $enrollment; // pre-fill form with existing values

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();

     // collect form inputs
    $old['student_name'] = trim($_POST['student_name'] ?? '');
    $old['student_email'] = trim($_POST['student_email'] ?? '');
    $old['course_id'] = (int) ($_POST['course_id'] ?? 0);
    $old['enrollment_date'] = trim($_POST['enrollment_date'] ?? '');
    $old['notes'] = trim($_POST['notes'] ?? '');

     // basic validation
    if ($old['student_name'] === '')
        $errors['student_name'] = 'Student name is required.';
    if ($old['student_email'] === '')
        $errors['student_email'] = 'Email is required.';
    elseif (!filter_var($old['student_email'], FILTER_VALIDATE_EMAIL))
        $errors['student_email'] = 'Enter a valid email.';
    if ($old['course_id'] === 0)
        $errors['course_id'] = 'Please select a course.';

    if (empty($errors)) {
        // Check duplicate (exclude self)
        $dup = $db->prepare('SELECT id FROM enrollments WHERE student_email = ? AND course_id = ? AND id != ?');
        $dup->execute([$old['student_email'], $old['course_id'], $id]);
        if ($dup->fetch()) {
            $errors['student_email'] = 'This student is already enrolled in that course.';
        } else {
            // Check capacity only if course changed
            if ($old['course_id'] != $enrollment['course_id']) {
                $capStmt = $db->prepare('SELECT capacity FROM courses WHERE id = ?');
                $capStmt->execute([$old['course_id']]);
                $cap = $capStmt->fetchColumn();

                $cntStmt = $db->prepare('SELECT COUNT(*) FROM enrollments WHERE course_id = ?');
                $cntStmt->execute([$old['course_id']]);
                $cnt = $cntStmt->fetchColumn();

                if ($cnt >= $cap) {
                    $errors['course_id'] = 'That course has reached maximum capacity (' . $cap . ').';
                }
            }

              // update enrollment if still valid
            if (empty($errors)) {
                $db->prepare(
                        'UPDATE enrollments SET student_name=?, student_email=?, course_id=?, enrollment_date=?, notes=? WHERE id=?'
                )->execute([$old['student_name'], $old['student_email'], $old['course_id'],
                    $old['enrollment_date'], $old['notes'], $id]);
                setFlash('success', 'Enrollment updated successfully!');
                header('Location: enrollments.php');
                exit;
            }
        }
    }
}

$activePage = 'enrollments';
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8"/>
        <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
        <title>Edit Enrollment — EduEnroll</title>
        <?php include 'includes/page_styles.php'; ?>
    </head>
    <body>

<?php include 'includes/navbar.php'; ?>

        <div class="wrap">
            <div class="page-header">
                <div>
                    <h1>Edit Enrollment</h1>
                    <p>Editing record #<?= (int) $id ?></p>
                </div>
                <a href="enrollments.php" class="btn btn-outline">← Back</a>
            </div>

            <div class="card form-card">
                <div class="card-body">
                    <form method="POST">
<?php csrfField(); ?>
                        <div class="form-grid">

                            <div class="field">
                                <label>Student Full Name *</label>
                                <input type="text" name="student_name" value="<?= htmlspecialchars($old['student_name']) ?>" required />
<?php if (!empty($errors['student_name'])): ?><div class="field-error"><?= htmlspecialchars($errors['student_name']) ?></div><?php endif; ?>
                            </div>

                            <div class="field">
                                <label>Student Email *</label>
                                <input type="email" name="student_email" value="<?= htmlspecialchars($old['student_email']) ?>" required />
<?php if (!empty($errors['student_email'])): ?><div class="field-error"><?= htmlspecialchars($errors['student_email']) ?></div><?php endif; ?>
                            </div>

                            <div class="field field-full">
                                <label>Course *</label>
                                <select name="course_id" required>
                                    <option value="0">— Select —</option>
                                    <?php foreach ($courses as $c): ?>
                                        <option value="<?= $c['id'] ?>" <?= $old['course_id'] == $c['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($c['course_code'] . ' — ' . $c['title']) ?> (Cap: <?= (int) $c['capacity'] ?>)
                                        </option>
<?php endforeach; ?>
                                </select>
<?php if (!empty($errors['course_id'])): ?><div class="field-error"><?= htmlspecialchars($errors['course_id']) ?></div><?php endif; ?>
                            </div>

                            <div class="field">
                                <label>Enrollment Date</label>
                                <input type="date" name="enrollment_date" value="<?= htmlspecialchars($old['enrollment_date']) ?>" />
                            </div>

                            <div class="field field-full">
                                <label>Notes</label>
                                <textarea name="notes" rows="2"><?= htmlspecialchars($old['notes']) ?></textarea>
                            </div>

                        </div>
                        <div class="flex gap-1 mt-2">
                            <button type="submit" class="btn btn-primary">Update Enrollment</button>
                            <a href="enrollments.php" class="btn btn-outline">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <footer>
            <span>◈ EduEnroll — Course Enrollment System</span>
            <span>Lebanese University · Platform Development 2026</span>
        </footer>
    </body>
</html>
