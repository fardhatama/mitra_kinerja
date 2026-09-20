<?php
require_once __DIR__ . '/includes/auth.php';
header('Location: ' . (currentUser() ? 'dashboard.php' : 'login.php'));
exit;
