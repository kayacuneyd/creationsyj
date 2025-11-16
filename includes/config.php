<?php

/**
 * Core configuration.
 *
 * Adjust these values for your local and production environments.
 * For production, you can override via environment variables or
 * a small wrapper that loads different config values.
 */

// Basic environment toggle (simple but effective)
define('APP_ENV', getenv('APP_ENV') ?: 'local'); // local | production

// Database configuration
define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_NAME', getenv('DB_NAME') ?: 'u629681856_creationsjy');
define('DB_USER', getenv('DB_USER') ?: 'u629681856_creationsjy');
define('DB_PASS', getenv('DB_PASS') ?: 'Beykoz1453!');

// Site configuration
define('SITE_URL', getenv('SITE_URL') ?: 'https://creationsjy.test');
define('DEFAULT_LANG', 'fr');

// Admin signup security token
// SECURITY: When empty (default), signup is only allowed when no admins exist (fresh install).
// To enable signup after first admin, set ADMIN_SIGNUP_TOKEN environment variable.
// NEVER hardcode a token value here - use environment variables only.
define('ADMIN_SIGNUP_TOKEN', getenv('ADMIN_SIGNUP_TOKEN') ?: '');

// WhatsApp configuration
define('WHATSAPP_NUMBER', getenv('WHATSAPP_NUMBER') ?: '+41XXXXXXXXX'); // Yasemin's WhatsApp Business number.
//define('WHATSAPP_NUMBER', getenv('WHATSAPP_NUMBER') ?: '+41XXXXXXXXX'); // Yasemin's WhatsApp Business number.

// Upload configuration
define('UPLOAD_DIR', __DIR__ . '/../uploads/');
define('MAX_UPLOAD_SIZE', 5 * 1024 * 1024); // 5MB

// Session configuration (can be refined in auth.php)
ini_set('session.cookie_httponly', 1);
ini_set('session.use_strict_mode', 1);
if (APP_ENV === 'production') {
    ini_set('session.cookie_secure', 1);
}

