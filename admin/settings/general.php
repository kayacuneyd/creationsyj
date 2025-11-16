<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';

requireAdmin();

global $pdo;

$errors = [];
$success = false;

// Load current settings into key => value
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
        $siteNameFr = trim($_POST['site_name_fr'] ?? '');
        $siteNameEn = trim($_POST['site_name_en'] ?? '');
        $contactEmail = trim($_POST['contact_email'] ?? '');
        $instagramUrl = trim($_POST['instagram_url'] ?? '');

        $pairs = [
            'site_name_fr' => $siteNameFr,
            'site_name_en' => $siteNameEn,
            'contact_email' => $contactEmail,
            'instagram_url' => $instagramUrl,
        ];

        $stmtUp = $pdo->prepare('
            INSERT INTO site_settings (setting_key, setting_value)
            VALUES (:key, :value)
            ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)
        ');

        foreach ($pairs as $key => $value) {
            $stmtUp->execute([
                'key' => $key,
                'value' => $value,
            ]);
        }

        $success = true;
        $settings = array_merge($settings, $pairs);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>General settings · Admin · Creations JY</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="/assets/css/main.css">
</head>
<body>
    <header class="site-header">
        <div class="site-header-inner container">
            <strong>General settings</strong>
            <nav class="site-nav">
                <a href="/admin/index.php">Dashboard</a>
                <a href="/admin/products/index.php">Products</a>
                <a href="/admin/categories/index.php">Categories</a>
                <a href="/admin/settings/general.php">Settings</a>
                <a href="/admin/logout.php">Logout</a>
            </nav>
        </div>
    </header>
    <main>
        <section class="section">
            <div class="container">
                <h1 class="section-title">General</h1>
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
                        <label for="site_name_fr">Site name (FR)</label>
                        <input id="site_name_fr" name="site_name_fr" type="text" class="input" value="<?php echo e($settings['site_name_fr'] ?? 'Créations JY'); ?>">
                    </div>
                    <div style="margin-bottom:1rem;">
                        <label for="site_name_en">Site name (EN)</label>
                        <input id="site_name_en" name="site_name_en" type="text" class="input" value="<?php echo e($settings['site_name_en'] ?? 'Creations JY'); ?>">
                    </div>
                    <div style="margin-bottom:1rem;">
                        <label for="contact_email">Contact email</label>
                        <input id="contact_email" name="contact_email" type="email" class="input" value="<?php echo e($settings['contact_email'] ?? 'contact@creationsjy.com'); ?>">
                    </div>
                    <div style="margin-bottom:1rem;">
                        <label for="instagram_url">Instagram URL</label>
                        <input id="instagram_url" name="instagram_url" type="url" class="input" value="<?php echo e($settings['instagram_url'] ?? 'https://instagram.com/creationsjy'); ?>">
                    </div>

                    <button type="submit" class="btn-primary" style="margin-top:1rem;">
                        Save settings
                    </button>
                </form>
            </div>
        </section>
    </main>
</body>
</html>


