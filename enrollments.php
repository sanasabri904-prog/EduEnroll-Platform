<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';
requireLogin(); // both admins and users must be logged in

$db = getDB();
$user = currentUser();

// Get courses for admin filter dropdown
$courses = $db->query('SELECT id, course_code, title FROM courses ORDER BY course_code')->fetchAll();
$filterCourse = (int) ($_GET['course_id'] ?? 0);

if (isAdmin()) {
    // Admin sees all enrollments, with optional course filter
    if ($filterCourse) {
        $stmt = $db->prepare(
                'SELECT e.*, c.title AS course_title, c.course_code
             FROM enrollments e JOIN courses c ON e.course_id = c.id
             WHERE e.course_id = ? ORDER BY e.enrollment_date DESC'
        );
        $stmt->execute([$filterCourse]);
    } else {
        $stmt = $db->query(
                'SELECT e.*, c.title AS course_title, c.course_code
             FROM enrollments e JOIN courses c ON e.course_id = c.id
             ORDER BY e.created_at DESC'
        );
    }
} else {
    // Regular user sees ONLY their own enrollments
    $stmt = $db->prepare(
            'SELECT e.*, c.title AS course_title, c.course_code
         FROM enrollments e JOIN courses c ON e.course_id = c.id
         WHERE e.student_email = ?
         ORDER BY e.created_at DESC'
    );
    $stmt->execute([$user['email']]);
}

$enrollments = $stmt->fetchAll();
$flash = getFlash();
$activePage = 'enrollments';
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8"/>
        <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
        <title>Enrollments — EduEnroll</title>
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
                    <h1><?= isAdmin() ? 'All Enrollments' : 'My Enrollments' ?></h1>
                    <p><?= count($enrollments) ?> record<?= count($enrollments) !== 1 ? 's' : '' ?>
                        <?= ($filterCourse && isAdmin()) ? ' (filtered)' : '' ?>
                    </p>
                </div>
                <?php if (isAdmin()): ?>
                    <a href="add_enrollment.php" class="btn btn-primary">+ Enroll Student</a>
                <?php else: ?>
                    <a href="courses.php" class="btn btn-primary">+ Enroll in a Course</a>
                <?php endif; ?>
            </div>

            <!-- Filter by Course — admin only -->
            <?php if (isAdmin()): ?>
                <div class="card mb-2">
                    <div class="card-body" style="padding:.85rem 1.25rem">
                        <form method="GET" style="display:flex; gap:.75rem; align-items:center; flex-wrap:wrap">
                            <label style="font-size:.82rem; font-weight:600; color:#6b6860">Filter by course:</label>
                            <select name="course_id" onchange="this.form.submit()"
                                    style="width:auto; flex:1; min-width:220px; padding:.5rem .85rem; border:1px solid #e2dfd8; border-radius:8px; font-family:'Outfit',sans-serif; background:#f5f4f0;">
                                <option value="0">— All Courses —</option>
                                <?php foreach ($courses as $c): ?>
                                    <option value="<?= $c['id'] ?>" <?= $filterCourse == $c['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($c['course_code'] . ' — ' . $c['title']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <?php if ($filterCourse): ?>
                                <a href="enrollments.php" class="btn btn-outline btn-sm">Clear Filter</a>
                            <?php endif; ?>
                        </form>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Enrollments Table -->
            <div class="card">
                <div class="table-wrap">
                    <?php if (empty($enrollments)): ?>
                        <div class="empty-state">
                            <span class="empty-icon">📋</span>
                            <h3><?= isAdmin() ? 'No enrollments found' : 'You have no enrollments yet' ?></h3>
                            <p><?= isAdmin() ? 'Use the "+ Enroll Student" button to add one.' : 'Go to <a href="courses.php">Courses</a> to enroll.' ?></p>
                        </div>
                    <?php else: ?>
                        <table>
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <?php if (isAdmin()): ?><th>Student</th><th>Email</th><?php endif; ?>
                                    <th>Course</th>
                                    <th>Enrollment Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($enrollments as $e): ?>
                                    <tr>
                                        <td class="text-muted"><?= (int) $e['id'] ?></td>
                                        <?php if (isAdmin()): ?>
                                            <td><?= htmlspecialchars($e['student_name']) ?></td>
                                            <td class="text-muted"><?= htmlspecialchars($e['student_email']) ?></td>
                                        <?php endif; ?>
                                        <td>
                                            <span class="badge"><?= htmlspecialchars($e['course_code']) ?></span>
                                            <?= htmlspecialchars($e['course_title']) ?>
                                        </td>
                                        <td class="text-muted"><?= htmlspecialchars($e['enrollment_date']) ?></td>
                                        <td>
                                            <?php if (isAdmin()): ?>
                                                <a href="edit_enrollment.php?id=<?= (int) $e['id'] ?>"   class="btn btn-outline btn-sm">✏️ Edit</a>
                                                <a href="delete_enrollment.php?id=<?= (int) $e['id'] ?>" class="btn btn-danger btn-sm">🗑️ Delete</a>
                                            <?php else: ?>
                                                <a href="self_unenroll.php?course_id=<?= (int) $e['course_id'] ?>" class="btn btn-danger btn-sm">🚪 Leave</a>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
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
