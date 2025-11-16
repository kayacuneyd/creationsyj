<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/language.php';
require_once __DIR__ . '/../includes/whatsapp.php';

$lang = 'fr';

// Fetch categories for filter
$categoriesStmt = $pdo->query("
    SELECT c.id, ct.name
    FROM categories c
    JOIN category_translations ct 
      ON c.id = ct.category_id AND ct.language_code = 'fr'
    ORDER BY ct.name ASC
");
$categories = $categoriesStmt->fetchAll();

$status = $_GET['status'] ?? '';
$categoryId = isset($_GET['category']) ? (int) $_GET['category'] : null;
$search = trim($_GET['q'] ?? '');
$sort = $_GET['sort'] ?? 'newest';
$page = max(1, (int) ($_GET['page'] ?? 1));
$perPage = 12;
$offset = ($page - 1) * $perPage;

$where = [];
$params = [];

if ($status && in_array($status, ['available', 'sold', 'reserved'], true)) {
    $where[] = 'p.status = :status';
    $params['status'] = $status;
}

if ($categoryId) {
    $where[] = 'p.category_id = :category_id';
    $params['category_id'] = $categoryId;
}

if ($search !== '') {
    $where[] = '(pt.title LIKE :search OR pt.description LIKE :search)';
    $params['search'] = '%' . $search . '%';
}

$whereSql = $where ? ('WHERE ' . implode(' AND ', $where)) : '';

// Build ORDER BY clause
$orderBy = 'p.created_at DESC'; // default: newest
switch ($sort) {
    case 'oldest':
        $orderBy = 'p.created_at ASC';
        break;
    case 'a-z':
        $orderBy = 'pt.title ASC';
        break;
    case 'newest':
    default:
        $orderBy = 'p.created_at DESC';
        break;
}

// Count total
$countSql = "
    SELECT COUNT(*) AS total
    FROM products p
    JOIN product_translations pt 
      ON p.id = pt.product_id AND pt.language_code = 'fr'
    $whereSql
";
$countStmt = $pdo->prepare($countSql);
$countStmt->execute($params);
$total = (int) $countStmt->fetchColumn();
$totalPages = max(1, (int) ceil($total / $perPage));

// Fetch products
$sql = "
    SELECT p.id, p.slug, p.status,
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
    $whereSql
    ORDER BY $orderBy
    LIMIT :limit OFFSET :offset
";

$stmt = $pdo->prepare($sql);
foreach ($params as $key => $value) {
    $stmt->bindValue(':' . $key, $value);
}
$stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$products = $stmt->fetchAll();

include __DIR__ . '/../includes/header.php';
?>

<section class="section">
    <div class="container">
        <h1 class="section-title">Toutes les créations</h1>

        <form method="get" class="filters-grid" style="margin-top: 1rem;">
            <div>
                <label for="category">Catégorie</label>
                <select id="category" name="category" class="select">
                    <option value="">Toutes</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?php echo (int) $cat['id']; ?>" <?php echo $categoryId === (int) $cat['id'] ? 'selected' : ''; ?>>
                            <?php echo e($cat['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label for="status">Statut</label>
                <select id="status" name="status" class="select">
                    <option value="">Tous</option>
                    <option value="available" <?php echo $status === 'available' ? 'selected' : ''; ?>><?php echo e(t('available')); ?></option>
                    <option value="sold" <?php echo $status === 'sold' ? 'selected' : ''; ?>><?php echo e(t('sold')); ?></option>
                    <option value="reserved" <?php echo $status === 'reserved' ? 'selected' : ''; ?>><?php echo e(t('reserved')); ?></option>
                </select>
            </div>
            <div>
                <label for="q">Recherche</label>
                <input id="q" name="q" type="search" class="input" placeholder="Rechercher un produit…" value="<?php echo e($search); ?>" data-product-search>
            </div>
            <div>
                <label for="sort">Trier par</label>
                <select id="sort" name="sort" class="select">
                    <option value="newest" <?php echo $sort === 'newest' ? 'selected' : ''; ?>>Plus récent</option>
                    <option value="oldest" <?php echo $sort === 'oldest' ? 'selected' : ''; ?>>Plus ancien</option>
                    <option value="a-z" <?php echo $sort === 'a-z' ? 'selected' : ''; ?>>A-Z</option>
                </select>
            </div>
            <div style="align-self: end;">
                <button type="submit" class="btn-primary">
                    Filtrer
                </button>
            </div>
        </form>

        <?php if ($products): ?>
            <div class="product-grid" style="margin-top: 1.5rem;">
                <?php foreach ($products as $product): ?>
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
                            <h2 style="margin-top: 0.75rem; margin-bottom: 0.5rem;">
                                <?php echo e($product['title']); ?>
                            </h2>
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

            <?php if ($totalPages > 1): ?>
                <nav style="margin-top: 1.5rem; display: flex; gap: 0.5rem; flex-wrap: wrap;">
                    <?php for ($p = 1; $p <= $totalPages; $p++): ?>
                        <?php
                        $urlParams = $_GET;
                        $urlParams['page'] = $p;
                        $url = '/fr/produits.php?' . http_build_query($urlParams);
                        ?>
                        <a href="<?php echo e($url); ?>" class="btn-primary" style="background-color: <?php echo $p === $page ? 'var(--dusty-rose)' : 'var(--light-gray)'; ?>; color: <?php echo $p === $page ? '#fff' : '#3A3232'; ?>;">
                            <?php echo $p; ?>
                        </a>
                    <?php endfor; ?>
                </nav>
            <?php endif; ?>
        <?php else: ?>
            <p style="margin-top: 1.5rem;">Aucun produit trouvé pour ces critères.</p>
        <?php endif; ?>
    </div>
</section>

<?php include __DIR__ . '/../includes/footer.php'; ?>


