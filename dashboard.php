<?php
// dashboard.php – the page users see after logging in.
declare(strict_types=1);
require __DIR__ . '/includes/init.php';

if (empty($_SESSION['user_id'])) {
    header('Location: ' . BASE_URL . '/login.php');
    exit;
}

$pageTitle = 'Dashboard';
require __DIR__ . '/includes/header.php';
?>
<section class="card">
    <h1>Welcome, <?= e($_SESSION['username'] ?? '') ?>!</h1>
    <p>You are logged in as <strong><?= e($_SESSION['role'] ?? '') ?></strong>.</p>
    <p class="muted">Activity 2 will add role-based pages and menus here.</p>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
