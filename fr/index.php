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

include __DIR__ . '/../includes/header.php';
?>

<section class="hero">
    <div class="container">
        <h1 class="hero-title">Donnez une seconde vie à vos objets</h1>
        <p class="hero-subtitle">
            Créations uniques fabriquées à partir de matériaux recyclés par Yasemin Jemmely, en Gruyère.
        </p>
        <a href="/fr/produits.php" class="btn-primary">
            Découvrir les créations
        </a>
    </div>
</section>

<section class="section" style="background-color: var(--off-white);">
    <div class="container">
        <h2 class="section-title">La promesse de Créations JY</h2>
        <div style="display: grid; gap: 1rem; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));">
            <article style="background-color: #fff; border-radius: 0.75rem; padding: 1.25rem; box-shadow: 0 10px 30px rgba(0, 0, 0, 0.06);">
                <h3 style="margin-top: 0;">Upcycling haut de gamme</h3>
                <p style="color: #8B7F7F; margin-bottom: 0;">
                    La matière première provient de pièces seconde main, antiques et oubliées.
                    Yasemin les associe pour créer des objets uniques, pensés pour durer.
                </p>
            </article>
            <article style="background-color: #fff; border-radius: 0.75rem; padding: 1.25rem; box-shadow: 0 10px 30px rgba(0, 0, 0, 0.06);">
                <h3 style="margin-top: 0;">Commande fluide via WhatsApp</h3>
                <p style="color: #8B7F7F; margin-bottom: 0;">
                    Le site fait office de vitrine : vous explorez, demandez un devis via WhatsApp Business
                    et finalisez la commande directement avec Yasemin.
                </p>
            </article>
            <article style="background-color: #fff; border-radius: 0.75rem; padding: 1.25rem; box-shadow: 0 10px 30px rgba(0, 0, 0, 0.06);">
                <h3 style="margin-top: 0;">Palette shabby chic</h3>
                <p style="color: #8B7F7F; margin-bottom: 0;">
                    L'univers visuel se base sur des roses poudrés, des crèmes douces et des gris chauds,
                    pour conserver l'esprit vintage et raffiné du projet.
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
        <h2 class="section-title">Comment ça marche ?</h2>
        <ol style="padding-left: 1.25rem; color: #8B7F7F;">
            <li style="margin-bottom: 0.5rem;">
                Parcourez la galerie mobile-first pour repérer une pièce disponible, réservée ou vendue.
            </li>
            <li style="margin-bottom: 0.5rem;">
                Ouvrez la conversation WhatsApp pour demander un devis, une vidéo ou des mesures.
            </li>
            <li>
                Finalisez la commande, la remise en main propre ou l'expédition directement avec Yasemin.
            </li>
        </ol>
        <div style="margin-top: 1.5rem;">
            <h3 style="margin-bottom: 0.5rem;">Pour qui créons-nous&nbsp;?</h3>
            <ul style="color: #8B7F7F; padding-left: 1.25rem;">
                <li>Amoureux du vintage et du shabby chic à la recherche de pièces uniques.</li>
                <li>Consommateurs éco-responsables souhaitant soutenir l'artisanat suisse.</li>
                <li>Clients suisses et européens, majoritairement mobiles (70&nbsp;% du trafic attendu).</li>
            </ul>
            <p>
                Toutes les créations sont imaginées dans l'atelier de Gruyère, puis documentées pour vous permettre
                de vivre l'expérience à distance comme sur place.
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

