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
        $googleAnalyticsId = trim($_POST['google_analytics_id'] ?? '');
        $defaultMetaTitleFr = trim($_POST['default_meta_title_fr'] ?? '');
        $defaultMetaDescriptionFr = trim($_POST['default_meta_description_fr'] ?? '');
        $defaultMetaTitleEn = trim($_POST['default_meta_title_en'] ?? '');
        $defaultMetaDescriptionEn = trim($_POST['default_meta_description_en'] ?? '');
        $siteDescriptionFr = trim($_POST['site_description_fr'] ?? '');
        $siteDescriptionEn = trim($_POST['site_description_en'] ?? '');

        $pairs = [
            'google_analytics_id' => $googleAnalyticsId,
            'default_meta_title_fr' => $defaultMetaTitleFr,
            'default_meta_description_fr' => $defaultMetaDescriptionFr,
            'default_meta_title_en' => $defaultMetaTitleEn,
            'default_meta_description_en' => $defaultMetaDescriptionEn,
            'site_description_fr' => $siteDescriptionFr,
            'site_description_en' => $siteDescriptionEn,
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
        
        // Log activity
        logActivity('settings_updated', 'site_settings', null, 'SEO settings updated');
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>SEO settings · Admin · Creations JY</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="/assets/css/admin.css">
</head>
<body>
    <header class="site-header">
        <div class="site-header-inner container">
            <strong>SEO settings</strong>
            <nav class="site-nav">
                <a href="/admin/index.php">Dashboard</a>
                <a href="/admin/settings/general.php">General</a>
                <a href="/admin/settings/whatsapp.php">WhatsApp</a>
                <a href="/admin/settings/seo.php">SEO</a>
                <a href="/admin/logout.php">Logout</a>
            </nav>
        </div>
    </header>
    <main>
        <section class="section">
            <div class="container">
                <h1 class="section-title">SEO Settings</h1>
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
                    
                    <h2>Google Analytics</h2>
                    <div style="margin-bottom:1rem;">
                        <label for="google_analytics_id">Google Analytics ID (G-XXXXXXXXXX)</label>
                        <input id="google_analytics_id" name="google_analytics_id" type="text" class="input" placeholder="G-XXXXXXXXXX" value="<?php echo e($settings['google_analytics_id'] ?? ''); ?>">
                        <small style="display: block; margin-top: 0.25rem; color: #8B7F7F;">Leave empty to disable</small>
                    </div>

                    <h2 style="margin-top: 2rem;">Default Meta Tags (FR)</h2>
                    <div style="margin-bottom:1rem;">
                        <label for="default_meta_title_fr">Default Meta Title (FR)</label>
                        <input id="default_meta_title_fr" name="default_meta_title_fr" type="text" class="input" maxlength="70" value="<?php echo e($settings['default_meta_title_fr'] ?? ''); ?>">
                        <small style="display: block; margin-top: 0.25rem; color: #8B7F7F;">Used when page-specific meta title is not set</small>
                    </div>
                    <div style="margin-bottom:1rem;">
                        <label for="default_meta_description_fr">Default Meta Description (FR)</label>
                        <textarea id="default_meta_description_fr" name="default_meta_description_fr" rows="2" class="textarea" maxlength="160"><?php echo e($settings['default_meta_description_fr'] ?? ''); ?></textarea>
                        <small style="display: block; margin-top: 0.25rem; color: #8B7F7F;">Used when page-specific meta description is not set</small>
                    </div>

                    <h2 style="margin-top: 2rem;">Default Meta Tags (EN)</h2>
                    <div style="margin-bottom:1rem;">
                        <label for="default_meta_title_en">Default Meta Title (EN)</label>
                        <input id="default_meta_title_en" name="default_meta_title_en" type="text" class="input" maxlength="70" value="<?php echo e($settings['default_meta_title_en'] ?? ''); ?>">
                        <small style="display: block; margin-top: 0.25rem; color: #8B7F7F;">Used when page-specific meta title is not set</small>
                    </div>
                    <div style="margin-bottom:1rem;">
                        <label for="default_meta_description_en">Default Meta Description (EN)</label>
                        <textarea id="default_meta_description_en" name="default_meta_description_en" rows="2" class="textarea" maxlength="160"><?php echo e($settings['default_meta_description_en'] ?? ''); ?></textarea>
                        <small style="display: block; margin-top: 0.25rem; color: #8B7F7F;">Used when page-specific meta description is not set</small>
                    </div>

                    <h2 style="margin-top: 2rem;">Site Description</h2>
                    <div style="margin-bottom:1rem;">
                        <label for="site_description_fr">Site Description (FR)</label>
                        <textarea id="site_description_fr" name="site_description_fr" rows="3" class="textarea"><?php echo e($settings['site_description_fr'] ?? ''); ?></textarea>
                    </div>
                    <div style="margin-bottom:1rem;">
                        <label for="site_description_en">Site Description (EN)</label>
                        <textarea id="site_description_en" name="site_description_en" rows="3" class="textarea"><?php echo e($settings['site_description_en'] ?? ''); ?></textarea>
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

