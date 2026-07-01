<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';
requireAdmin(); // only admins can add courses

$errors = [];
$old = ['course_code' => '', 'title' => '', 'instructor' => '', 'schedule' => '', 'credits' => 3, 'capacity' => 30, 'description' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();

    $old['course_code'] = trim($_POST['course_code'] ?? '');
    $old['title'] = trim($_POST['title'] ?? '');
    $old['instructor'] = trim($_POST['instructor'] ?? '');
    $old['schedule'] = trim($_POST['schedule'] ?? '');
    $old['credits'] = (int) ($_POST['credits'] ?? 3);
    $old['capacity'] = (int) ($_POST['capacity'] ?? 30);
    $old['description'] = trim($_POST['description'] ?? '');

    if ($old['course_code'] === '')
        $errors['course_code'] = 'Course code is required.';
    if ($old['title'] === '')
        $errors['title'] = 'Course title is required.';
    if ($old['instructor'] === '')
        $errors['instructor'] = 'Instructor name is required.';
    if ($old['schedule'] === '')
        $errors['schedule'] = 'Schedule is required.';
    if ($old['credits'] < 1 || $old['credits'] > 10)
        $errors['credits'] = 'Credits must be between 1 and 10.';
    if ($old['capacity'] < 1)
        $errors['capacity'] = 'Capacity must be at least 1.';
    
// if no errors, insert into DB
    if (empty($errors)) {
        $db = getDB();
        $dup = $db->prepare('SELECT id FROM courses WHERE course_code = ?');
        $dup->execute([$old['course_code']]);
        if ($dup->fetch()) {
            $errors['course_code'] = 'This course code already exists.';
        } else {
            $db->prepare(
                    'INSERT INTO courses (course_code, title, instructor, schedule, credits, capacity, description)
                 VALUES (?, ?, ?, ?, ?, ?, ?)'
            )->execute([$old['course_code'], $old['title'], $old['instructor'],
                $old['schedule'], $old['credits'], $old['capacity'], $old['description']]);
            setFlash('success', 'Course "' . $old['title'] . '" added successfully!');
            header('Location: courses.php');
            exit;
        }
    }
}

$activePage = 'add_course';
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8"/>
        <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
        <title>Add Course — EduEnroll</title>
<?php include 'includes/page_styles.php'; ?>
    </head>
    <body>

<?php include 'includes/navbar.php'; ?>

        <div class="wrap">
            <div class="page-header">
                <div>
                    <h1>Add New Course</h1>
                    <p>Fill in the details to create a new course</p>
                </div>
                <a href="courses.php" class="btn btn-outline">← Back to Courses</a>
            </div>

            <div class="card form-card">
                <div class="card-body">
                    <form method="POST">
                                <?php csrfField(); ?>
                        <div class="form-grid">

                            <div class="field">
                                <label>Course Code *</label>
                                <input type="text" name="course_code" value="<?= htmlspecialchars($old['course_code']) ?>" placeholder="e.g. CS301" required />
                                <?php if (!empty($errors['course_code'])): ?><div class="field-error"><?= htmlspecialchars($errors['course_code']) ?></div><?php endif; ?>
                            </div>

                            <div class="field">
                                <label>Credits *</label>
                                <input type="number" name="credits" min="1" max="10" value="<?= (int) $old['credits'] ?>" required />
                                <?php if (!empty($errors['credits'])): ?><div class="field-error"><?= htmlspecialchars($errors['credits']) ?></div><?php endif; ?>
                            </div>

                            <div class="field field-full">
                                <label>Course Title *</label>
                                <input type="text" name="title" value="<?= htmlspecialchars($old['title']) ?>" placeholder="e.g. Web Development" required />
                                <?php if (!empty($errors['title'])): ?><div class="field-error"><?= htmlspecialchars($errors['title']) ?></div><?php endif; ?>
                            </div>

                            <div class="field">
                                <label>Instructor *</label>
                                <input type="text" name="instructor" value="<?= htmlspecialchars($old['instructor']) ?>" placeholder="Dr. Name" required />
                                <?php if (!empty($errors['instructor'])): ?><div class="field-error"><?= htmlspecialchars($errors['instructor']) ?></div><?php endif; ?>
                            </div>

                            <div class="field">
                                <label>Capacity *</label>
                                <input type="number" name="capacity" min="1" value="<?= (int) $old['capacity'] ?>" required />
                                <?php if (!empty($errors['capacity'])): ?><div class="field-error"><?= htmlspecialchars($errors['capacity']) ?></div><?php endif; ?>
                            </div>

                            <div class="field field-full">
                                <label>Schedule *</label>
                                <input type="text" name="schedule" value="<?= htmlspecialchars($old['schedule']) ?>" placeholder="e.g. Mon/Wed 09:00-10:30" required />
<?php if (!empty($errors['schedule'])): ?><div class="field-error"><?= htmlspecialchars($errors['schedule']) ?></div><?php endif; ?>
                            </div>

                            <div class="field field-full">
                                <label>Description (optional)</label>
                                <textarea name="description" rows="3" placeholder="Brief description…"><?= htmlspecialchars($old['description']) ?></textarea>
                            </div>

                        </div>
                        <div class="flex gap-1 mt-2">
                            <button type="submit" class="btn btn-primary">Save Course</button>
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
