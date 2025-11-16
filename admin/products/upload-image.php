<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/db.php';

/**
 * Create thumbnail from source image.
 */
function createThumbnail(string $source, string $destination, int $width, int $height, bool $webp = false): void
{
    if (!file_exists($source)) {
        return;
    }

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
    
    $dir = dirname($destination);
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
    
    if ($webp && function_exists('imagewebp')) {
        imagewebp($thumb, $destination, 85);
    } else {
        imagejpeg($thumb, $destination, 85);
    }

    imagedestroy($thumb);
    imagedestroy($image);
}

/**
 * Handle image upload for a product.
 * Can be called from create.php or used as standalone script.
 */
function handleImageUpload(int $productId, array $files): void
{
    global $pdo;

    if (empty($files['name'][0])) {
        return;
    }

    $allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
    $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];

    $originalDir = UPLOAD_DIR . 'products/original/';
    $thumbDir = UPLOAD_DIR . 'products/thumbnail/';
    $mediumDir = UPLOAD_DIR . 'products/medium/';
    $largeDir = UPLOAD_DIR . 'products/large/';
    $thumbWebpDir = UPLOAD_DIR . 'products/thumbnail/webp/';
    $mediumWebpDir = UPLOAD_DIR . 'products/medium/webp/';
    $largeWebpDir = UPLOAD_DIR . 'products/large/webp/';

    if (!is_dir($originalDir)) {
        mkdir($originalDir, 0755, true);
    }
    if (!is_dir($thumbDir)) {
        mkdir($thumbDir, 0755, true);
    }
    if (!is_dir($mediumDir)) {
        mkdir($mediumDir, 0755, true);
    }
    if (!is_dir($largeDir)) {
        mkdir($largeDir, 0755, true);
    }
    if (!is_dir($thumbWebpDir)) {
        mkdir($thumbWebpDir, 0755, true);
    }
    if (!is_dir($mediumWebpDir)) {
        mkdir($mediumWebpDir, 0755, true);
    }
    if (!is_dir($largeWebpDir)) {
        mkdir($largeWebpDir, 0755, true);
    }

    foreach ($files['tmp_name'] as $key => $tmpName) {
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
        $ext = strtolower(pathinfo($files['name'][$key], PATHINFO_EXTENSION));
        if (!in_array($ext, $allowedExtensions, true)) {
            continue;
        }

        // Validate size
        if ($files['size'][$key] > MAX_UPLOAD_SIZE) {
            continue;
        }

        // Generate secure filename
        $filename = bin2hex(random_bytes(16)) . '.' . $ext;
        $originalPath = $originalDir . $filename;

        if (!move_uploaded_file($tmpName, $originalPath)) {
            continue;
        }

        // Create JPEG versions
        createThumbnail($originalPath, $thumbDir . $filename, 400, 400, false);
        createThumbnail($originalPath, $mediumDir . $filename, 800, 800, false);
        createThumbnail($originalPath, $largeDir . $filename, 1200, 1200, false);
        
        // Create WebP versions
        $webpFilename = pathinfo($filename, PATHINFO_FILENAME) . '.webp';
        if (function_exists('imagewebp')) {
            createThumbnail($originalPath, $thumbWebpDir . $webpFilename, 400, 400, true);
            createThumbnail($originalPath, $mediumWebpDir . $webpFilename, 800, 800, true);
            createThumbnail($originalPath, $largeWebpDir . $webpFilename, 1200, 1200, true);
        }

        // Get current max display_order for this product
        $orderStmt = $pdo->prepare('SELECT COALESCE(MAX(display_order), -1) + 1 AS next_order FROM product_images WHERE product_id = ?');
        $orderStmt->execute([$productId]);
        $nextOrder = (int) $orderStmt->fetchColumn();

        $stmt = $pdo->prepare('
            INSERT INTO product_images (product_id, filename, display_order)
            VALUES (:product_id, :filename, :display_order)
        ');
        $stmt->execute([
            'product_id' => $productId,
            'filename' => $filename,
            'display_order' => $nextOrder,
        ]);
    }
}

// Standalone script execution (for direct POST requests)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['product_id'])) {
    requireAdmin();

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

    handleImageUpload($productId, $_FILES['images']);

    header('Location: /admin/products/edit.php?id=' . $productId);
    exit;
}

// If called as function (from create.php), the function is already defined above



