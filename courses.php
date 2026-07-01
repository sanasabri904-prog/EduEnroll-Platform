<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';
requireLogin();

$db = getDB();
$user = currentUser();

// Fetch courses + enrollment count per course
$courses = $db->query(
                'SELECT c.*, COUNT(e.id) AS enroll_count
     FROM courses c
     LEFT JOIN enrollments e ON c.id = e.course_id
     GROUP BY c.id
     ORDER BY c.course_code'
        )->fetchAll();

// For regular users: get the list of course IDs they're enrolled in
$enrolledCourseIds = [];
if (!isAdmin()) {
    $stmt = $db->prepare('SELECT course_id FROM enrollments WHERE student_email = ?');
    $stmt->execute([$user['email']]);
    $enrolledCourseIds = $stmt->fetchAll(PDO::FETCH_COLUMN);
}

$flash = getFlash();
$activePage = 'courses';
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8"/>
        <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
        <title>Courses — EduEnroll</title>
        <?php include 'includes/page_styles.php'; ?>
    </head>
    <body>

        <?php include 'includes/navbar.php'; ?>

        <div class="wrap">

            <?php if ($flash): ?>
                <div class="flash flash-<?= htmlspecialchars($flash['type']) ?>">
                    <?= htmlspecialchars($flash['msg']) ?>
                    <button onclick="this.parentElement.remove()" class="flash-close">✕</button>
                </div>
            <?php endif; ?>

            <div class="page-header">
                <div>
                    <h1>All Courses</h1>
                    <p><?= count($courses) ?> course<?= count($courses) !== 1 ? 's' : '' ?> available</p>
                </div>
                <?php if (isAdmin()): ?>
                    <a href="add_course.php" class="btn btn-primary">+ Add Course</a>
                <?php endif; ?>
            </div>

            <?php if (empty($courses)): ?>
                <div class="empty-state">
                    <span class="empty-icon">📚</span>
                    <h3>No courses yet</h3>
                    <p>An admin needs to add courses first.</p>
                </div>
            <?php else: ?>
                <div class="course-grid">
                    <?php foreach ($courses as $i => $c): ?>
                        <?php
                        $isFull = $c['enroll_count'] >= $c['capacity'];
                        $enrolled = in_array($c['id'], $enrolledCourseIds);
                        ?>
                        <div class="course-card" style="animation-delay: <?= $i * 60 ?>ms">
                            <div class="course-code"><?= htmlspecialchars($c['course_code']) ?></div>
                            <div class="course-title"><?= htmlspecialchars($c['title']) ?></div>
                            <div class="course-meta">
                                👨‍🏫 <?= htmlspecialchars($c['instructor']) ?><br>
                                🕐 <?= htmlspecialchars($c['schedule']) ?><br>
                                ⭐ <?= $c['credits'] ?> credits &nbsp;|&nbsp;
                                👥 <?= $c['enroll_count'] ?> / <?= $c['capacity'] ?> enrolled
                            </div>
                            <?php if ($c['description']): ?>
                                <div class="course-desc"><?= htmlspecialchars(mb_strimwidth($c['description'], 0, 110, '…')) ?></div>
                            <?php endif; ?>

                            <div class="course-actions">
                                <?php if (isAdmin()): ?>
                                    <!-- Admin actions -->
                                    <a href="add_enrollment.php?course_id=<?= $c['id'] ?>" class="btn btn-success btn-sm">+ Enroll Student</a>
                                    <a href="edit_course.php?id=<?= $c['id'] ?>"           class="btn btn-outline btn-sm">✏️ Edit</a>
                                    <a href="delete_course.php?id=<?= $c['id'] ?>"         class="btn btn-danger btn-sm">🗑️ Delete</a>
                                <?php else: ?>
                                    <!-- User actions -->
                                    <?php if ($enrolled): ?>
                                        <span class="btn btn-outline btn-sm btn-disabled">✅ Already Enrolled</span>
                                        <a href="self_unenroll.php?course_id=<?= $c['id'] ?>" class="btn btn-danger btn-sm">Leave Course</a>
                                    <?php elseif ($isFull): ?>
                                        <span class="btn btn-outline btn-sm btn-disabled">🔒 Course Full</span>
                                    <?php else: ?>
                                        <a href="self_enroll.php?course_id=<?= $c['id'] ?>" class="btn btn-success btn-sm">+ Enroll Me</a>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

        </div>

        <footer>
            <span>◈ EduEnroll — Course Enrollment System</span>
            <span>Lebanese University · Platform Development 2026</span>
        </footer>

        <script>
            document.addEventListener('DOMContentLoaded', function () {
                var flash = document.querySelector('.flash');
                if (flash) {
                    setTimeout(function () {
                        flash.style.transition = 'opacity .4s';
                        flash.style.opacity = '0';
                        setTimeout(function () {
                            flash.remove();
                        }, 400);
                    }, 4000);
                }
            });
        </script>
    </body>
</html>
