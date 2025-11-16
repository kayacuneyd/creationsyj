<?php

require_once __DIR__ . '/config.php';

/**
 * Determine current language from URL.
 */
function getCurrentLanguage(): string
{
    $uri = $_SERVER['REQUEST_URI'] ?? '/';

    if (strpos($uri, '/fr/') === 0 || $uri === '/fr') {
        return 'fr';
    }

    if (strpos($uri, '/en/') === 0 || $uri === '/en') {
        return 'en';
    }

    return DEFAULT_LANG;
}

/**
 * Simple in-memory translations for UI chrome.
 * Content (products/categories) comes from DB.
 */
function t(string $key, ?string $lang = null): string
{
    static $translations = null;

    if ($translations === null) {
        $translations = [
            'fr' => [
                'home' => 'Accueil',
                'about' => 'À propos',
                'products' => 'Produits',
                'contact' => 'Contact',
                'whatsapp_cta' => 'Contacter via WhatsApp',
                'available' => 'Disponible',
                'sold' => 'Vendu',
                'reserved' => 'Réservé',
            ],
            'en' => [
                'home' => 'Home',
                'about' => 'About',
                'products' => 'Products',
                'contact' => 'Contact',
                'whatsapp_cta' => 'Contact via WhatsApp',
                'available' => 'Available',
                'sold' => 'Sold',
                'reserved' => 'Reserved',
            ],
        ];
    }

    $lang = $lang ?: getCurrentLanguage();

    return $translations[$lang][$key] ?? $key;
}

/**
 * Translate URL path between FR and EN variants.
 */
function translateUrl(string $url, string $targetLang): string
{
    // Remove language prefix if present
    $path = $url;
    if (strpos($path, '/fr/') === 0) {
        $path = substr($path, 3);
    } elseif (strpos($path, '/en/') === 0) {
        $path = substr($path, 3);
    } elseif ($path === '/fr') {
        $path = '/';
    } elseif ($path === '/en') {
        $path = '/';
    }
    
    // Handle root path
    if ($path === '' || $path === '/') {
        return '/';
    }
    
    // Extract query string if present
    $queryString = '';
    $qPos = strpos($path, '?');
    if ($qPos !== false) {
        $queryString = substr($path, $qPos);
        $path = substr($path, 0, $qPos);
    }
    
    // URL mapping
    $urlMap = [
        'fr' => [
            '/produits.php' => '/products.php',
            '/produit.php' => '/product.php',
            '/a-propos.php' => '/about.php',
            '/contact.php' => '/contact.php',
            '/produits' => '/products',
            '/produit' => '/product',
            '/a-propos' => '/about',
            '/contact' => '/contact',
        ],
        'en' => [
            '/products.php' => '/produits.php',
            '/product.php' => '/produit.php',
            '/about.php' => '/a-propos.php',
            '/contact.php' => '/contact.php',
            '/products' => '/produits',
            '/product' => '/produit',
            '/about' => '/a-propos',
            '/contact' => '/contact',
        ],
    ];

    $currentLang = getCurrentLanguage();

    if ($currentLang === $targetLang) {
        return $path . $queryString;
    }

    // Try to translate the path
    $translatedPath = $path;
    if (isset($urlMap[$currentLang])) {
        foreach ($urlMap[$currentLang] as $from => $to) {
            if ($path === $from || strpos($path, $from) === 0) {
                $translatedPath = str_replace($from, $to, $path);
                break;
            }
        }
    }

    return $translatedPath . $queryString;
}


