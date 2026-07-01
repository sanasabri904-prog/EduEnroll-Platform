<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';
requireAdmin(); // only admins can delete courses

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

// handle delete confirmation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_delete'])) {
    verifyCsrf();
    $db->prepare('DELETE FROM courses WHERE id = ?')->execute([$id]);
    setFlash('success', 'Course "' . $course['title'] . '" deleted successfully.');
    header('Location: courses.php');
    exit;
}

$activePage = 'courses';
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8"/>
        <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
        <title>Delete Course — EduEnroll</title>
        <?php include 'includes/page_styles.php'; ?>
    </head>
    <body>

        <?php include 'includes/navbar.php'; ?>

        <div class="wrap">
            <div class="confirm-box">
                <span class="confirm-icon">🗑️</span>
                <h2>Delete Course?</h2>
                <p>
                    You are about to permanently delete:<br>
                    <strong><?= htmlspecialchars($course['course_code'] . ' — ' . $course['title']) ?></strong><br><br>
                    ⚠️ All student enrollments for this course will also be removed.
                    This action <strong>cannot be undone</strong>.
                </p>
                <form method="POST">
                    <?php csrfField(); ?>
                    <div class="confirm-actions">
                        <button type="submit" name="confirm_delete" value="1" class="btn btn-danger">Yes, Delete</button>
                        <a href="courses.php" class="btn btn-outline">Cancel</a>
                    </div>
                </form>
            </div>
        </div>

        <footer>
            <span>◈ EduEnroll — Course Enrollment System</span>
            <span>Lebanese University · Platform Development 2026</span>
        </footer>
    </body>
</html>
