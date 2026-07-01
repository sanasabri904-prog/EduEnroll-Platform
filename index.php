<?php

require_once 'includes/auth.php';
startSession();
header('Location: ' . (isLoggedIn() ? 'dashboard.php' : 'login.php'));
exit;
