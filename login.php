<?php
// login.php – authenticate users and manage failed-login lockouts.
declare(strict_types=1);
require __DIR__ . '/includes/init.php';

$error = '';
$username = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim((string) ($_POST['username'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');

    $stmt = $conn->prepare('SELECT id, username, password_hash, role, failed_attempts, (locked_until > NOW()) AS is_locked FROM users WHERE username = ? LIMIT 1');
    $stmt->bind_param('s', $username);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();

    if ($user && (int) $user['is_locked'] === 1) {
        $error = 'Invalid username or password';
    } elseif ($user && password_verify($password, $user['password_hash'])) {
        $stmt = $conn->prepare('UPDATE users SET failed_attempts = 0, locked_until = NULL WHERE id = ?');
        $userId = (int) $user['id'];
        $stmt->bind_param('i', $userId);
        $stmt->execute();

        session_regenerate_id(true);
        $_SESSION['user_id'] = $userId;
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];

        header('Location: ' . BASE_URL . '/dashboard.php');
        exit;
    } else {
        $error = 'Invalid username or password';

        if ($user) {
            $newAttempts = min(5, (int) $user['failed_attempts'] + 1);
            $userId = (int) $user['id'];
            if ($newAttempts >= 5) {
                $stmt = $conn->prepare('UPDATE users SET failed_attempts = ?, locked_until = DATE_ADD(NOW(), INTERVAL 15 MINUTE) WHERE id = ?');
                $stmt->bind_param('ii', $newAttempts, $userId);
            } else {
                $stmt = $conn->prepare('UPDATE users SET failed_attempts = ? WHERE id = ?');
                $stmt->bind_param('ii', $newAttempts, $userId);
            }
            $stmt->execute();
        }
    }
}

$pageTitle = 'Log in';
require __DIR__ . '/includes/header.php';
?>
<section class="card narrow">
    <h1>Log in</h1>
    <?php if ($error): ?><div class="errors"><?= e($error) ?></div><?php endif; ?>

    <form method="post" action="login.php">
        <label>Username
            <input type="text" name="username" value="<?= e($username) ?>" required autocomplete="username">
        </label>
        <label>Password
            <input type="password" name="password" required autocomplete="current-password">
        </label>
        <button type="submit">Log in</button>
    </form>
    <p class="muted">No account yet? <a href="register.php">Register</a></p>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
