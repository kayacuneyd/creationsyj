<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/db.php';

requireAdmin();

global $pdo;

$productId = isset($_POST['product_id']) ? (int) $_POST['product_id'] : 0;
$token = $_POST['csrf_token'] ?? null;

if (!verifyCSRFToken($token)) {
    http_response_code(400);
    exit('Invalid CSRF token');
}

if ($productId <= 0) {
    http_response_code(400);
    exit('Invalid product id');
}

if (!isset($_FILES['images'])) {
    http_response_code(400);
    exit('No files uploaded');
}

$allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
$allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];

foreach ($_FILES['images']['tmp_name'] as $key => $tmpName) {
    if (!is_uploaded_file($tmpName)) {
        continue;
    }

    // Validate MIME type
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $tmpName);
    finfo_close($finfo);

    if (!in_array($mime, $allowedTypes, true)) {
        continue;
    }

    // Validate extension
    $ext = strtolower(pathinfo($_FILES['images']['name'][$key], PATHINFO_EXTENSION));
    if (!in_array($ext, $allowedExtensions, true)) {
        continue;
    }

    // Validate size
    if ($_FILES['images']['size'][$key] > MAX_UPLOAD_SIZE) {
        continue;
    }

    // Generate secure filename
    $filename = bin2hex(random_bytes(16)) . '.' . $ext;

    $originalDir = UPLOAD_DIR . 'products/original/';
    $thumbDir = UPLOAD_DIR . 'products/thumbnail/';
    $mediumDir = UPLOAD_DIR . 'products/medium/';

    if (!is_dir($originalDir)) {
        mkdir($originalDir, 0755, true);
    }
    if (!is_dir($thumbDir)) {
        mkdir($thumbDir, 0755, true);
    }
    if (!is_dir($mediumDir)) {
        mkdir($mediumDir, 0755, true);
    }

    $originalPath = $originalDir . $filename;
    if (!move_uploaded_file($tmpName, $originalPath)) {
        continue;
    }

    createThumbnail($originalPath, $thumbDir . $filename, 400, 400);
    createThumbnail($originalPath, $mediumDir . $filename, 800, 800);

    $stmt = $pdo->prepare('
        INSERT INTO product_images (product_id, filename, display_order)
        VALUES (:product_id, :filename, :display_order)
    ');
    $stmt->execute([
        'product_id' => $productId,
        'filename' => $filename,
        'display_order' => $key,
    ]);
}

header('Location: /admin/products/edit.php?id=' . $productId);
exit;

function createThumbnail(string $source, string $destination, int $width, int $height): void
{
    [$origWidth, $origHeight, $type] = getimagesize($source);

    $ratio = min($width / $origWidth, $height / $origHeight);
    $newWidth = (int) ($origWidth * $ratio);
    $newHeight = (int) ($origHeight * $ratio);

    $thumb = imagecreatetruecolor($newWidth, $newHeight);

    switch ($type) {
        case IMAGETYPE_JPEG:
            $image = imagecreatefromjpeg($source);
            break;
        case IMAGETYPE_PNG:
            $image = imagecreatefrompng($source);
            imagealphablending($thumb, false);
            imagesavealpha($thumb, true);
            break;
        case IMAGETYPE_WEBP:
            if (!function_exists('imagecreatefromwebp')) {
                return;
            }
            $image = imagecreatefromwebp($source);
            break;
        default:
            return;
    }

    imagecopyresampled($thumb, $image, 0, 0, 0, 0, $newWidth, $newHeight, $origWidth, $origHeight);
    imagejpeg($thumb, $destination, 85);

    imagedestroy($thumb);
    imagedestroy($image);
}


