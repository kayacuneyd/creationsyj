<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/language.php';
require_once __DIR__ . '/meta-tags.php';

$currentLang = getCurrentLanguage();
$siteName = $currentLang === 'fr' ? 'Créations JY' : 'Creations JY';

$pageType = $pageType ?? 'website';
$metaData = $metaData ?? [];

$baseUrl = rtrim(getSiteBaseUrl(), '/');
$currentPath = getCurrentPath();
$langUrls = [
    'fr' => $baseUrl . '/fr' . translateUrl($currentPath, 'fr'),
    'en' => $baseUrl . '/en' . translateUrl($currentPath, 'en'),
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
            <div class="site-logo">
                <a href="<?php echo $currentLang === 'fr' ? '/fr/' : '/en/'; ?>">
                    <?php if ($logoUrl = getSiteLogoUrl()): ?>
                        <img src="<?php echo e($logoUrl); ?>" alt="<?php echo e($siteName); ?>" loading="lazy">
                    <?php else: ?>
                        <strong><?php echo e($siteName); ?></strong>
                    <?php endif; ?>
                </a>
            </div>
            <button class="nav-toggle" type="button" aria-label="Toggle navigation" aria-expanded="false" aria-controls="primary-navigation" data-nav-toggle>
                <span></span>
                <span></span>
                <span></span>
            </button>
            <nav class="site-nav" id="primary-navigation" data-nav-menu>
                <a href="<?php echo $currentLang === 'fr' ? '/fr/' : '/en/'; ?>"><?php echo e(t('home')); ?></a>
                <a href="<?php echo $currentLang === 'fr' ? '/fr/a-propos.php' : '/en/about.php'; ?>"><?php echo e(t('about')); ?></a>
                <a href="<?php echo $currentLang === 'fr' ? '/fr/produits.php' : '/en/products.php'; ?>"><?php echo e(t('products')); ?></a>
                <a href="<?php echo $currentLang === 'fr' ? '/fr/contact.php' : '/en/contact.php'; ?>"><?php echo e(t('contact')); ?></a>
                <span class="nav-divider" aria-hidden="true">|</span>
                <?php
                $otherLang = $currentLang === 'fr' ? 'en' : 'fr';
                $currentPath = getCurrentPath();
                $translatedPath = translateUrl($currentPath, $otherLang);
                $otherLangUrl = '/' . $otherLang . $translatedPath;
                ?>
                <a href="<?php echo e($otherLangUrl); ?>" style="font-weight: 500;">
                    <?php echo $otherLang === 'fr' ? 'FR' : 'EN'; ?>
                </a>
            </nav>
        </div>
    </header>
    <main>
