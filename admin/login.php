<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!empty($_SESSION['admin_id'])) {
    header('Location: /admin/index.php');
    exit;
}

$error = '';
$ipAddress = getClientIp();

// Check if IP is blocked
if (isLoginBlocked($ipAddress, 'ip')) {
    $remaining = getRemainingBlockTime($ipAddress, 'ip');
    $error = 'Too many failed login attempts. Please try again in ' . ($remaining ?? 15) . ' minutes.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$error) {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $token = $_POST['csrf_token'] ?? null;

    if (!verifyCSRFToken($token)) {
        $error = 'Security check failed, please try again.';
    } elseif ($username === '' || $password === '') {
        $error = 'Please fill in all fields.';
    } else {
        // Check if username is blocked
        if (isLoginBlocked($username, 'username')) {
            $remaining = getRemainingBlockTime($username, 'username');
            $error = 'Too many failed login attempts for this username. Please try again in ' . ($remaining ?? 15) . ' minutes.';
        } else {
            $stmt = $pdo->prepare('SELECT * FROM admin_users WHERE username = ? LIMIT 1');
            $stmt->execute([$username]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password_hash'])) {
                // Successful login - clear attempts
                clearLoginAttempts($ipAddress, 'ip');
                clearLoginAttempts($username, 'username');
                
                $_SESSION['admin_id'] = $user['id'];
                $_SESSION['admin_role'] = $user['role'];

                $update = $pdo->prepare('UPDATE admin_users SET last_login = NOW() WHERE id = ?');
                $update->execute([$user['id']]);

                header('Location: /admin/index.php');
                exit;
            } else {
                // Failed login - record attempts
                recordFailedLogin($ipAddress, 'ip');
                recordFailedLogin($username, 'username');
                
                // Check if now blocked
                if (isLoginBlocked($ipAddress, 'ip') || isLoginBlocked($username, 'username')) {
                    $remaining = getRemainingBlockTime($ipAddress, 'ip') ?? getRemainingBlockTime($username, 'username');
                    $error = 'Too many failed login attempts. Please try again in ' . ($remaining ?? 15) . ' minutes.';
                } else {
                    $error = 'Invalid credentials.';
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Admin Login · Creations JY</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="/assets/css/admin.css">
</head>
<body style="background-color: #FAF6F0;">
    <div class="container" style="max-width: 420px; padding-top: 4rem;">
        <h1 class="section-title" style="text-align: center;">Admin Login</h1>
        <?php if ($error): ?>
            <p style="margin-top: 1rem; padding: 0.75rem 1rem; background-color: #FFEBEE; border-radius: 0.5rem;">
                <?php echo e($error); ?>
            </p>
        <?php endif; ?>
        <form method="post" style="margin-top: 1.5rem;">
            <input type="hidden" name="csrf_token" value="<?php echo e(generateCSRFToken()); ?>">
            <div style="margin-bottom: 1rem;">
                <label for="username">Username</label>
                <input id="username" name="username" type="text" class="input" required>
            </div>
            <div style="margin-bottom: 1rem;">
                <label for="password">Password</label>
                <input id="password" name="password" type="password" class="input" required>
            </div>
            <button type="submit" class="btn-primary" style="width: 100%; margin-top: 0.5rem;">
                Login
            </button>
        </form>
    </div>
</body>
</html>


