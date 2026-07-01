<?php
// self_enroll.php — allow logged-in users to enroll themselves
// access: regular users only (admins redirected)

require_once 'includes/db.php';
require_once 'includes/auth.php';
requireLogin();

// Admins use add_enrollment.php instead
if (isAdmin()) {
    header('Location: courses.php');
    exit;
}

$db = getDB();
$user = currentUser();

$course_id = (int) ($_GET['course_id'] ?? 0);

// Fetch course
$stmt = $db->prepare('SELECT * FROM courses WHERE id = ?');
$stmt->execute([$course_id]);
$course = $stmt->fetch();

if (!$course) {
    setFlash('error', 'Course not found.');
    header('Location: courses.php');
    exit;
}

// Check already enrolled
$dup = $db->prepare('SELECT id FROM enrollments WHERE student_email = ? AND course_id = ?');
$dup->execute([$user['email'], $course_id]);
if ($dup->fetch()) {
    setFlash('error', 'You are already enrolled in this course.');
    header('Location: courses.php');
    exit;
}

// Check capacity
$countStmt = $db->prepare('SELECT COUNT(*) FROM enrollments WHERE course_id = ?');
$countStmt->execute([$course_id]);
if ($countStmt->fetchColumn() >= $course['capacity']) {
    setFlash('error', 'This course has reached maximum capacity.');
    header('Location: courses.php');
    exit;
}

// Process confirmation
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();

    // Re-check duplicate & capacity on POST (safety)
    $dup->execute([$user['email'], $course_id]);
    if ($dup->fetch()) {
        setFlash('error', 'You are already enrolled in this course.');
        header('Location: courses.php');
        exit;
    }
    $countStmt->execute([$course_id]);
    if ($countStmt->fetchColumn() >= $course['capacity']) {
        setFlash('error', 'Sorry, this course just became full.');
        header('Location: courses.php');
        exit;
    }

    $db->prepare(
            'INSERT INTO enrollments (student_name, student_email, course_id, enrollment_date)
         VALUES (?, ?, ?, ?)'
    )->execute([$user['full_name'], $user['email'], $course_id, date('Y-m-d')]);

    setFlash('success', 'You have successfully enrolled in "' . $course['title'] . '"!');
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
        <title>Confirm Enrollment — EduEnroll</title>
        <?php include 'includes/page_styles.php'; ?>
    </head>
    <body>

        <?php include 'includes/navbar.php'; ?>

        <div class="wrap">
            <div class="confirm-box">
                <span class="confirm-icon">📚</span>
                <h2>Confirm Enrollment</h2>
                <p>
                    You are about to enroll in:<br><br>
                    <strong><?= htmlspecialchars($course['course_code'] . ' — ' . $course['title']) ?></strong><br>
                    👨‍🏫 <?= htmlspecialchars($course['instructor']) ?><br>
                    🕐 <?= htmlspecialchars($course['schedule']) ?><br>
                    ⭐ <?= (int) $course['credits'] ?> credits<br><br>
                    <span style="color:#6b6860; font-size:.85rem;">
                        Spots available: <?= (int) $course['capacity'] - (int) $course['enroll_count'] ?? '' ?>
                    </span>
                </p>
                <form method="POST">
                    <?php csrfField(); ?>
                    <div class="confirm-actions">
                        <button type="submit" class="btn btn-success">✅ Yes, Enroll Me</button>
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
