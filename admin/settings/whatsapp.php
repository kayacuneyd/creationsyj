<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';

requireAdmin();

global $pdo;

$errors = [];
$success = false;

$settings = [];
$stmt = $pdo->query('SELECT setting_key, setting_value FROM site_settings');
foreach ($stmt as $row) {
    $settings[$row['setting_key']] = $row['setting_value'];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf_token'] ?? null;
    if (!verifyCSRFToken($token)) {
        $errors[] = 'Security check failed, please try again.';
    } else {
        $whatsAppNumber = trim($_POST['whatsapp_number'] ?? '');

        $stmtUp = $pdo->prepare('
            INSERT INTO site_settings (setting_key, setting_value)
            VALUES (:key, :value)
            ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)
        ');
        $stmtUp->execute([
            'key' => 'whatsapp_number',
            'value' => $whatsAppNumber,
        ]);

        $settings['whatsapp_number'] = $whatsAppNumber;
        $success = true;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>WhatsApp settings · Admin · Creations JY</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="/assets/css/main.css">
</head>
<body>
    <header class="site-header">
        <div class="site-header-inner container">
            <strong>WhatsApp settings</strong>
            <nav class="site-nav">
                <a href="/admin/index.php">Dashboard</a>
                <a href="/admin/settings/general.php">General</a>
                <a href="/admin/settings/whatsapp.php">WhatsApp</a>
                <a href="/admin/logout.php">Logout</a>
            </nav>
        </div>
    </header>
    <main>
        <section class="section">
            <div class="container">
                <h1 class="section-title">WhatsApp</h1>
                <?php if ($success): ?>
                    <p style="margin-top:1rem;padding:0.75rem 1rem;background-color:#E8F5E9;border-radius:0.5rem;">
                        Settings saved.
                    </p>
                <?php endif; ?>
                <?php if ($errors): ?>
                    <ul style="margin-top:1rem;padding:0.75rem 1rem;background-color:#FFEBEE;border-radius:0.5rem;">
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo e($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>

                <form method="post">
                    <input type="hidden" name="csrf_token" value="<?php echo e(generateCSRFToken()); ?>">
                    <div style="margin-bottom:1rem;">
                        <label for="whatsapp_number">WhatsApp number (+41…)</label>
                        <input id="whatsapp_number" name="whatsapp_number" type="text" class="input" value="<?php echo e($settings['whatsapp_number'] ?? '+41XXXXXXXXX'); ?>">
                    </div>

                    <button type="submit" class="btn-primary" style="margin-top:1rem;">
                        Save
                    </button>
                </form>
            </div>
        </section>
    </main>
</body>
</html>


