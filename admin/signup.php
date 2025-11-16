<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$adminCountStmt = $pdo->query('SELECT COUNT(*) FROM admin_users');
$adminCount = (int) $adminCountStmt->fetchColumn();
$hasAdmins = $adminCount > 0;

// When at least one admin exists we require a signup token to avoid public registrations.
$signupLocked = $hasAdmins && ADMIN_SIGNUP_TOKEN === '';

if (!empty($_SESSION['admin_id'])) {
    header('Location: /admin/index.php');
    exit;
}

if ($signupLocked) {
    header('Location: /admin/login.php');
    exit;
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf_token'] ?? null;
    $fullName = trim($_POST['full_name'] ?? '');
    $username = strtolower(trim($_POST['username'] ?? ''));
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $passwordConfirm = $_POST['password_confirm'] ?? '';
    $inviteToken = trim($_POST['invite_token'] ?? '');

    if (!verifyCSRFToken($token)) {
        $errors[] = 'Security check failed, please try again.';
    }

    if ($fullName === '' || mb_strlen($fullName) < 3) {
        $errors[] = 'Please provide your full name (min. 3 characters).';
    }

    if ($username === '' || !preg_match('/^[a-z0-9_]{3,30}$/', $username)) {
        $errors[] = 'Username must be 3-30 characters (letters, numbers, underscores).';
    }

    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please provide a valid email address.';
    }

    if (strlen($password) < 8) {
        $errors[] = 'Password must be at least 8 characters.';
    }

    if ($password !== $passwordConfirm) {
        $errors[] = 'Password confirmation does not match.';
    }

    if ($hasAdmins) {
        if (ADMIN_SIGNUP_TOKEN === '' || !hash_equals(ADMIN_SIGNUP_TOKEN, $inviteToken)) {
            $errors[] = 'Invalid signup token.';
        }
    }

    if (!$errors) {
        $checkStmt = $pdo->prepare('SELECT COUNT(*) FROM admin_users WHERE username = :username OR email = :email');
        $checkStmt->execute([
            'username' => $username,
            'email' => $email,
        ]);
        $exists = (int) $checkStmt->fetchColumn() > 0;

        if ($exists) {
            $errors[] = 'Username or email is already registered.';
        }
    }

    if (!$errors) {
        $role = $hasAdmins ? 'editor' : 'super_admin';
        $hash = password_hash($password, PASSWORD_BCRYPT);

        $insertStmt = $pdo->prepare('
            INSERT INTO admin_users (username, email, password_hash, full_name, role)
            VALUES (:username, :email, :password_hash, :full_name, :role)
        ');
        $insertStmt->execute([
            'username' => $username,
            'email' => $email,
            'password_hash' => $hash,
            'full_name' => $fullName,
            'role' => $role,
        ]);

        $_SESSION['admin_id'] = (int) $pdo->lastInsertId();
        $_SESSION['admin_role'] = $role;

        header('Location: /admin/index.php');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Admin Signup · Creations JY</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="/assets/css/admin.css">
</head>
<body style="background-color: #FAF6F0;">
    <div class="container" style="max-width: 480px; padding-top: 3rem;">
        <h1 class="section-title" style="text-align: center;">Create Admin Account</h1>
        <p style="text-align: center; color: #8B7F7F;">
            <?php if (!$hasAdmins): ?>
                First-time setup — the user created here becomes the Super Admin.
            <?php else: ?>
                Provide the invite token configured in <code>includes/config.php</code> to create a new admin.
            <?php endif; ?>
        </p>
        <?php if ($errors): ?>
            <ul style="margin-top: 1rem; padding: 0.75rem 1rem; background-color: #FFEBEE; border-radius: 0.5rem;">
                <?php foreach ($errors as $error): ?>
                    <li><?php echo e($error); ?></li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
        <form method="post" style="margin-top: 1.5rem;">
            <input type="hidden" name="csrf_token" value="<?php echo e(generateCSRFToken()); ?>">
            <?php if ($hasAdmins): ?>
                <div style="margin-bottom: 1rem;">
                    <label for="invite_token">Signup token</label>
                    <input id="invite_token" name="invite_token" type="text" class="input" required>
                </div>
            <?php endif; ?>
            <div style="margin-bottom: 1rem;">
                <label for="full_name">Full name</label>
                <input id="full_name" name="full_name" type="text" class="input" value="<?php echo e($_POST['full_name'] ?? ''); ?>" required>
            </div>
            <div style="margin-bottom: 1rem; display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 1rem;">
                <div>
                    <label for="username">Username</label>
                    <input id="username" name="username" type="text" class="input" value="<?php echo e($_POST['username'] ?? ''); ?>" required>
                </div>
                <div>
                    <label for="email">Email</label>
                    <input id="email" name="email" type="email" class="input" value="<?php echo e($_POST['email'] ?? ''); ?>" required>
                </div>
            </div>
            <div style="margin-bottom: 1rem;">
                <label for="password">Password</label>
                <input id="password" name="password" type="password" class="input" required>
            </div>
            <div style="margin-bottom: 1rem;">
                <label for="password_confirm">Confirm password</label>
                <input id="password_confirm" name="password_confirm" type="password" class="input" required>
            </div>
            <button type="submit" class="btn-primary" style="width: 100%; margin-top: 0.5rem;">
                Create account
            </button>
            <p style="text-align: center; margin-top: 1rem;">
                Already have access? <a href="/admin/login.php">Back to login</a>
            </p>
        </form>
    </div>
</body>
</html>
