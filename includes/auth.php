<?php

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/functions.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Require an authenticated admin user.
 * Redirects to admin login if not authenticated.
 */
function requireAdmin(): void
{
    if (empty($_SESSION['admin_id'])) {
        header('Location: /admin/login.php');
        exit;
    }
}

/**
 * Get the currently authenticated admin user record.
 */
function currentAdmin(): ?array
{
    static $cachedUser = null;

    if ($cachedUser !== null) {
        return $cachedUser;
    }

    if (empty($_SESSION['admin_id'])) {
        return null;
    }

    global $pdo;

    $stmt = $pdo->prepare('SELECT * FROM admin_users WHERE id = ? LIMIT 1');
    $stmt->execute([$_SESSION['admin_id']]);
    $user = $stmt->fetch();

    $cachedUser = $user ?: null;

    return $cachedUser;
}

/**
 * Check if current admin has a given role.
 */
function adminHasRole(string $role): bool
{
    $user = currentAdmin();

    if (!$user) {
        return false;
    }

    if ($user['role'] === 'super_admin') {
        return true;
    }

    return $user['role'] === $role;
}


