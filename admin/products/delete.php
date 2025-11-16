<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';

requireAdmin();

global $pdo;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method not allowed');
}

$token = $_POST['csrf_token'] ?? null;
if (!verifyCSRFToken($token)) {
    http_response_code(400);
    exit('Invalid CSRF token');
}

$id = isset($_POST['id']) ? (int) $_POST['id'] : 0;
if ($id <= 0) {
    http_response_code(400);
    exit('Invalid product id');
}

// Get product title before deletion for logging
$titleStmt = $pdo->prepare('SELECT title FROM product_translations WHERE product_id = :id AND language_code = "en" LIMIT 1');
$titleStmt->execute(['id' => $id]);
$productTitle = $titleStmt->fetchColumn() ?: 'Unknown';

$stmt = $pdo->prepare('DELETE FROM products WHERE id = :id');
$stmt->execute(['id' => $id]);

// Log activity
logActivity('product_deleted', 'products', $id, 'Product deleted: ' . $productTitle);

header('Location: /admin/products/index.php');
exit;


