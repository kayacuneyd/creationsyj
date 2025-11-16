<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/language.php';
require_once __DIR__ . '/../includes/whatsapp.php';

$lang = 'fr';

// Fetch a few featured products
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
      ON p.id = pt.product_id AND pt.language_code = 'fr'
    WHERE p.featured = 1
    ORDER BY p.created_at DESC
    LIMIT 6
");
$featuredProducts = $stmt->fetchAll();

$heroTitle = getSiteSetting('hero_title_fr', 'Donnez une seconde vie à vos objets');
$heroSubtitle = getSiteSetting('hero_subtitle_fr', 'Créations uniques fabriquées à partir de matériaux recyclés par Yasemin Jemmely, en Gruyère.');
$heroCta = getSiteSetting('hero_cta_fr', 'Découvrir les créations');

include __DIR__ . '/../includes/header.php';
?>

<section class="hero">
    <div class="container">
        <h1 class="hero-title"><?php echo e($heroTitle); ?></h1>
        <p class="hero-subtitle">
            <?php echo e($heroSubtitle); ?>
        </p>
        <a href="/fr/produits.php" class="btn-primary">
            <?php echo e($heroCta); ?>
        </a>
    </div>
</section>

<section class="section" style="background-color: var(--off-white);">
    <div class="container">
        <h2 class="section-title">Pourquoi les intérieurs amoureux de l’âme vintage nous choisissent</h2>
        <div style="display: grid; gap: 1rem; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));">
            <article style="background-color: #fff; border-radius: 0.75rem; padding: 1.25rem; box-shadow: 0 10px 30px rgba(0, 0, 0, 0.06);">
                <h3 style="margin-top: 0;">Sélection signature</h3>
                <p style="color: #8B7F7F; margin-bottom: 0;">
                    Chaque matière est chinée en Suisse romande puis restaurée à la main pour préserver patines et histoires.
                </p>
            </article>
            <article style="background-color: #fff; border-radius: 0.75rem; padding: 1.25rem; box-shadow: 0 10px 30px rgba(0, 0, 0, 0.06);">
                <h3 style="margin-top: 0;">Processus boutique</h3>
                <p style="color: #8B7F7F; margin-bottom: 0;">
                    Les fiches produits détaillent les formats, les textures et les statuts pour que vous sachiez exactement ce que vous adoptez.
                </p>
            </article>
            <article style="background-color: #fff; border-radius: 0.75rem; padding: 1.25rem; box-shadow: 0 10px 30px rgba(0, 0, 0, 0.06);">
                <h3 style="margin-top: 0;">Accompagnement humain</h3>
                <p style="color: #8B7F7F; margin-bottom: 0;">
                    Une simple discussion WhatsApp suffit pour réserver, commander, personnaliser ou programmer un retrait atelier.
                </p>
            </article>
        </div>
    </div>
</section>

<section class="section">
    <div class="container">
        <h2 class="section-title">Produits en vedette</h2>
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
                                <a class="btn-primary" style="flex: 1;" href="/fr/produit.php?id=<?php echo (int) $product['id']; ?>">
                                    Voir le produit
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
            <p>Aucun produit en vedette pour le moment.</p>
        <?php endif; ?>
    </div>
</section>

<section class="section">
    <div class="container">
        <h2 class="section-title">Un parcours simple, même depuis votre smartphone</h2>
        <ol style="padding-left: 1.25rem; color: #8B7F7F;">
            <li style="margin-bottom: 0.5rem;">
                Explorez la galerie mobile pour découvrir les pièces disponibles, les réservations ou les trésors déjà adoptés.
            </li>
            <li style="margin-bottom: 0.5rem;">
                Appuyez sur « WhatsApp » pour demander des photos live, confirmer une personnalisation ou lancer un devis.
            </li>
            <li>
                Validez le paiement sécurisé, la livraison ou la collecte atelier directement avec Yasemin.
            </li>
        </ol>
        <div style="margin-top: 1.5rem;">
            <h3 style="margin-bottom: 0.5rem;">Pensé pour vos projets</h3>
            <ul style="color: #8B7F7F; padding-left: 1.25rem;">
                <li>Décorations d’intérieur et shootings lifestyle</li>
                <li>Hôtellerie de charme et boutiques concept</li>
                <li>Cadeaux d’entreprise responsables</li>
            </ul>
            <p>
                Nous mettons à jour la collection chaque semaine afin de synchroniser l’expérience digitale sur ce qui sort réellement de l’atelier.
            </p>
        </div>
    </div>
</section>

<section class="section" style="background-color: var(--off-white);">
    <div class="container">
        <h2 class="section-title">À propos de Yasemin</h2>
        <p>
            Yasemin transforme des objets oubliés en pièces uniques, prêtes à vivre une nouvelle histoire dans votre intérieur.
        </p>
        <a href="/fr/a-propos.php" class="btn-primary" style="margin-top: 1rem;">
            En savoir plus
        </a>
    </div>
</section>

<?php include __DIR__ . '/../includes/footer.php'; ?>
