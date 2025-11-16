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

$stmt = $pdo->prepare('DELETE FROM products WHERE id = :id');
$stmt->execute(['id' => $id]);

header('Location: /admin/products/index.php');
exit;


