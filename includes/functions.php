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


