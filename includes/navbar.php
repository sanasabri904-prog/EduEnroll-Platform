<?php
// includes/navbar.php
$user = currentUser();
?>
<nav class="navbar">
    <a href="dashboard.php" class="navbar-brand">
        <span>◈</span> EduEnroll
    </a>

    <ul class="navbar-nav">
        <li><a href="dashboard.php"  class="<?= ($activePage ?? '') === 'dashboard' ? 'active' : '' ?>">Dashboard</a></li>
        <li><a href="courses.php"    class="<?= ($activePage ?? '') === 'courses' ? 'active' : '' ?>">Courses</a></li>
        <li><a href="enrollments.php" class="<?= ($activePage ?? '') === 'enrollments' ? 'active' : '' ?>">Enrollments</a></li>
    </ul>

    <div class="navbar-right">
        <span class="navbar-user"><?= htmlspecialchars($user['full_name']) ?></span>
        <?php if (isAdmin()): ?>
            <span class="navbar-role">⚡ Admin</span>
        <?php endif; ?>
        <a href="logout.php" class="btn-logout">Logout</a>
    </div>
</nav>