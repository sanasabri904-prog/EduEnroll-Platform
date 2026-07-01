<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';
requireAdmin(); // only admins can edit courses

$db = getDB();
$id = (int) ($_GET['id'] ?? 0);

// fetch course by id
$stmt = $db->prepare('SELECT * FROM courses WHERE id = ?');
$stmt->execute([$id]);
$course = $stmt->fetch();
if (!$course) {
    setFlash('error', 'Course not found.');
    header('Location: courses.php');
    exit;
}

$errors = [];
$old = $course; // pre-fill form with existing values

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();

    // collect form inputs
    $old['course_code'] = trim($_POST['course_code'] ?? '');
    $old['title'] = trim($_POST['title'] ?? '');
    $old['instructor'] = trim($_POST['instructor'] ?? '');
    $old['schedule'] = trim($_POST['schedule'] ?? '');
    $old['credits'] = (int) ($_POST['credits'] ?? 3);
    $old['capacity'] = (int) ($_POST['capacity'] ?? 30);
    $old['description'] = trim($_POST['description'] ?? '');

    // basic validation
    if ($old['course_code'] === '')
        $errors['course_code'] = 'Course code is required.';
    if ($old['title'] === '')
        $errors['title'] = 'Course title is required.';
    if ($old['instructor'] === '')
        $errors['instructor'] = 'Instructor is required.';
    if ($old['schedule'] === '')
        $errors['schedule'] = 'Schedule is required.';
    if ($old['credits'] < 1 || $old['credits'] > 10)
        $errors['credits'] = 'Credits must be 1–10.';
    if ($old['capacity'] < 1)
        $errors['capacity'] = 'Capacity must be at least 1.';

    // capacity cannot be less than current enrollment count
    if (empty($errors['capacity'])) {
        $cntStmt = $db->prepare('SELECT COUNT(*) FROM enrollments WHERE course_id = ?');
        $cntStmt->execute([$id]);
        $currentEnrolled = $cntStmt->fetchColumn();
        if ($old['capacity'] < $currentEnrolled) {
            $errors['capacity'] = 'Capacity cannot be less than current enrollments (' . $currentEnrolled . ').';
        }
    }

    // if no errors, update course
    if (empty($errors)) {
        $dup = $db->prepare('SELECT id FROM courses WHERE course_code = ? AND id != ?');
        $dup->execute([$old['course_code'], $id]);
        if ($dup->fetch()) {
            $errors['course_code'] = 'Another course already uses this code.';
        } else {
            $db->prepare(
                    'UPDATE courses SET course_code=?, title=?, instructor=?, schedule=?, credits=?, capacity=?, description=? WHERE id=?'
            )->execute([$old['course_code'], $old['title'], $old['instructor'],
                $old['schedule'], $old['credits'], $old['capacity'], $old['description'], $id]);
            setFlash('success', 'Course updated successfully!');
            header('Location: courses.php');
            exit;
        }
    }
}

$activePage = 'courses';
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8"/>
        <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
        <title>Edit Course — EduEnroll</title>
        <?php include 'includes/page_styles.php'; ?>
    </head>
    <body>

        <?php include 'includes/navbar.php'; ?>

        <div class="wrap">
            <div class="page-header">
                <div>
                    <h1>Edit Course</h1>
                    <p>Editing: <strong><?= htmlspecialchars($course['course_code'] . ' — ' . $course['title']) ?></strong></p>
                </div>
                <a href="courses.php" class="btn btn-outline">← Back</a>
            </div>

            <div class="card form-card">
                <div class="card-body">
                    <form method="POST">
                        <?php csrfField(); ?>
                        <div class="form-grid">

                            <div class="field">
                                <label>Course Code *</label>
                                <input type="text" name="course_code" value="<?= htmlspecialchars($old['course_code']) ?>" required />
                                <?php if (!empty($errors['course_code'])): ?><div class="field-error"><?= htmlspecialchars($errors['course_code']) ?></div><?php endif; ?>
                            </div>

                            <div class="field">
                                <label>Credits *</label>
                                <input type="number" name="credits" min="1" max="10" value="<?= (int) $old['credits'] ?>" required />
                                <?php if (!empty($errors['credits'])): ?><div class="field-error"><?= htmlspecialchars($errors['credits']) ?></div><?php endif; ?>
                            </div>

                            <div class="field field-full">
                                <label>Course Title *</label>
                                <input type="text" name="title" value="<?= htmlspecialchars($old['title']) ?>" required />
                                <?php if (!empty($errors['title'])): ?><div class="field-error"><?= htmlspecialchars($errors['title']) ?></div><?php endif; ?>
                            </div>

                            <div class="field">
                                <label>Instructor *</label>
                                <input type="text" name="instructor" value="<?= htmlspecialchars($old['instructor']) ?>" required />
                                <?php if (!empty($errors['instructor'])): ?><div class="field-error"><?= htmlspecialchars($errors['instructor']) ?></div><?php endif; ?>
                            </div>

                            <div class="field">
                                <label>Capacity *</label>
                                <input type="number" name="capacity" min="1" value="<?= (int) $old['capacity'] ?>" required />
                                <?php if (!empty($errors['capacity'])): ?><div class="field-error"><?= htmlspecialchars($errors['capacity']) ?></div><?php endif; ?>
                            </div>

                            <div class="field field-full">
                                <label>Schedule *</label>
                                <input type="text" name="schedule" value="<?= htmlspecialchars($old['schedule']) ?>" required />
                                <?php if (!empty($errors['schedule'])): ?><div class="field-error"><?= htmlspecialchars($errors['schedule']) ?></div><?php endif; ?>
                            </div>

                            <div class="field field-full">
                                <label>Description (optional)</label>
                                <textarea name="description" rows="3"><?= htmlspecialchars($old['description']) ?></textarea>
                            </div>

                        </div>
                        <div class="flex gap-1 mt-2">
                            <button type="submit" class="btn btn-primary">Update Course</button>
                            <a href="courses.php" class="btn btn-outline">Cancel</a>
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
