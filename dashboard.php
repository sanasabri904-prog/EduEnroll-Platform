<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';
requireLogin();

$db = getDB();
$user = currentUser();

$totalCourses = $db->query('SELECT COUNT(*) FROM courses')->fetchColumn();
$totalEnrollments = $db->query('SELECT COUNT(*) FROM enrollments')->fetchColumn();
$totalStudents = $db->query('SELECT COUNT(DISTINCT student_email) FROM enrollments')->fetchColumn();

// My enrollments count (for regular user)
$myEnrollments = 0;
if (!isAdmin()) {
    $stmt = $db->prepare('SELECT COUNT(*) FROM enrollments WHERE student_email = ?');
    $stmt->execute([$user['email']]);
    $myEnrollments = $stmt->fetchColumn();
}

// Recent enrollments — admin sees all, user sees only theirs
if (isAdmin()) {
    $recentEnrollments = $db->query(
                    'SELECT e.student_name, e.student_email, c.title AS course_title, c.course_code, e.enrollment_date
         FROM enrollments e JOIN courses c ON e.course_id = c.id
         ORDER BY e.created_at DESC LIMIT 6'
            )->fetchAll();
} else {
    $stmt = $db->prepare(
            'SELECT e.student_name, e.student_email, c.title AS course_title, c.course_code, e.enrollment_date
         FROM enrollments e JOIN courses c ON e.course_id = c.id
         WHERE e.student_email = ?
         ORDER BY e.created_at DESC LIMIT 6'
    );
    $stmt->execute([$user['email']]);
    $recentEnrollments = $stmt->fetchAll();
}

$flash = getFlash();
$activePage = 'dashboard';
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8"/>
        <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
        <title>Dashboard — EduEnroll</title>
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
                    <h1>Dashboard</h1>
                    <p>Welcome, <strong><?= htmlspecialchars($user['full_name']) ?></strong> —
                        <?= isAdmin() ? '⚡ You have full admin access.' : '👀 Browse courses and manage your enrollments.' ?>
                    </p>
                </div>
                <?php if (isAdmin()): ?>
                    <a href="add_course.php" class="btn btn-primary">+ Add Course</a>
                <?php else: ?>
                    <a href="courses.php" class="btn btn-primary">Browse Courses</a>
                <?php endif; ?>
            </div>

            <!-- Stat Cards -->
            <div class="stats-row">
                <div class="stat-card">
                    <span class="stat-icon">📚</span>
                    <span class="stat-num"><?= $totalCourses ?></span>
                    <span class="stat-lbl">Total Courses</span>
                </div>
                <?php if (isAdmin()): ?>
                    <div class="stat-card">
                        <span class="stat-icon">📋</span>
                        <span class="stat-num"><?= $totalEnrollments ?></span>
                        <span class="stat-lbl">Total Enrollments</span>
                    </div>
                    <div class="stat-card">
                        <span class="stat-icon">👥</span>
                        <span class="stat-num"><?= $totalStudents ?></span>
                        <span class="stat-lbl">Unique Students</span>
                    </div>
                <?php else: ?>
                    <div class="stat-card">
                        <span class="stat-icon">📋</span>
                        <span class="stat-num"><?= $myEnrollments ?></span>
                        <span class="stat-lbl">My Enrollments</span>
                    </div>
                    <div class="stat-card">
                        <span class="stat-icon">🎓</span>
                        <span class="stat-num"><?= $totalCourses - $myEnrollments ?></span>
                        <span class="stat-lbl">Available Courses</span>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Recent Enrollments Table -->
            <div class="card">
                <div class="table-head">
                    <h2><?= isAdmin() ? 'Recent Enrollments' : 'My Recent Enrollments' ?></h2>
                    <a href="enrollments.php" class="btn btn-outline btn-sm">View All</a>
                </div>
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <?php if (isAdmin()): ?><th>Student</th><th>Email</th><?php endif; ?>
                                <th>Course</th><th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($recentEnrollments)): ?>
                                <tr><td colspan="4" class="empty-msg">
                                        <?= isAdmin() ? 'No enrollments yet.' : 'You have not enrolled in any course yet. <a href="courses.php">Browse courses</a>' ?>
                                    </td></tr>
                            <?php else: ?>
                                <?php foreach ($recentEnrollments as $e): ?>
                                    <tr>
                                        <?php if (isAdmin()): ?>
                                            <td><?= htmlspecialchars($e['student_name']) ?></td>
                                            <td class="text-muted"><?= htmlspecialchars($e['student_email']) ?></td>
                                        <?php endif; ?>
                                        <td>
                                            <span class="badge"><?= htmlspecialchars($e['course_code']) ?></span>
                                            <?= htmlspecialchars($e['course_title']) ?>
                                        </td>
                                        <td class="text-muted"><?= htmlspecialchars($e['enrollment_date']) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

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
