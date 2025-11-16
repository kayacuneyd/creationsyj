<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/language.php';
require_once __DIR__ . '/../includes/whatsapp.php';

$lang = 'en';

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($id <= 0) {
    http_response_code(404);
    echo 'Product not found.';
    exit;
}

$stmt = $pdo->prepare("
    SELECT p.*, pt.title, pt.description, pt.materials, pt.dimensions
    FROM products p
    JOIN product_translations pt 
      ON p.id = pt.product_id AND pt.language_code = 'en'
    WHERE p.id = :id
    LIMIT 1
");
$stmt->execute(['id' => $id]);
$product = $stmt->fetch();

if (!$product) {
    http_response_code(404);
    echo 'Product not found.';
    exit;
}

$imagesStmt = $pdo->prepare("
    SELECT * 
    FROM product_images 
    WHERE product_id = :id
    ORDER BY is_primary DESC, display_order ASC
");
$imagesStmt->execute(['id' => $id]);
$images = $imagesStmt->fetchAll();

include __DIR__ . '/../includes/header.php';
?>

<section class="section">
    <div class="container" style="display: grid; gap: 2rem; grid-template-columns: minmax(0, 1fr);">
        <div>
            <?php if ($images): ?>
                <div>
                    <img src="/uploads/products/medium/<?php echo e($images[0]['filename']); ?>" alt="<?php echo e($product['title']); ?>" style="width: 100%; border-radius: 0.75rem;" loading="lazy">
                </div>
                <?php if (count($images) > 1): ?>
                    <div style="margin-top: 0.75rem; display: flex; gap: 0.5rem; overflow-x: auto;">
                        <?php foreach ($images as $index => $img): ?>
                            <?php if ($index === 0) { continue; } ?>
                            <img src="/uploads/products/thumbnail/<?php echo e($img['filename']); ?>" alt="<?php echo e($product['title']); ?>" style="width: 80px; height: 80px; object-fit: cover; border-radius: 0.5rem;" loading="lazy">
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            <?php else: ?>
                <img src="/assets/images/placeholder.jpg" alt="<?php echo e($product['title']); ?>" style="width: 100%; border-radius: 0.75rem;" loading="lazy">
            <?php endif; ?>
        </div>

        <div>
            <h1 class="section-title"><?php echo e($product['title']); ?></h1>
            <p style="margin-bottom: 0.5rem;">
                <span class="badge <?php echo $product['status'] === 'sold' ? 'badge-sold' : ($product['status'] === 'reserved' ? 'badge-reserved' : 'badge-available'); ?>">
                    <?php echo e(t($product['status'])); ?>
                </span>
            </p>
            <?php if (!empty($product['sku'])): ?>
                <p style="color: #8B7F7F; font-size: 0.9rem;">SKU: <?php echo e($product['sku']); ?></p>
            <?php endif; ?>

            <?php if (!empty($product['description'])): ?>
                <h2 style="margin-top: 1.5rem;">Description</h2>
                <p><?php echo nl2br(e($product['description'])); ?></p>
            <?php endif; ?>

            <?php if (!empty($product['materials'])): ?>
                <h2 style="margin-top: 1rem;">Materials</h2>
                <p><?php echo nl2br(e($product['materials'])); ?></p>
            <?php endif; ?>

            <?php if (!empty($product['dimensions'])): ?>
                <h2 style="margin-top: 1rem;">Dimensions</h2>
                <p><?php echo e($product['dimensions']); ?></p>
            <?php endif; ?>

            <div style="margin-top: 1.5rem;">
                <button
                    type="button"
                    class="btn-primary"
                    onclick="(function(){ fetch('/api/log-inquiry.php',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({product_id:<?php echo (int) $product['id']; ?>})}).catch(function(){}); window.open('<?php echo e(getWhatsAppLink($product['id'])); ?>','_blank'); })();"
                >
                    Contact via WhatsApp
                </button>
            </div>
        </div>
    </div>
</section>

<?php include __DIR__ . '/../includes/footer.php'; ?>


