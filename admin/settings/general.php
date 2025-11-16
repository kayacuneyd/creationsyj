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

$defaults = [
    'site_name_fr' => 'Créations JY',
    'site_name_en' => 'Creations JY',
    'brand_tagline_fr' => 'Atelier d’upcycling en Gruyère · Créations uniques et durables.',
    'brand_tagline_en' => 'Upcycling studio in Gruyère · Unique and sustainable decor.',
    'hero_title_fr' => 'Donnez une seconde vie à vos objets',
    'hero_title_en' => 'Give a Second Life to Your Objects',
    'hero_subtitle_fr' => 'Créations uniques fabriquées à partir de matériaux recyclés par Yasemin Jemmely, en Gruyère.',
    'hero_subtitle_en' => 'Unique creations made from recycled materials by Yasemin Jemmely in Gruyère, Switzerland.',
    'hero_cta_fr' => 'Découvrir les créations',
    'hero_cta_en' => 'Explore Creations',
    'contact_email' => 'contact@creationsjy.com',
    'instagram_url' => 'https://instagram.com/creationsjy',
    'whatsapp_number' => WHATSAPP_NUMBER,
    'about_media_url' => '/assets/images/placeholder.jpg',
    'site_logo_url' => '',
];
$settings = array_merge($defaults, $settings);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf_token'] ?? null;
    if (!verifyCSRFToken($token)) {
        $errors[] = 'Security check failed, please try again.';
    } else {
        $siteNameFr = trim($_POST['site_name_fr'] ?? '');
        $siteNameEn = trim($_POST['site_name_en'] ?? '');
        $brandTaglineFr = trim($_POST['brand_tagline_fr'] ?? '');
        $brandTaglineEn = trim($_POST['brand_tagline_en'] ?? '');
        $heroTitleFr = trim($_POST['hero_title_fr'] ?? '');
        $heroTitleEn = trim($_POST['hero_title_en'] ?? '');
        $heroSubtitleFr = trim($_POST['hero_subtitle_fr'] ?? '');
        $heroSubtitleEn = trim($_POST['hero_subtitle_en'] ?? '');
        $heroCtaFr = trim($_POST['hero_cta_fr'] ?? '');
        $heroCtaEn = trim($_POST['hero_cta_en'] ?? '');
        $contactEmail = trim($_POST['contact_email'] ?? '');
        $instagramUrl = trim($_POST['instagram_url'] ?? '');
        $whatsAppNumber = trim($_POST['whatsapp_number'] ?? '');
        $aboutMediaUrl = trim($_POST['about_media_url'] ?? '');
        $siteLogoUrl = trim($_POST['site_logo_url'] ?? '');
        $siteLogoFile = $_FILES['site_logo_file'] ?? null;
        $aboutMediaFile = $_FILES['about_media_file'] ?? null;

        if ($siteNameFr === '' || $siteNameEn === '') {
            $errors[] = 'Site names cannot be empty.';
        }

        if ($brandTaglineFr === '' || $brandTaglineEn === '') {
            $errors[] = 'Brand taglines cannot be empty.';
        }

        foreach ([
            'heroTitleFr' => $heroTitleFr,
            'heroTitleEn' => $heroTitleEn,
            'heroSubtitleFr' => $heroSubtitleFr,
            'heroSubtitleEn' => $heroSubtitleEn,
            'heroCtaFr' => $heroCtaFr,
            'heroCtaEn' => $heroCtaEn,
        ] as $field => $value) {
            if ($value === '') {
                $errors[] = 'Hero content fields cannot be empty.';
                break;
            }
        }

        if ($contactEmail === '' || !filter_var($contactEmail, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Please enter a valid contact email.';
        }

        if ($instagramUrl !== '' && !filter_var($instagramUrl, FILTER_VALIDATE_URL)) {
            $errors[] = 'Please provide a valid Instagram URL.';
        }

        if ($whatsAppNumber === '') {
            $whatsAppNumber = $settings['whatsapp_number'];
        }

        $whatsAppNumber = str_replace([' ', '-', '(', ')'], '', $whatsAppNumber);
        if ($whatsAppNumber !== '') {
            if ($whatsAppNumber[0] !== '+') {
                $whatsAppNumber = '+' . ltrim($whatsAppNumber, '+');
            }
            if (!preg_match('/^\+[0-9]{6,15}$/', $whatsAppNumber)) {
                $errors[] = 'Please provide a valid WhatsApp number (include country code).';
            }
        }

        $uploadDir = __DIR__ . '/../../uploads/settings/';
        if (!is_dir($uploadDir)) {
            if (!mkdir($uploadDir, 0775, true) && !is_dir($uploadDir)) {
                $errors[] = 'Unable to create uploads directory.';
            }
        }

        $uploadedPaths = [];

        $allowedMime = ['image/jpeg', 'image/png', 'image/webp'];
        $maxSize = 2 * 1024 * 1024;

        if ($siteLogoFile && $siteLogoFile['error'] === UPLOAD_ERR_OK) {
            if (!in_array($siteLogoFile['type'], $allowedMime, true)) {
                $errors[] = 'Logo must be a JPG, PNG or WebP file.';
            } elseif ($siteLogoFile['size'] > $maxSize) {
                $errors[] = 'Logo must be under 2MB.';
            } else {
                $extension = pathinfo($siteLogoFile['name'], PATHINFO_EXTENSION);
                $filename = 'logo_' . time() . '.' . $extension;
                $destination = $uploadDir . $filename;
                if (move_uploaded_file($siteLogoFile['tmp_name'], $destination)) {
                    $uploadedPaths['site_logo_url'] = '/uploads/settings/' . $filename;
                } else {
                    $errors[] = 'Unable to upload logo.';
                }
            }
        }

        if ($aboutMediaFile && $aboutMediaFile['error'] === UPLOAD_ERR_OK) {
            if (!in_array($aboutMediaFile['type'], $allowedMime, true)) {
                $errors[] = 'Media must be a JPG, PNG or WebP file.';
            } elseif ($aboutMediaFile['size'] > $maxSize) {
                $errors[] = 'Media must be under 2MB.';
            } else {
                $extension = pathinfo($aboutMediaFile['name'], PATHINFO_EXTENSION);
                $filename = 'about_' . time() . '.' . $extension;
                $destination = $uploadDir . $filename;
                if (move_uploaded_file($aboutMediaFile['tmp_name'], $destination)) {
                    $uploadedPaths['about_media_url'] = '/uploads/settings/' . $filename;
                } else {
                    $errors[] = 'Unable to upload media.';
                }
            }
        }

        if ($aboutMediaUrl !== '' && empty($uploadedPaths['about_media_url'])) {
            $isAbsolute = filter_var($aboutMediaUrl, FILTER_VALIDATE_URL);
            $isRelative = str_starts_with($aboutMediaUrl, '/');
            if (!$isAbsolute && !$isRelative) {
                $errors[] = 'About media URL must be an absolute URL or start with /.';
            }
        }

        if ($siteLogoUrl !== '' && empty($uploadedPaths['site_logo_url'])) {
            $isAbsolute = filter_var($siteLogoUrl, FILTER_VALIDATE_URL);
            $isRelative = str_starts_with($siteLogoUrl, '/');
            if (!$isAbsolute && !$isRelative) {
                $errors[] = 'Logo URL must be an absolute URL or start with /.';
            }
        }

        if (!$errors) {
            $pairs = [
                'site_name_fr' => $siteNameFr,
                'site_name_en' => $siteNameEn,
                'brand_tagline_fr' => $brandTaglineFr,
                'brand_tagline_en' => $brandTaglineEn,
                'hero_title_fr' => $heroTitleFr,
                'hero_title_en' => $heroTitleEn,
                'hero_subtitle_fr' => $heroSubtitleFr,
                'hero_subtitle_en' => $heroSubtitleEn,
                'hero_cta_fr' => $heroCtaFr,
                'hero_cta_en' => $heroCtaEn,
                'contact_email' => $contactEmail,
                'instagram_url' => $instagramUrl,
                'whatsapp_number' => $whatsAppNumber,
                'about_media_url' => $uploadedPaths['about_media_url'] ?? $aboutMediaUrl,
                'site_logo_url' => $uploadedPaths['site_logo_url'] ?? $siteLogoUrl,
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
            refreshSiteSettingsCache();
            
            // Log activity
            logActivity('settings_updated', 'site_settings', null, 'General settings updated');
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>General settings · Admin · Creations JY</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="/assets/css/admin.css">
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

                <form method="post" class="settings-form" enctype="multipart/form-data">
                    <input type="hidden" name="csrf_token" value="<?php echo e(generateCSRFToken()); ?>">

                    <div class="settings-panel">
                        <div class="settings-panel-header">
                            <h2>Brand identity</h2>
                            <p>Control how the brand name and tagline appear across the bilingual site.</p>
                        </div>
                        <div class="settings-grid">
                            <div>
                                <label for="site_name_fr">Site name (FR)</label>
                                <input id="site_name_fr" name="site_name_fr" type="text" class="input" value="<?php echo e($settings['site_name_fr']); ?>" required>
                            </div>
                            <div>
                                <label for="site_name_en">Site name (EN)</label>
                                <input id="site_name_en" name="site_name_en" type="text" class="input" value="<?php echo e($settings['site_name_en']); ?>" required>
                            </div>
                            <div>
                                <label for="brand_tagline_fr">Tagline (FR)</label>
                                <input id="brand_tagline_fr" name="brand_tagline_fr" type="text" class="input" value="<?php echo e($settings['brand_tagline_fr']); ?>" required>
                            </div>
                            <div>
                                <label for="brand_tagline_en">Tagline (EN)</label>
                                <input id="brand_tagline_en" name="brand_tagline_en" type="text" class="input" value="<?php echo e($settings['brand_tagline_en']); ?>" required>
                            </div>
                        </div>
                    </div>

                    <div class="settings-panel">
                        <div class="settings-panel-header">
                            <h2>Hero content</h2>
                            <p>Homepage hero texts feed both French and English landing sections.</p>
                        </div>
                        <div class="settings-grid two-columns">
                            <div>
                                <label for="hero_title_fr">Hero title (FR)</label>
                                <input id="hero_title_fr" name="hero_title_fr" type="text" class="input" value="<?php echo e($settings['hero_title_fr']); ?>" required>
                            </div>
                            <div>
                                <label for="hero_title_en">Hero title (EN)</label>
                                <input id="hero_title_en" name="hero_title_en" type="text" class="input" value="<?php echo e($settings['hero_title_en']); ?>" required>
                            </div>
                            <div>
                                <label for="hero_subtitle_fr">Hero subtitle (FR)</label>
                                <textarea id="hero_subtitle_fr" name="hero_subtitle_fr" class="textarea" rows="3" required><?php echo e($settings['hero_subtitle_fr']); ?></textarea>
                            </div>
                            <div>
                                <label for="hero_subtitle_en">Hero subtitle (EN)</label>
                                <textarea id="hero_subtitle_en" name="hero_subtitle_en" class="textarea" rows="3" required><?php echo e($settings['hero_subtitle_en']); ?></textarea>
                            </div>
                            <div>
                                <label for="hero_cta_fr">CTA label (FR)</label>
                                <input id="hero_cta_fr" name="hero_cta_fr" type="text" class="input" value="<?php echo e($settings['hero_cta_fr']); ?>" required>
                            </div>
                            <div>
                                <label for="hero_cta_en">CTA label (EN)</label>
                                <input id="hero_cta_en" name="hero_cta_en" type="text" class="input" value="<?php echo e($settings['hero_cta_en']); ?>" required>
                            </div>
                        </div>
                    </div>

                    <div class="settings-panel" id="messaging">
                        <div class="settings-panel-header">
                            <h2>Messaging & contact</h2>
                            <p>WhatsApp number and contact endpoints used across the storefront.</p>
                        </div>
                        <div class="settings-grid">
                            <div>
                                <label for="contact_email">Contact email</label>
                                <input id="contact_email" name="contact_email" type="email" class="input" value="<?php echo e($settings['contact_email']); ?>" required>
                            </div>
                            <div>
                                <label for="instagram_url">Instagram URL</label>
                                <input id="instagram_url" name="instagram_url" type="url" class="input" value="<?php echo e($settings['instagram_url']); ?>">
                            </div>
                            <div>
                                <label for="whatsapp_number">WhatsApp number (with country code)</label>
                                <input id="whatsapp_number" name="whatsapp_number" type="text" class="input" value="<?php echo e($settings['whatsapp_number']); ?>" required>
                                <small>Displayed in contact forms and used for CTA links.</small>
                            </div>
                        </div>
                    </div>

                    <div class="settings-panel">
                        <div class="settings-panel-header">
                            <h2>About page media</h2>
                            <p>Add a portrait or logo showcased on the About pages.</p>
                        </div>
                <div class="settings-grid">
                    <div>
                        <label for="about_media_file">Upload media</label>
                        <input id="about_media_file" name="about_media_file" type="file" class="input" accept="image/*">
                        <small>Upload JPG, PNG ou WebP (max 2&nbsp;MB).</small>
                    </div>
                    <div>
                        <label for="about_media_url">Media URL</label>
                        <input id="about_media_url" name="about_media_url" type="text" class="input" value="<?php echo e($settings['about_media_url']); ?>">
                        <small>Optional: provide a direct URL instead of uploading.</small>
                    </div>
                </div>
            </div>

            <div class="settings-panel">
                <div class="settings-panel-header">
                    <h2>Site logo</h2>
                    <p>Upload a logo for the navigation bar. Falls back to the site name if empty.</p>
                </div>
                <div class="settings-grid">
                    <div>
                        <label for="site_logo_file">Upload logo</label>
                        <input id="site_logo_file" name="site_logo_file" type="file" class="input" accept="image/*">
                        <small>Recommended height 42px, formats JPG/PNG/WebP.</small>
                    </div>
                    <div>
                        <label for="site_logo_url">Logo URL</label>
                        <input id="site_logo_url" name="site_logo_url" type="text" class="input" value="<?php echo e($settings['site_logo_url']); ?>">
                        <small>Optional direct link.</small>
                    </div>
                </div>
                <?php if (!empty($settings['site_logo_url'])): ?>
                    <div style="margin-top: 1rem;">
                        <small>Logo actuel :</small>
                        <div style="margin-top: 0.5rem;">
                            <img src="<?php echo e($settings['site_logo_url']); ?>" alt="Logo" style="max-height: 60px;">
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <button type="submit" class="btn-primary">
                Save settings
            </button>
                </form>
            </div>
        </section>
    </main>
</body>
</html>
