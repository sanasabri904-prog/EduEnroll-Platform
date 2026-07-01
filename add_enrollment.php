<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';
requireAdmin();

$db = getDB();
$courses = $db->query('SELECT id, course_code, title, capacity FROM courses ORDER BY course_code')->fetchAll();

$errors = [];
$old = [
    'student_name' => '',
    'student_email' => '',
    'course_id' => (int) ($_GET['course_id'] ?? 0),
    'enrollment_date' => date('Y-m-d'),
    'notes' => '',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();

    $old['student_name'] = trim($_POST['student_name'] ?? '');
    $old['student_email'] = trim($_POST['student_email'] ?? '');
    $old['course_id'] = (int) ($_POST['course_id'] ?? 0);
    $old['enrollment_date'] = trim($_POST['enrollment_date'] ?? date('Y-m-d'));
    $old['notes'] = trim($_POST['notes'] ?? '');

    if ($old['student_name'] === '')
        $errors['student_name'] = 'Student name is required.';
    if ($old['student_email'] === '')
        $errors['student_email'] = 'Student email is required.';
    elseif (!filter_var($old['student_email'], FILTER_VALIDATE_EMAIL))
        $errors['student_email'] = 'Enter a valid email address.';
    if ($old['course_id'] === 0)
        $errors['course_id'] = 'Please select a course.';

    if (empty($errors)) {
        // Check duplicate
        $dup = $db->prepare('SELECT id FROM enrollments WHERE student_email = ? AND course_id = ?');
        $dup->execute([$old['student_email'], $old['course_id']]);
        if ($dup->fetch()) {
            $errors['student_email'] = 'This student is already enrolled in the selected course.';
        } else {
            // Check capacity
            $capStmt = $db->prepare('SELECT capacity FROM courses WHERE id = ?');
            $capStmt->execute([$old['course_id']]);
            $cap = $capStmt->fetchColumn();

            $cntStmt = $db->prepare('SELECT COUNT(*) FROM enrollments WHERE course_id = ?');
            $cntStmt->execute([$old['course_id']]);
            $cnt = $cntStmt->fetchColumn();

            if ($cnt >= $cap) {
                $errors['course_id'] = 'This course has reached maximum capacity (' . $cap . ').';
            } else {
                $db->prepare(
                        'INSERT INTO enrollments (student_name, student_email, course_id, enrollment_date, notes)
                     VALUES (?, ?, ?, ?, ?)'
                )->execute([$old['student_name'], $old['student_email'],
                    $old['course_id'], $old['enrollment_date'], $old['notes']]);
                setFlash('success', 'Student enrolled successfully!');
                header('Location: enrollments.php');
                exit;
            }
        }
    }
}

$activePage = 'add_enrollment';
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8"/>
        <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
        <title>Enroll Student — EduEnroll</title>
        <?php include 'includes/page_styles.php'; ?>
    </head>
    <body>

        <?php include 'includes/navbar.php'; ?>

        <div class="wrap">
            <div class="page-header">
                <div>
                    <h1>Enroll a Student</h1>
                    <p>Add a new student enrollment record</p>
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
                                <input type="text" name="student_name" value="<?= htmlspecialchars($old['student_name']) ?>" placeholder="Student's full name" required />
                                <?php if (!empty($errors['student_name'])): ?><div class="field-error"><?= htmlspecialchars($errors['student_name']) ?></div><?php endif; ?>
                            </div>

                            <div class="field">
                                <label>Student Email *</label>
                                <input type="email" name="student_email" value="<?= htmlspecialchars($old['student_email']) ?>" placeholder="student@example.com" required />
                                <?php if (!empty($errors['student_email'])): ?><div class="field-error"><?= htmlspecialchars($errors['student_email']) ?></div><?php endif; ?>
                            </div>

                            <div class="field field-full">
                                <label>Select Course *</label>
                                <select name="course_id" required>
                                    <option value="0">— Choose a course —</option>
                                    <?php foreach ($courses as $c): ?>
                                        <option value="<?= $c['id'] ?>" <?= $old['course_id'] == $c['id'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($c['course_code'] . ' — ' . $c['title']) ?> (Capacity: <?= (int) $c['capacity'] ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <?php if (!empty($errors['course_id'])): ?><div class="field-error"><?= htmlspecialchars($errors['course_id']) ?></div><?php endif; ?>
                            </div>

                            <div class="field">
                                <label>Enrollment Date *</label>
                                <input type="date" name="enrollment_date" value="<?= htmlspecialchars($old['enrollment_date']) ?>" required />
                            </div>

                            <div class="field field-full">
                                <label>Notes (optional)</label>
                                <textarea name="notes" rows="2" placeholder="Any additional notes…"><?= htmlspecialchars($old['notes']) ?></textarea>
                            </div>

                        </div>
                        <div class="flex gap-1 mt-2">
                            <button type="submit" class="btn btn-success">Enroll Student</button>
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
