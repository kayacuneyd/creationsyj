<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/language.php';
require_once __DIR__ . '/meta-tags.php';

$currentLang = getCurrentLanguage();
$siteName = $currentLang === 'fr' ? 'Créations JY' : 'Creations JY';

$pageType = $pageType ?? 'website';
$metaData = $metaData ?? [];

$currentPath = getCurrentPath();
$langUrls = [
    'fr' => rtrim(SITE_URL, '/') . '/fr' . translateUrl($currentPath, 'fr'),
    'en' => rtrim(SITE_URL, '/') . '/en' . translateUrl($currentPath, 'en'),
];
?>
<!DOCTYPE html>
<html lang="<?php echo e($currentLang); ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php generateMetaTags($pageType, $metaData); ?>
    <link rel="alternate" hreflang="fr" href="<?php echo e($langUrls['fr']); ?>">
    <link rel="alternate" hreflang="en" href="<?php echo e($langUrls['en']); ?>">
    <link rel="alternate" hreflang="x-default" href="<?php echo e($langUrls['fr']); ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;500;600&family=Lato:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/main.css">
</head>
<body>
    <header class="site-header">
        <div class="site-header-inner container">
            <div>
                <a href="<?php echo $currentLang === 'fr' ? '/fr/' : '/en/'; ?>">
                    <strong><?php echo e($siteName); ?></strong>
                </a>
            </div>
            <nav class="site-nav" data-nav-menu>
                <a href="<?php echo $currentLang === 'fr' ? '/fr/' : '/en/'; ?>"><?php echo e(t('home')); ?></a>
                <a href="<?php echo $currentLang === 'fr' ? '/fr/a-propos.php' : '/en/about.php'; ?>"><?php echo e(t('about')); ?></a>
                <a href="<?php echo $currentLang === 'fr' ? '/fr/produits.php' : '/en/products.php'; ?>"><?php echo e(t('products')); ?></a>
                <a href="<?php echo $currentLang === 'fr' ? '/fr/contact.php' : '/en/contact.php'; ?>"><?php echo e(t('contact')); ?></a>
            </nav>
        </div>
    </header>
    <main>


