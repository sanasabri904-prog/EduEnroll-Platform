<?php
// self_unenroll.php — allow a user to remove themselves from a course
// access: regular users only (admins redirected)

require_once 'includes/db.php';
require_once 'includes/auth.php';
requireLogin();

if (isAdmin()) {
    header('Location: courses.php');
    exit;
}

$db = getDB();
$user = currentUser();
$course_id = (int) ($_GET['course_id'] ?? 0);

// Fetch the enrollment
$stmt = $db->prepare(
        'SELECT e.*, c.title AS course_title, c.course_code
     FROM enrollments e JOIN courses c ON e.course_id = c.id
     WHERE e.student_email = ? AND e.course_id = ?'
);
$stmt->execute([$user['email'], $course_id]);
$enrollment = $stmt->fetch();

if (!$enrollment) {
    setFlash('error', 'Enrollment not found.');
    header('Location: courses.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $db->prepare('DELETE FROM enrollments WHERE student_email = ? AND course_id = ?')
            ->execute([$user['email'], $course_id]);
    setFlash('success', 'You have left "' . $enrollment['course_title'] . '" successfully.');
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
        <title>Leave Course — EduEnroll</title>
        <?php include 'includes/page_styles.php'; ?>
    </head>
    <body>

        <?php include 'includes/navbar.php'; ?>

        <div class="wrap">
            <div class="confirm-box">
                <span class="confirm-icon">🚪</span>
                <h2>Leave Course?</h2>
                <p>
                    You are about to unenroll from:<br><br>
                    <strong><?= htmlspecialchars($enrollment['course_code'] . ' — ' . $enrollment['course_title']) ?></strong><br><br>
                    This action <strong>cannot be undone</strong>. You will lose your spot.
                </p>
                <form method="POST">
                    <?php csrfField(); ?>
                    <div class="confirm-actions">
                        <button type="submit" class="btn btn-danger">Yes, Leave Course</button>
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
