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
    $urlMap = [
        'fr' => [
            '/produits' => '/products',
            '/produit' => '/product',
            '/a-propos' => '/about',
            '/contact' => '/contact',
        ],
        'en' => [
            '/products' => '/produits',
            '/product' => '/produit',
            '/about' => '/a-propos',
            '/contact' => '/contact',
        ],
    ];

    $currentLang = getCurrentLanguage();

    if ($currentLang === $targetLang) {
        return $url;
    }

    foreach ($urlMap[$currentLang] as $from => $to) {
        if (strpos($url, $from) === 0) {
            return str_replace($from, $to, $url);
        }
    }

    return $url;
}


