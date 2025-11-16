<?php

/**
 * Generic helper functions used across the site.
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Escape HTML output.
 */
function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

/**
 * Generate or return the CSRF token for the current session.
 */
function generateCSRFToken(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

/**
 * Verify an incoming CSRF token.
 */
function verifyCSRFToken(?string $token): bool
{
    return isset($_SESSION['csrf_token']) && is_string($token) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Get current full URL (used for meta tags, etc.).
 */
function getCurrentFullUrl(): string
{
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $uri = $_SERVER['REQUEST_URI'] ?? '/';

    return $scheme . '://' . $host . $uri;
}

/**
 * Resolve the base site URL using the current request when possible.
 */
function getSiteBaseUrl(): string
{
    if (!empty($_SERVER['HTTP_HOST'])) {
        $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        return $scheme . '://' . $_SERVER['HTTP_HOST'];
    }

    $configuredUrl = defined('SITE_URL') ? trim(SITE_URL) : '';

    return $configuredUrl !== '' ? rtrim($configuredUrl, '/') : 'http://localhost';
}

/**
 * Get current request path (without scheme/host/query).
 */
function getCurrentPath(): string
{
    $uri = $_SERVER['REQUEST_URI'] ?? '/';
    $qPos = strpos($uri, '?');
    if ($qPos !== false) {
        $uri = substr($uri, 0, $qPos);
    }
    return $uri ?: '/';
}

/**
 * Get client IP address.
 */
function getClientIp(): string
{
    $ipKeys = ['HTTP_CF_CONNECTING_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_REAL_IP', 'REMOTE_ADDR'];
    foreach ($ipKeys as $key) {
        if (!empty($_SERVER[$key])) {
            $ip = trim(explode(',', $_SERVER[$key])[0]);
            if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                return $ip;
            }
        }
    }
    return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
}

/**
 * Check if login is blocked for identifier (IP or username).
 */
function isLoginBlocked(string $identifier, string $type = 'ip'): bool
{
    global $pdo;
    
    $stmt = $pdo->prepare('
        SELECT blocked_until 
        FROM login_attempts 
        WHERE identifier = :identifier 
          AND attempt_type = :type 
          AND blocked_until > NOW()
        LIMIT 1
    ');
    $stmt->execute(['identifier' => $identifier, 'type' => $type]);
    $result = $stmt->fetch();
    
    return $result !== false;
}

/**
 * Record a failed login attempt.
 */
function recordFailedLogin(string $identifier, string $type = 'ip'): void
{
    global $pdo;
    
    $maxAttempts = 5;
    $blockDuration = 15 * 60; // 15 minutes in seconds
    
    $stmt = $pdo->prepare('
        SELECT attempts, blocked_until 
        FROM login_attempts 
        WHERE identifier = :identifier 
          AND attempt_type = :type
        LIMIT 1
    ');
    $stmt->execute(['identifier' => $identifier, 'type' => $type]);
    $existing = $stmt->fetch();
    
    if ($existing) {
        $newAttempts = $existing['attempts'] + 1;
        $blockedUntil = null;
        
        if ($newAttempts >= $maxAttempts) {
            $blockedUntil = date('Y-m-d H:i:s', time() + $blockDuration);
        }
        
        $updateStmt = $pdo->prepare('
            UPDATE login_attempts 
            SET attempts = :attempts, 
                blocked_until = :blocked_until,
                last_attempt = NOW()
            WHERE identifier = :identifier 
              AND attempt_type = :type
        ');
        $updateStmt->execute([
            'attempts' => $newAttempts,
            'blocked_until' => $blockedUntil,
            'identifier' => $identifier,
            'type' => $type,
        ]);
    } else {
        $blockedUntil = null;
        if ($maxAttempts <= 1) {
            $blockedUntil = date('Y-m-d H:i:s', time() + $blockDuration);
        }
        
        $insertStmt = $pdo->prepare('
            INSERT INTO login_attempts (identifier, attempt_type, attempts, blocked_until)
            VALUES (:identifier, :type, 1, :blocked_until)
        ');
        $insertStmt->execute([
            'identifier' => $identifier,
            'type' => $type,
            'blocked_until' => $blockedUntil,
        ]);
    }
}

/**
 * Clear login attempts for identifier (on successful login).
 */
function clearLoginAttempts(string $identifier, string $type = 'ip'): void
{
    global $pdo;
    
    $stmt = $pdo->prepare('DELETE FROM login_attempts WHERE identifier = :identifier AND attempt_type = :type');
    $stmt->execute(['identifier' => $identifier, 'type' => $type]);
}

/**
 * Get remaining block time in minutes.
 */
function getRemainingBlockTime(string $identifier, string $type = 'ip'): ?int
{
    global $pdo;
    
    $stmt = $pdo->prepare('
        SELECT TIMESTAMPDIFF(SECOND, NOW(), blocked_until) AS seconds
        FROM login_attempts 
        WHERE identifier = :identifier 
          AND attempt_type = :type 
          AND blocked_until > NOW()
        LIMIT 1
    ');
    $stmt->execute(['identifier' => $identifier, 'type' => $type]);
    $result = $stmt->fetch();
    
    if ($result && $result['seconds'] > 0) {
        return (int) ceil($result['seconds'] / 60);
    }
    
    return null;
}

/**
 * Log admin activity.
 */
function logActivity(string $action, ?string $tableName = null, ?int $recordId = null, ?string $details = null, ?int $adminId = null): void
{
    global $pdo;
    
    if ($adminId === null) {
        $adminId = $_SESSION['admin_id'] ?? null;
    }
    
    $ipAddress = getClientIp();
    
    $stmt = $pdo->prepare('
        INSERT INTO activity_logs (admin_id, action, table_name, record_id, details, ip_address)
        VALUES (:admin_id, :action, :table_name, :record_id, :details, :ip_address)
    ');
    $stmt->execute([
        'admin_id' => $adminId,
        'action' => $action,
        'table_name' => $tableName,
        'record_id' => $recordId,
        'details' => $details,
        'ip_address' => $ipAddress,
    ]);
}

/**
 * Get product image URL or placeholder if image doesn't exist.
 */
function getProductImageUrl(?string $filename, string $size = 'medium'): string
{
    if (empty($filename)) {
        return '/assets/images/placeholder.jpg';
    }
    
    $sizeMap = [
        'thumbnail' => '/uploads/products/thumbnail/',
        'medium' => '/uploads/products/medium/',
        'large' => '/uploads/products/large/',
    ];
    
    $basePath = $sizeMap[$size] ?? $sizeMap['medium'];
    $imagePath = $_SERVER['DOCUMENT_ROOT'] . $basePath . $filename;
    
    // Check if image file exists, if not return placeholder
    if (!file_exists($imagePath)) {
        return '/assets/images/placeholder.jpg';
    }
    
    return $basePath . $filename;
}

/**
 * Generate picture element with WebP support and fallback.
 * Returns placeholder if image doesn't exist.
 */
function getPictureElement(?string $filename, string $alt, string $size = 'medium', array $attributes = []): string
{
    // If no filename or file doesn't exist, return placeholder
    if (empty($filename)) {
        $attrString = '';
        foreach ($attributes as $key => $value) {
            $attrString .= ' ' . htmlspecialchars($key, ENT_QUOTES, 'UTF-8') . '="' . htmlspecialchars($value, ENT_QUOTES, 'UTF-8') . '"';
        }
        return '<img src="/assets/images/placeholder.jpg" alt="' . htmlspecialchars($alt, ENT_QUOTES, 'UTF-8') . '"' . $attrString . '>';
    }
    
    $baseFilename = pathinfo($filename, PATHINFO_FILENAME);
    $ext = pathinfo($filename, PATHINFO_EXTENSION);
    $webpFilename = $baseFilename . '.webp';
    
    $sizeMap = [
        'thumbnail' => '/uploads/products/thumbnail/',
        'medium' => '/uploads/products/medium/',
        'large' => '/uploads/products/large/',
    ];
    
    $webpSizeMap = [
        'thumbnail' => '/uploads/products/thumbnail/webp/',
        'medium' => '/uploads/products/medium/webp/',
        'large' => '/uploads/products/large/webp/',
    ];
    
    $basePath = $sizeMap[$size] ?? $sizeMap['medium'];
    $webpPath = $webpSizeMap[$size] ?? $webpSizeMap['medium'];
    
    $jpegSrc = $basePath . $filename;
    $webpSrc = $webpPath . $webpFilename;
    
    // Check if main image file exists
    $jpegFullPath = $_SERVER['DOCUMENT_ROOT'] . $jpegSrc;
    if (!file_exists($jpegFullPath)) {
        $attrString = '';
        foreach ($attributes as $key => $value) {
            $attrString .= ' ' . htmlspecialchars($key, ENT_QUOTES, 'UTF-8') . '="' . htmlspecialchars($value, ENT_QUOTES, 'UTF-8') . '"';
        }
        return '<img src="/assets/images/placeholder.jpg" alt="' . htmlspecialchars($alt, ENT_QUOTES, 'UTF-8') . '"' . $attrString . '>';
    }
    
    $attrString = '';
    foreach ($attributes as $key => $value) {
        $attrString .= ' ' . htmlspecialchars($key, ENT_QUOTES, 'UTF-8') . '="' . htmlspecialchars($value, ENT_QUOTES, 'UTF-8') . '"';
    }
    
    $html = '<picture>';
    
    // Check if WebP file exists
    $webpFullPath = $_SERVER['DOCUMENT_ROOT'] . $webpSrc;
    if (file_exists($webpFullPath)) {
        $html .= '<source srcset="' . htmlspecialchars($webpSrc, ENT_QUOTES, 'UTF-8') . '" type="image/webp">';
    }
    
    $html .= '<img src="' . htmlspecialchars($jpegSrc, ENT_QUOTES, 'UTF-8') . '" alt="' . htmlspecialchars($alt, ENT_QUOTES, 'UTF-8') . '"' . $attrString . '>';
    $html .= '</picture>';
    
    return $html;
}

