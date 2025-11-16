<?php

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/language.php';
require_once __DIR__ . '/db.php';

/**
 * Fetch product by ID with minimal fields for WhatsApp messages.
 */
function getProductById(int $productId): ?array
{
    global $pdo;

    $stmt = $pdo->prepare('
        SELECT p.id, p.slug, pt.title
        FROM products p
        JOIN product_translations pt 
          ON p.id = pt.product_id 
         AND pt.language_code = :lang
        WHERE p.id = :id
        LIMIT 1
    ');

    $stmt->execute([
        'id' => $productId,
        'lang' => getCurrentLanguage(),
    ]);

    $product = $stmt->fetch();

    return $product ?: null;
}

/**
 * Build a WhatsApp deeplink URL with optional product context.
 */
function getWhatsAppLink(?int $productId = null, string $customMessage = ''): string
{
    $phone = str_replace(['+', ' ', '-'], '', WHATSAPP_NUMBER);

    $message = "Bonjour, je suis intéressé(e) par vos créations.";

    if ($productId) {
        $product = getProductById($productId);

        if ($product) {
            $lang = getCurrentLanguage();
            $productPath = $lang === 'fr' ? '/fr/produit/' : '/en/product/';
            $productUrl = rtrim(SITE_URL, '/') . $productPath . $product['slug'];
            $message = 'Bonjour, je suis intéressé(e) par ce produit: ' . $product['title'] . ' - ' . $productUrl;
        }
    }

    if ($customMessage !== '') {
        $message .= ' ' . $customMessage;
    }

    return 'https://wa.me/' . $phone . '?text=' . urlencode($message);
}


