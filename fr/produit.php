<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/language.php';
require_once __DIR__ . '/../includes/whatsapp.php';

$lang = 'fr';

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($id <= 0) {
    http_response_code(404);
    echo 'Produit introuvable.';
    exit;
}

$stmt = $pdo->prepare("
    SELECT p.*, pt.title, pt.description, pt.materials, pt.dimensions, pt.meta_title, pt.meta_description
    FROM products p
    JOIN product_translations pt 
      ON p.id = pt.product_id AND pt.language_code = 'fr'
    WHERE p.id = :id
    LIMIT 1
");
$stmt->execute(['id' => $id]);
$product = $stmt->fetch();

if (!$product) {
    http_response_code(404);
    echo 'Produit introuvable.';
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

// Prepare meta data for header
$pageType = 'product';
$primaryImage = $images ? (SITE_URL . '/uploads/products/medium/' . $images[0]['filename']) : (SITE_URL . '/assets/images/placeholder.jpg');
$metaData = [
    'title' => $product['meta_title'] ?: ($product['title'] . ' | Créations JY'),
    'description' => $product['meta_description'] ?: mb_substr(strip_tags($product['description'] ?? ''), 0, 160, 'UTF-8'),
    'image' => $primaryImage,
    'product' => $product,
];

include __DIR__ . '/../includes/header.php';
?>

<section class="section">
    <div class="container" style="display: grid; gap: 2rem; grid-template-columns: minmax(0, 1fr);">
        <div>
            <?php if ($images): ?>
                <div class="product-gallery">
                    <div>
                        <?php
                        $webpFilename = pathinfo($images[0]['filename'], PATHINFO_FILENAME) . '.webp';
                        $webpPath = '/uploads/products/medium/webp/' . $webpFilename;
                        $jpegPath = '/uploads/products/medium/' . $images[0]['filename'];
                        $webpExists = file_exists($_SERVER['DOCUMENT_ROOT'] . $webpPath);
                        ?>
                        <picture>
                            <?php if ($webpExists): ?>
                                <source srcset="<?php echo e($webpPath); ?>" type="image/webp">
                            <?php endif; ?>
                            <img 
                                src="<?php echo e($jpegPath); ?>" 
                                alt="<?php echo e($product['title']); ?>" 
                                data-gallery-image
                                data-full-image="/uploads/products/large/<?php echo e($images[0]['filename']); ?>"
                                style="width: 100%; border-radius: 0.75rem;" 
                                loading="lazy"
                            >
                        </picture>
                    </div>
                    <?php if (count($images) > 1): ?>
                        <div class="product-gallery-thumbnails">
                            <?php foreach ($images as $index => $img): ?>
                                <?php
                                $thumbWebpFilename = pathinfo($img['filename'], PATHINFO_FILENAME) . '.webp';
                                $thumbWebpPath = '/uploads/products/thumbnail/webp/' . $thumbWebpFilename;
                                $thumbJpegPath = '/uploads/products/thumbnail/' . $img['filename'];
                                $thumbWebpExists = file_exists($_SERVER['DOCUMENT_ROOT'] . $thumbWebpPath);
                                ?>
                                <picture>
                                    <?php if ($thumbWebpExists): ?>
                                        <source srcset="<?php echo e($thumbWebpPath); ?>" type="image/webp">
                                    <?php endif; ?>
                                    <img 
                                        src="<?php echo e($thumbJpegPath); ?>" 
                                        alt="<?php echo e($product['title']); ?>" 
                                        data-gallery-image
                                        data-full-image="/uploads/products/large/<?php echo e($img['filename']); ?>"
                                        <?php echo $index === 0 ? 'class="active"' : ''; ?>
                                        loading="lazy"
                                    >
                                </picture>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
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
                <p style="color: #8B7F7F; font-size: 0.9rem;">Référence: <?php echo e($product['sku']); ?></p>
            <?php endif; ?>

            <?php if (!empty($product['description'])): ?>
                <h2 style="margin-top: 1.5rem;">Description</h2>
                <p><?php echo nl2br(e($product['description'])); ?></p>
            <?php endif; ?>

            <?php if (!empty($product['materials'])): ?>
                <h2 style="margin-top: 1rem;">Matériaux</h2>
                <p><?php echo nl2br(e($product['materials'])); ?></p>
            <?php endif; ?>

            <?php if (!empty($product['dimensions'])): ?>
                <h2 style="margin-top: 1rem;">Dimensions</h2>
                <p><?php echo e($product['dimensions']); ?></p>
            <?php endif; ?>

            <div style="margin-top: 1.5rem;">
                <h2 style="margin-bottom: 1rem;">Contacter via WhatsApp</h2>
                <form id="whatsapp-inquiry-form" style="display: flex; flex-direction: column; gap: 1rem; max-width: 500px;">
                    <div>
                        <label for="customer_name" style="display: block; margin-bottom: 0.5rem; font-weight: 500;">Nom *</label>
                        <input 
                            type="text" 
                            id="customer_name" 
                            name="customer_name" 
                            class="input" 
                            required
                            placeholder="Votre nom"
                        >
                    </div>
                    <div>
                        <label for="message" style="display: block; margin-bottom: 0.5rem; font-weight: 500;">Message</label>
                        <textarea 
                            id="message" 
                            name="message" 
                            class="textarea" 
                            rows="4"
                            placeholder="Votre message (optionnel)"
                        ></textarea>
                    </div>
                    <button type="submit" class="btn-primary">
                        Envoyer via WhatsApp
                    </button>
                </form>
            </div>
        </div>
    </div>
</section>

<!-- Lightbox Modal -->
<div class="lightbox">
    <button class="lightbox-close" aria-label="Fermer">&times;</button>
    <button class="lightbox-prev" aria-label="Image précédente">&#8249;</button>
    <button class="lightbox-next" aria-label="Image suivante">&#8250;</button>
    <div class="lightbox-content">
        <picture>
            <source class="lightbox-source" srcset="" type="image/webp">
            <img class="lightbox-image" src="" alt="">
        </picture>
        <div class="lightbox-counter"></div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var form = document.getElementById('whatsapp-inquiry-form');
    if (form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            var customerName = document.getElementById('customer_name').value.trim();
            var message = document.getElementById('message').value.trim();
            var productId = <?php echo (int) $product['id']; ?>;
            
            // Log inquiry
            fetch('/api/log-inquiry.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({
                    product_id: productId,
                    customer_name: customerName,
                    message: message
                })
            }).catch(function() {});
            
            // Build WhatsApp link
            var phone = '<?php echo e(str_replace(['+', ' ', '-'], '', getWhatsAppNumber())); ?>';
            var lang = 'fr';
            var productTitle = <?php echo json_encode($product['title']); ?>;
            var productUrl = '<?php echo rtrim(SITE_URL, '/'); ?>/fr/produit/<?php echo e($product['slug'] ?? $product['id']); ?>';
            
            var whatsappMessage = 'Bonjour' + (customerName ? ', je m\'appelle ' + customerName : '') + ', je suis intéressé(e) par ce produit: ' + productTitle + ' - ' + productUrl;
            if (message) {
                whatsappMessage += '\n\n' + message;
            }
            
            var whatsappLink = 'https://wa.me/' + phone + '?text=' + encodeURIComponent(whatsappMessage);
            window.open(whatsappLink, '_blank');
        });
    }
});
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>

