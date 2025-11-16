<?php

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/language.php';

$lang = getCurrentLanguage();

if ($lang === 'fr') {
    header('Location: /fr/');
    exit;
}

header('Location: /en/');
exit;


