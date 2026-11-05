<?php
// index.php – send visitors to the right page.
declare(strict_types=1);
require __DIR__ . '/includes/init.php';
header('Location: ' . BASE_URL . (empty($_SESSION['user_id']) ? '/login.php' : '/dashboard.php'));
exit;
