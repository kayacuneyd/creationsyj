<?php

require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/language.php';

function generateMetaTags(string $pageType, array $data = []): void
{
    $lang = getCurrentLanguage();
    $siteName = $lang === 'fr' ? 'Créations JY' : 'Creations JY';

    $defaults = [
        'title' => $siteName . ' | Upcycling Artisanal',
        'description' => $lang === 'fr'
            ? "Découvrez des créations uniques issues de matériaux recyclés par Yasemin Jemmely en Suisse."
            : "Discover unique creations made from recycled materials by Yasemin Jemmely in Switzerland.",
        'image' => SITE_URL . '/assets/images/og-default.jpg',
    ];

    $meta = array_merge($defaults, $data);

    ?>
    <title><?php echo e($meta['title']); ?></title>
    <meta name="description" content="<?php echo e($meta['description']); ?>">

    <meta property="og:type" content="<?php echo $pageType === 'product' ? 'product' : 'website'; ?>">
    <meta property="og:title" content="<?php echo e($meta['title']); ?>">
    <meta property="og:description" content="<?php echo e($meta['description']); ?>">
    <meta property="og:image" content="<?php echo e($meta['image']); ?>">
    <meta property="og:url" content="<?php echo e(getCurrentFullUrl()); ?>">
    <meta property="og:locale" content="<?php echo $lang === 'fr' ? 'fr_FR' : 'en_US'; ?>">

    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?php echo e($meta['title']); ?>">
    <meta name="twitter:description" content="<?php echo e($meta['description']); ?>">
    <meta name="twitter:image" content="<?php echo e($meta['image']); ?>">
    
    <!-- Structured Data (JSON-LD) -->
    <?php if ($pageType === 'product' && isset($meta['product'])): ?>
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "Product",
        "name": "<?php echo e($meta['product']['title']); ?>",
        "description": "<?php echo e(mb_substr(strip_tags($meta['product']['description'] ?? ''), 0, 200, 'UTF-8')); ?>",
        "image": "<?php echo e($meta['image']); ?>",
        "offers": {
            "@type": "Offer",
            "availability": "<?php echo $meta['product']['status'] === 'available' ? 'https://schema.org/InStock' : 'https://schema.org/OutOfStock'; ?>",
            "price": "0",
            "priceCurrency": "CHF"
        }
    }
    </script>
    <?php endif; ?>
    <?php
}


