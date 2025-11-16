<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/config.php';

header('Content-Type: application/xml; charset=utf-8');

echo '<?xml version="1.0" encoding="UTF-8"?>';
?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"
        xmlns:xhtml="http://www.w3.org/1999/xhtml">
<?php

$base = rtrim(SITE_URL, '/');

$staticPages = [
    ['fr' => '/fr/', 'en' => '/en/', 'priority' => '1.0'],
    ['fr' => '/fr/produits.php', 'en' => '/en/products.php', 'priority' => '0.9'],
    ['fr' => '/fr/a-propos.php', 'en' => '/en/about.php', 'priority' => '0.7'],
    ['fr' => '/fr/contact.php', 'en' => '/en/contact.php', 'priority' => '0.6'],
];

foreach ($staticPages as $page) {
    echo '<url>';
    echo '<loc>' . htmlspecialchars($base . $page['fr'], ENT_QUOTES, 'UTF-8') . '</loc>';
    echo '<xhtml:link rel="alternate" hreflang="en" href="' . htmlspecialchars($base . $page['en'], ENT_QUOTES, 'UTF-8') . '"/>';
    echo '<xhtml:link rel="alternate" hreflang="fr" href="' . htmlspecialchars($base . $page['fr'], ENT_QUOTES, 'UTF-8') . '"/>';
    echo '<priority>' . $page['priority'] . '</priority>';
    echo '</url>';
}

$stmt = $pdo->query("
    SELECT p.id, p.slug, p.updated_at
    FROM products p
    WHERE p.status = 'available'
");

while ($product = $stmt->fetch()) {
    $slug = $product['slug'];
    $lastmod = date('Y-m-d', strtotime($product['updated_at']));
    echo '<url>';
    echo '<loc>' . htmlspecialchars($base . '/fr/produit.php?id=' . $product['id'], ENT_QUOTES, 'UTF-8') . '</loc>';
    echo '<xhtml:link rel="alternate" hreflang="en" href="' . htmlspecialchars($base . '/en/product.php?id=' . $product['id'], ENT_QUOTES, 'UTF-8') . '"/>';
    echo '<xhtml:link rel="alternate" hreflang="fr" href="' . htmlspecialchars($base . '/fr/produit.php?id=' . $product['id'], ENT_QUOTES, 'UTF-8') . '"/>';
    echo '<lastmod>' . $lastmod . '</lastmod>';
    echo '<priority>0.8</priority>';
    echo '</url>';
}

?>
</urlset>


