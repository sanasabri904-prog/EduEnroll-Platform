<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';
requireAdmin(); // only admins can remove enrollments

$db = getDB();
$id = (int) ($_GET['id'] ?? 0);

// fetch enrollment + course info
$stmt = $db->prepare(
        'SELECT e.*, c.title AS course_title, c.course_code
     FROM enrollments e JOIN courses c ON e.course_id = c.id
     WHERE e.id = ?'
);
$stmt->execute([$id]);
$enrollment = $stmt->fetch();
if (!$enrollment) {
    setFlash('error', 'Enrollment not found.');
    header('Location: enrollments.php');
    exit;
}

// handle delete confirmation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_delete'])) {
    verifyCsrf();
    $db->prepare('DELETE FROM enrollments WHERE id = ?')->execute([$id]);
    setFlash('success', 'Enrollment removed successfully.');
    header('Location: enrollments.php');
    exit;
}

$activePage = 'enrollments';
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8"/>
        <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
        <title>Delete Enrollment — EduEnroll</title>
        <?php include 'includes/page_styles.php'; ?>
    </head>
    <body>

        <?php include 'includes/navbar.php'; ?>

        <div class="wrap">
            <div class="confirm-box">
                <span class="confirm-icon">🗑️</span>
                <h2>Remove Enrollment?</h2>
                <p>
                    You are about to remove:<br>
                    <strong><?= htmlspecialchars($enrollment['student_name']) ?></strong>
                    (<?= htmlspecialchars($enrollment['student_email']) ?>)<br>
                    from <strong><?= htmlspecialchars($enrollment['course_code'] . ' — ' . $enrollment['course_title']) ?></strong>.<br><br>
                    This action <strong>cannot be undone</strong>.
                </p>
                <form method="POST">
                    <?php csrfField(); ?>
                    <div class="confirm-actions">
                        <button type="submit" name="confirm_delete" value="1" class="btn btn-danger">Yes, Remove</button>
                        <a href="enrollments.php" class="btn btn-outline">Cancel</a>
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
