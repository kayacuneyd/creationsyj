<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';

requireAdmin();

global $pdo;

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
if ($id <= 0) {
    http_response_code(404);
    exit('Product not found.');
}

// Load base product
$stmt = $pdo->prepare('SELECT * FROM products WHERE id = :id LIMIT 1');
$stmt->execute(['id' => $id]);
$product = $stmt->fetch();

if (!$product) {
    http_response_code(404);
    exit('Product not found.');
}

// Load translations
$tStmt = $pdo->prepare('SELECT * FROM product_translations WHERE product_id = :id');
$tStmt->execute(['id' => $id]);
$translations = [];
foreach ($tStmt as $row) {
    $translations[$row['language_code']] = $row;
}

// Load categories
$categories = $pdo->query("
    SELECT c.id, ct.name
    FROM categories c
    LEFT JOIN category_translations ct 
      ON c.id = ct.category_id AND ct.language_code = 'fr'
    ORDER BY ct.name ASC
")->fetchAll();

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf_token'] ?? null;
    if (!verifyCSRFToken($token)) {
        $errors[] = 'Security check failed, please try again.';
    }

    $categoryId = (int) ($_POST['category_id'] ?? 0);
    $slug = trim($_POST['slug'] ?? '');
    $sku = trim($_POST['sku'] ?? '');
    $status = $_POST['status'] ?? 'available';
    $featured = isset($_POST['featured']) ? 1 : 0;

    $titleFr = trim($_POST['title_fr'] ?? '');
    $descFr = trim($_POST['description_fr'] ?? '');
    $materialsFr = trim($_POST['materials_fr'] ?? '');
    $dimensionsFr = trim($_POST['dimensions_fr'] ?? '');

    $titleEn = trim($_POST['title_en'] ?? '');
    $descEn = trim($_POST['description_en'] ?? '');
    $materialsEn = trim($_POST['materials_en'] ?? '');
    $dimensionsEn = trim($_POST['dimensions_en'] ?? '');

    if ($categoryId <= 0) {
        $errors[] = 'Please choose a category.';
    }
    if ($titleFr === '' || $titleEn === '') {
        $errors[] = 'Titles in both FR and EN are required.';
    }
    if ($slug === '') {
        $slug = strtolower(preg_replace('/[^a-z0-9]+/i', '-', $titleEn));
    }

    if (!$errors) {
        $pdo->beginTransaction();
        try {
            $uStmt = $pdo->prepare('
                UPDATE products
                   SET category_id = :category_id,
                       slug = :slug,
                       sku = :sku,
                       status = :status,
                       featured = :featured
                 WHERE id = :id
            ');
            $uStmt->execute([
                'category_id' => $categoryId,
                'slug' => $slug,
                'sku' => $sku !== '' ? $sku : null,
                'status' => $status,
                'featured' => $featured,
                'id' => $id,
            ]);

            $upStmt = $pdo->prepare('
                UPDATE product_translations
                   SET title = :title,
                       description = :description,
                       materials = :materials,
                       dimensions = :dimensions
                 WHERE product_id = :product_id
                   AND language_code = :lang
            ');

            $upStmt->execute([
                'product_id' => $id,
                'lang' => 'fr',
                'title' => $titleFr,
                'description' => $descFr,
                'materials' => $materialsFr,
                'dimensions' => $dimensionsFr,
            ]);

            $upStmt->execute([
                'product_id' => $id,
                'lang' => 'en',
                'title' => $titleEn,
                'description' => $descEn,
                'materials' => $materialsEn,
                'dimensions' => $dimensionsEn,
            ]);

            $pdo->commit();
            $success = true;

            // Refresh product/translation data
            header('Location: /admin/products/edit.php?id=' . $id . '&saved=1');
            exit;
        } catch (Throwable $e) {
            $pdo->rollBack();
            $errors[] = 'Error updating product: ' . $e->getMessage();
        }
    }
}

$saved = isset($_GET['saved']);

// Helpers to populate form
$trFr = $translations['fr'] ?? ['title' => '', 'description' => '', 'materials' => '', 'dimensions' => ''];
$trEn = $translations['en'] ?? ['title' => '', 'description' => '', 'materials' => '', 'dimensions' => ''];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Edit product · Admin · Creations JY</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="/assets/css/main.css">
</head>
<body>
    <header class="site-header">
        <div class="site-header-inner container">
            <strong>Edit product</strong>
            <nav class="site-nav">
                <a href="/admin/index.php">Dashboard</a>
                <a href="/admin/products/index.php">Products</a>
                <a href="/admin/logout.php">Logout</a>
            </nav>
        </div>
    </header>
    <main>
        <section class="section">
            <div class="container">
                <h1 class="section-title">Edit product #<?php echo (int) $product['id']; ?></h1>
                <?php if ($saved): ?>
                    <p style="margin-top: 1rem; padding: 0.75rem 1rem; background-color: #E8F5E9; border-radius: 0.5rem;">
                        Product updated successfully.
                    </p>
                <?php endif; ?>
                <?php if ($errors): ?>
                    <ul style="margin-top: 1rem; padding: 0.75rem 1rem; background-color: #FFEBEE; border-radius: 0.5rem;">
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo e($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>

                <form method="post">
                    <input type="hidden" name="csrf_token" value="<?php echo e(generateCSRFToken()); ?>">

                    <h2>Basics</h2>
                    <div style="margin-bottom: 1rem;">
                        <label for="category_id">Category</label>
                        <select id="category_id" name="category_id" class="select" required>
                            <option value="">Choose…</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?php echo (int) $cat['id']; ?>" <?php echo $product['category_id'] == $cat['id'] ? 'selected' : ''; ?>>
                                    <?php echo e($cat['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div style="margin-bottom: 1rem;">
                        <label for="slug">Slug</label>
                        <input id="slug" name="slug" type="text" class="input" value="<?php echo e($product['slug']); ?>">
                    </div>
                    <div style="margin-bottom: 1rem;">
                        <label for="sku">SKU (optional)</label>
                        <input id="sku" name="sku" type="text" class="input" value="<?php echo e($product['sku']); ?>">
                    </div>
                    <div style="margin-bottom: 1rem;">
                        <label for="status">Status</label>
                        <select id="status" name="status" class="select">
                            <option value="available" <?php echo $product['status'] === 'available' ? 'selected' : ''; ?>>Available</option>
                            <option value="sold" <?php echo $product['status'] === 'sold' ? 'selected' : ''; ?>>Sold</option>
                            <option value="reserved" <?php echo $product['status'] === 'reserved' ? 'selected' : ''; ?>>Reserved</option>
                        </select>
                    </div>
                    <div style="margin-bottom: 1rem;">
                        <label>
                            <input type="checkbox" name="featured" value="1" <?php echo $product['featured'] ? 'checked' : ''; ?>>
                            Featured on homepage
                        </label>
                    </div>

                    <h2>Content (FR)</h2>
                    <div style="margin-bottom: 1rem;">
                        <label for="title_fr">Title (FR)</label>
                        <input id="title_fr" name="title_fr" type="text" class="input" required value="<?php echo e($trFr['title'] ?? ''); ?>">
                    </div>
                    <div style="margin-bottom: 1rem;">
                        <label for="description_fr">Description (FR)</label>
                        <textarea id="description_fr" name="description_fr" rows="4" class="textarea"><?php echo e($trFr['description'] ?? ''); ?></textarea>
                    </div>
                    <div style="margin-bottom: 1rem;">
                        <label for="materials_fr">Materials (FR)</label>
                        <textarea id="materials_fr" name="materials_fr" rows="3" class="textarea"><?php echo e($trFr['materials'] ?? ''); ?></textarea>
                    </div>
                    <div style="margin-bottom: 1rem;">
                        <label for="dimensions_fr">Dimensions (FR)</label>
                        <input id="dimensions_fr" name="dimensions_fr" type="text" class="input" value="<?php echo e($trFr['dimensions'] ?? ''); ?>">
                    </div>

                    <h2>Content (EN)</h2>
                    <div style="margin-bottom: 1rem;">
                        <label for="title_en">Title (EN)</label>
                        <input id="title_en" name="title_en" type="text" class="input" required value="<?php echo e($trEn['title'] ?? ''); ?>">
                    </div>
                    <div style="margin-bottom: 1rem;">
                        <label for="description_en">Description (EN)</label>
                        <textarea id="description_en" name="description_en" rows="4" class="textarea"><?php echo e($trEn['description'] ?? ''); ?></textarea>
                    </div>
                    <div style="margin-bottom: 1rem;">
                        <label for="materials_en">Materials (EN)</label>
                        <textarea id="materials_en" name="materials_en" rows="3" class="textarea"><?php echo e($trEn['materials'] ?? ''); ?></textarea>
                    </div>
                    <div style="margin-bottom: 1rem;">
                        <label for="dimensions_en">Dimensions (EN)</label>
                        <input id="dimensions_en" name="dimensions_en" type="text" class="input" value="<?php echo e($trEn['dimensions'] ?? ''); ?>">
                    </div>

                    <button type="submit" class="btn-primary" style="margin-top: 1rem;">
                        Save changes
                    </button>
                </form>

                <hr style="margin: 2rem 0;">

                <h2>Images</h2>
                <form method="post" action="/admin/products/upload-image.php" enctype="multipart/form-data" style="margin-top: 1rem;">
                    <input type="hidden" name="csrf_token" value="<?php echo e(generateCSRFToken()); ?>">
                    <input type="hidden" name="product_id" value="<?php echo (int) $product['id']; ?>">
                    <div style="margin-bottom: 1rem;">
                        <input type="file" name="images[]" multiple accept="image/jpeg,image/png,image/webp">
                    </div>
                    <button type="submit" class="btn-primary">Upload images</button>
                </form>
            </div>
        </section>
    </main>
</body>
</html>


