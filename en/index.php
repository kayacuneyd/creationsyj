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

$heroTitle = getSiteSetting('hero_title_en', 'Give a Second Life to Your Objects');
$heroSubtitle = getSiteSetting('hero_subtitle_en', 'Unique creations made from recycled materials by Yasemin Jemmely in Gruyère, Switzerland.');
$heroCta = getSiteSetting('hero_cta_en', 'Explore Creations');

include __DIR__ . '/../includes/header.php';
?>

<section class="hero">
    <div class="container">
        <h1 class="hero-title"><?php echo e($heroTitle); ?></h1>
        <p class="hero-subtitle">
            <?php echo e($heroSubtitle); ?>
        </p>
        <a href="/en/products.php" class="btn-primary">
            <?php echo e($heroCta); ?>
        </a>
    </div>
</section>

<section class="section" style="background-color: var(--off-white);">
    <div class="container">
        <h2 class="section-title">Why interiors with soul trust Creations JY</h2>
        <div style="display: grid; gap: 1rem; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));">
            <article style="background-color: #fff; border-radius: 0.75rem; padding: 1.25rem; box-shadow: 0 10px 30px rgba(0, 0, 0, 0.06);">
                <h3 style="margin-top: 0;">Curated sourcing</h3>
                <p style="color: #8B7F7F; margin-bottom: 0;">
                    Materials are handpicked across Switzerland, restored in Gruyère and matched to highlight their patina.
                </p>
            </article>
            <article style="background-color: #fff; border-radius: 0.75rem; padding: 1.25rem; box-shadow: 0 10px 30px rgba(0, 0, 0, 0.06);">
                <h3 style="margin-top: 0;">Retail-grade storytelling</h3>
                <p style="color: #8B7F7F; margin-bottom: 0;">
                    Product sheets share measurements, textures and availability so you can order confidently from your phone.
                </p>
            </article>
            <article style="background-color: #fff; border-radius: 0.75rem; padding: 1.25rem; box-shadow: 0 10px 30px rgba(0, 0, 0, 0.06);">
                <h3 style="margin-top: 0;">Human WhatsApp flow</h3>
                <p style="color: #8B7F7F; margin-bottom: 0;">
                    Tap the button, chat directly with Yasemin, customise or reserve instantly—no forms, just conversation.
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
        <h2 class="section-title">Your experience, from first scroll to delivery</h2>
        <ol style="padding-left: 1.25rem; color: #8B7F7F;">
            <li style="margin-bottom: 0.5rem;">
                Discover the constantly refreshed gallery and shortlist the pieces that resonate.
            </li>
            <li style="margin-bottom: 0.5rem;">
                Start a WhatsApp chat to get live photos, confirm availability or tailor dimensions.
            </li>
            <li>
                Secure payment, pickup or shipping arrangements directly with the studio.
            </li>
        </ol>
        <div style="margin-top: 1.5rem;">
            <h3 style="margin-bottom: 0.5rem;">Built for</h3>
            <ul style="color: #8B7F7F; padding-left: 1.25rem;">
                <li>Interior designers seeking rare focal points</li>
                <li>Boutiques and hotels wanting characterful décor</li>
                <li>Collectors who value sustainable luxury</li>
            </ul>
            <p>
                Weekly drops mirror what leaves the Gruyère workshop so you can source remotely with the confidence of an in-person visit.
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
