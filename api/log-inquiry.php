<?php
require_once __DIR__ . '/../includes/db.php';

header('Content-Type: application/json; charset=utf-8');

$payload = json_decode(file_get_contents('php://input'), true);

if (!is_array($payload) || empty($payload['product_id'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid payload']);
    exit;
}

$productId = (int) $payload['product_id'];

if ($productId <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid product id']);
    exit;
}

global $pdo;

$stmt = $pdo->prepare('INSERT INTO whatsapp_inquiries (product_id) VALUES (?)');
$stmt->execute([$productId]);

$pdo->prepare('UPDATE products SET view_count = view_count + 1 WHERE id = ?')
    ->execute([$productId]);

echo json_encode(['success' => true]);


