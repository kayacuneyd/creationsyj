<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';

requireAdmin();

global $pdo;

$current = currentAdmin();
if (!$current || $current['role'] !== 'super_admin') {
    http_response_code(403);
    exit('Only super admins can manage users.');
}

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf_token'] ?? null;
    if (!verifyCSRFToken($token)) {
        $errors[] = 'Security check failed, please try again.';
    } else {
        $userId = isset($_POST['user_id']) ? (int) $_POST['user_id'] : 0;
        $password = $_POST['password'] ?? '';

        if ($userId <= 0 || $password === '') {
            $errors[] = 'User and password are required.';
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare('UPDATE admin_users SET password_hash = :hash WHERE id = :id');
            $stmt->execute([
                'hash' => $hash,
                'id' => $userId,
            ]);
            $success = true;
        }
    }
}

$users = $pdo->query('SELECT id, username, email, role FROM admin_users ORDER BY id ASC')->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Admin users · Admin · Creations JY</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="/assets/css/main.css">
</head>
<body>
    <header class="site-header">
        <div class="site-header-inner container">
            <strong>Admin users</strong>
            <nav class="site-nav">
                <a href="/admin/index.php">Dashboard</a>
                <a href="/admin/settings/general.php">General</a>
                <a href="/admin/settings/users.php">Users</a>
                <a href="/admin/logout.php">Logout</a>
            </nav>
        </div>
    </header>
    <main>
        <section class="section">
            <div class="container">
                <h1 class="section-title">Users</h1>
                <?php if ($success): ?>
                    <p style="margin-top:1rem;padding:0.75rem 1rem;background-color:#E8F5E9;border-radius:0.5rem;">
                        Password updated.
                    </p>
                <?php endif; ?>
                <?php if ($errors): ?>
                    <ul style="margin-top:1rem;padding:0.75rem 1rem;background-color:#FFEBEE;border-radius:0.5rem;">
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo e($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>

                <table style="width:100%;border-collapse:collapse;margin-top:1rem;font-size:0.95rem;">
                    <thead>
                    <tr>
                        <th style="text-align:left;padding:0.5rem;border-bottom:1px solid #E8E4DD;">ID</th>
                        <th style="text-align:left;padding:0.5rem;border-bottom:1px solid #E8E4DD;">Username</th>
                        <th style="text-align:left;padding:0.5rem;border-bottom:1px solid #E8E4DD;">Email</th>
                        <th style="text-align:left;padding:0.5rem;border-bottom:1px solid #E8E4DD;">Role</th>
                        <th style="text-align:right;padding:0.5rem;border-bottom:1px solid #E8E4DD;">Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td style="padding:0.5rem;"><?php echo (int) $user['id']; ?></td>
                            <td style="padding:0.5rem;"><?php echo e($user['username']); ?></td>
                            <td style="padding:0.5rem;"><?php echo e($user['email']); ?></td>
                            <td style="padding:0.5rem;"><?php echo e($user['role']); ?></td>
                            <td style="padding:0.5rem;text-align:right;">
                                <form method="post" style="display:inline;">
                                    <input type="hidden" name="csrf_token" value="<?php echo e(generateCSRFToken()); ?>">
                                    <input type="hidden" name="user_id" value="<?php echo (int) $user['id']; ?>">
                                    <input type="password" name="password" placeholder="New password" class="input" style="width:160px;display:inline-block;">
                                    <button type="submit" class="btn-primary" style="margin-left:0.5rem;">Update</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </main>
</body>
</html>


