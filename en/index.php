<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/language.php';
require_once __DIR__ . '/../includes/whatsapp.php';

$lang = 'en';

$stmt = $pdo->query("
    SELECT p.id, p.slug, p.status, p.featured,
           pt.title, pt.description,
           (
               SELECT filename 
               FROM product_images 
               WHERE product_id = p.id 
               ORDER BY is_primary DESC, display_order ASC 
               LIMIT 1
           ) AS image
    FROM products p
    JOIN product_translations pt 
      ON p.id = pt.product_id AND pt.language_code = 'en'
    WHERE p.featured = 1
    ORDER BY p.created_at DESC
    LIMIT 6
");
$featuredProducts = $stmt->fetchAll();

include __DIR__ . '/../includes/header.php';
?>

<section class="hero">
    <div class="container">
        <h1 class="hero-title">Give a Second Life to Your Objects</h1>
        <p class="hero-subtitle">
            Unique creations made from recycled materials by Yasemin Jemmely in Gruyère, Switzerland.
        </p>
        <a href="/en/products.php" class="btn-primary">
            Explore Creations
        </a>
    </div>
</section>

<section class="section" style="background-color: var(--off-white);">
    <div class="container">
        <h2 class="section-title">The Creations JY Promise</h2>
        <div style="display: grid; gap: 1rem; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));">
            <article style="background-color: #fff; border-radius: 0.75rem; padding: 1.25rem; box-shadow: 0 10px 30px rgba(0, 0, 0, 0.06);">
                <h3 style="margin-top: 0;">High-end upcycling</h3>
                <p style="color: #8B7F7F; margin-bottom: 0;">
                    Second-hand, antique and forgotten materials are sourced with care, then recomposed
                    into one-of-a-kind decorative pieces that feel timeless.
                </p>
            </article>
            <article style="background-color: #fff; border-radius: 0.75rem; padding: 1.25rem; box-shadow: 0 10px 30px rgba(0, 0, 0, 0.06);">
                <h3 style="margin-top: 0;">WhatsApp powered ordering</h3>
                <p style="color: #8B7F7F; margin-bottom: 0;">
                    The website acts as a refined showroom. When you are ready, WhatsApp Business picks up the conversation
                    for quotes, extra photos and the final order.
                </p>
            </article>
            <article style="background-color: #fff; border-radius: 0.75rem; padding: 1.25rem; box-shadow: 0 10px 30px rgba(0, 0, 0, 0.06);">
                <h3 style="margin-top: 0;">Shabby-chic palette</h3>
                <p style="color: #8B7F7F; margin-bottom: 0;">
                    Dusty rose accents, cream backdrops and warm greys echo the design system defined for the brand
                    and set the tone for every photoshoot.
                </p>
            </article>
        </div>
    </div>
</section>

<section class="section">
    <div class="container">
        <h2 class="section-title">Featured Products</h2>
        <?php if ($featuredProducts): ?>
            <div class="product-grid">
                <?php foreach ($featuredProducts as $product): ?>
                    <?php
                    $statusClass = 'badge-available';
                    if ($product['status'] === 'sold') {
                        $statusClass = 'badge-sold';
                    } elseif ($product['status'] === 'reserved') {
                        $statusClass = 'badge-reserved';
                    }
                    ?>
                    <article class="product-card" data-product-card data-title="<?php echo e($product['title']); ?>">
                        <div class="relative">
                            <?php if ($product['image']): ?>
                                <?php
                                $thumbWebpFilename = pathinfo($product['image'], PATHINFO_FILENAME) . '.webp';
                                $thumbWebpPath = '/uploads/products/thumbnail/webp/' . $thumbWebpFilename;
                                $thumbJpegPath = '/uploads/products/thumbnail/' . $product['image'];
                                $thumbWebpExists = file_exists($_SERVER['DOCUMENT_ROOT'] . $thumbWebpPath);
                                ?>
                                <picture>
                                    <?php if ($thumbWebpExists): ?>
                                        <source srcset="<?php echo e($thumbWebpPath); ?>" type="image/webp">
                                    <?php endif; ?>
                                    <img src="<?php echo e($thumbJpegPath); ?>" alt="<?php echo e($product['title']); ?>" loading="lazy">
                                </picture>
                            <?php else: ?>
                                <img src="/assets/images/placeholder.jpg" alt="<?php echo e($product['title']); ?>" loading="lazy">
                            <?php endif; ?>
                        </div>
                        <div class="container" style="padding: 1rem 1.25rem 1.25rem;">
                            <span class="badge <?php echo $statusClass; ?>">
                                <?php echo e(t($product['status'])); ?>
                            </span>
                            <h3 style="margin-top: 0.75rem; margin-bottom: 0.5rem;">
                                <?php echo e($product['title']); ?>
                            </h3>
                            <p style="margin-bottom: 0.75rem; color: #8B7F7F; font-size: 0.9rem;">
                                <?php echo e(mb_strimwidth($product['description'] ?? '', 0, 120, '…', 'UTF-8')); ?>
                            </p>
                            <div style="display: flex; gap: 0.5rem;">
                                <a class="btn-primary" style="flex: 1;" href="/en/product.php?id=<?php echo (int) $product['id']; ?>">
                                    View Product
                                </a>
                                <a class="btn-primary" style="flex: 1; background-color: var(--sage-green);" href="<?php echo e(getWhatsAppLink((int) $product['id'])); ?>" target="_blank" rel="noopener">
                                    WhatsApp
                                </a>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
<?php else: ?>
            <p>No featured products yet.</p>
        <?php endif; ?>
    </div>
</section>

<section class="section">
    <div class="container">
        <h2 class="section-title">How it works</h2>
        <ol style="padding-left: 1.25rem; color: #8B7F7F;">
            <li style="margin-bottom: 0.5rem;">
                Browse the mobile-first gallery to spot the latest available, reserved or sold pieces.
            </li>
            <li style="margin-bottom: 0.5rem;">
                Tap the WhatsApp CTA to request a quote, a quick video or material details in real time.
            </li>
            <li>
                Finalise the order, pick-up or shipping plan directly with Yasemin in the chat.
            </li>
        </ol>
        <div style="margin-top: 1.5rem;">
            <h3 style="margin-bottom: 0.5rem;">Who we create for</h3>
            <ul style="color: #8B7F7F; padding-left: 1.25rem;">
                <li>Vintage and shabby-chic lovers hunting for statement pieces.</li>
                <li>Eco-conscious buyers eager to support Swiss craftsmanship.</li>
                <li>Swiss and European clients who mostly browse on mobile (70%+ expected traffic).</li>
            </ul>
            <p>
                Each item is documented from the Gruyère workshop so you can feel the textures, provenance and story
                whether you are nearby or abroad.
            </p>
        </div>
    </div>
</section>

<section class="section" style="background-color: var(--off-white);">
    <div class="container">
        <h2 class="section-title">About Yasemin</h2>
        <p>
            Yasemin transforms forgotten objects into unique pieces, ready to start a new story in your home.
        </p>
        <a href="/en/about.php" class="btn-primary" style="margin-top: 1rem;">
            Learn more
        </a>
    </div>
</section>

<?php include __DIR__ . '/../includes/footer.php'; ?>

