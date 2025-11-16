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

    $metaTitleFr = trim($_POST['meta_title_fr'] ?? '');
    $metaDescFr = trim($_POST['meta_description_fr'] ?? '');
    $metaTitleEn = trim($_POST['meta_title_en'] ?? '');
    $metaDescEn = trim($_POST['meta_description_en'] ?? '');

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
                       dimensions = :dimensions,
                       meta_title = :meta_title,
                       meta_description = :meta_description
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
                'meta_title' => $metaTitleFr !== '' ? $metaTitleFr : null,
                'meta_description' => $metaDescFr !== '' ? $metaDescFr : null,
            ]);

            $upStmt->execute([
                'product_id' => $id,
                'lang' => 'en',
                'title' => $titleEn,
                'description' => $descEn,
                'materials' => $materialsEn,
                'dimensions' => $dimensionsEn,
                'meta_title' => $metaTitleEn !== '' ? $metaTitleEn : null,
                'meta_description' => $metaDescEn !== '' ? $metaDescEn : null,
            ]);

            $pdo->commit();
            $success = true;

            // Log activity
            logActivity('product_updated', 'products', $id, 'Product updated: ' . $titleEn);

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

// Load product images
$imagesStmt = $pdo->prepare('SELECT * FROM product_images WHERE product_id = :id ORDER BY display_order ASC, id ASC');
$imagesStmt->execute(['id' => $id]);
$productImages = $imagesStmt->fetchAll();

// Handle image management (reorder, set primary, delete)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'manage_images') {
    $token = $_POST['csrf_token'] ?? null;
    if (verifyCSRFToken($token)) {
        // Handle delete
        if (isset($_POST['delete_image_id'])) {
            $deleteId = (int) $_POST['delete_image_id'];
            $delStmt = $pdo->prepare('SELECT filename FROM product_images WHERE id = :id AND product_id = :product_id');
            $delStmt->execute(['id' => $deleteId, 'product_id' => $id]);
            $imgToDelete = $delStmt->fetch();
            if ($imgToDelete) {
                $filename = $imgToDelete['filename'];
                $dirs = ['original', 'thumbnail', 'medium', 'large'];
                foreach ($dirs as $dir) {
                    $path = __DIR__ . '/../../uploads/products/' . $dir . '/' . $filename;
                    if (file_exists($path)) {
                        @unlink($path);
                    }
                }
                $pdo->prepare('DELETE FROM product_images WHERE id = :id')->execute(['id' => $deleteId]);
            }
        }
        // Handle reorder and primary
        if (isset($_POST['image_orders']) && is_array($_POST['image_orders'])) {
            $primaryId = isset($_POST['primary_image']) ? (int) $_POST['primary_image'] : 0;
            // Reset all primary flags
            $pdo->prepare('UPDATE product_images SET is_primary = 0 WHERE product_id = :id')->execute(['id' => $id]);
            // Update orders and set primary
            foreach ($_POST['image_orders'] as $imgId => $order) {
                $imgId = (int) $imgId;
                $order = (int) $order;
                $isPrimary = ($imgId === $primaryId && $primaryId > 0) ? 1 : 0;
                $pdo->prepare('UPDATE product_images SET display_order = :order, is_primary = :primary WHERE id = :id AND product_id = :product_id')
                    ->execute(['order' => $order, 'primary' => $isPrimary, 'id' => $imgId, 'product_id' => $id]);
            }
            logActivity('product_images_reordered', 'product_images', $id, 'Product images reordered');
        }
        header('Location: /admin/products/edit.php?id=' . $id . '&saved=1');
        exit;
    }
}

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
    <link rel="stylesheet" href="/assets/css/admin.css">
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
                    <div style="margin-bottom: 1rem;">
                        <label for="meta_title_fr">Meta Title (FR) - SEO</label>
                        <input id="meta_title_fr" name="meta_title_fr" type="text" class="input" maxlength="70" value="<?php echo e($trFr['meta_title'] ?? ''); ?>">
                        <small style="display: block; margin-top: 0.25rem; color: #8B7F7F;">Recommended: 50-60 characters</small>
                    </div>
                    <div style="margin-bottom: 1rem;">
                        <label for="meta_description_fr">Meta Description (FR) - SEO</label>
                        <textarea id="meta_description_fr" name="meta_description_fr" rows="2" class="textarea" maxlength="160"><?php echo e($trFr['meta_description'] ?? ''); ?></textarea>
                        <small style="display: block; margin-top: 0.25rem; color: #8B7F7F;">Recommended: 150-160 characters</small>
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
                    <div style="margin-bottom: 1rem;">
                        <label for="meta_title_en">Meta Title (EN) - SEO</label>
                        <input id="meta_title_en" name="meta_title_en" type="text" class="input" maxlength="70" value="<?php echo e($trEn['meta_title'] ?? ''); ?>">
                        <small style="display: block; margin-top: 0.25rem; color: #8B7F7F;">Recommended: 50-60 characters</small>
                    </div>
                    <div style="margin-bottom: 1rem;">
                        <label for="meta_description_en">Meta Description (EN) - SEO</label>
                        <textarea id="meta_description_en" name="meta_description_en" rows="2" class="textarea" maxlength="160"><?php echo e($trEn['meta_description'] ?? ''); ?></textarea>
                        <small style="display: block; margin-top: 0.25rem; color: #8B7F7F;">Recommended: 150-160 characters</small>
                    </div>

                    <button type="submit" class="btn-primary" style="margin-top: 1rem;">
                        Save changes
                    </button>
                </form>

                <hr style="margin: 2rem 0;">

                <h2>Images</h2>
                
                <?php if (!empty($productImages)): ?>
                    <form method="post" style="margin-top: 1rem;">
                        <input type="hidden" name="csrf_token" value="<?php echo e(generateCSRFToken()); ?>">
                        <input type="hidden" name="action" value="manage_images">
                        
                        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 1rem; margin-bottom: 1.5rem;">
                            <?php foreach ($productImages as $img): ?>
                                <div style="border: 2px solid <?php echo $img['is_primary'] ? 'var(--dusty-rose)' : '#E8E4DD'; ?>; border-radius: 0.5rem; padding: 0.75rem; background: #fff;">
                                    <img src="/uploads/products/thumbnail/<?php echo e($img['filename']); ?>" alt="" style="width: 100%; height: 150px; object-fit: cover; border-radius: 0.25rem; margin-bottom: 0.5rem;">
                                    <div style="margin-bottom: 0.5rem;">
                                        <label style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.9rem;">
                                            <input type="radio" name="primary_image" value="<?php echo (int) $img['id']; ?>" <?php echo $img['is_primary'] ? 'checked' : ''; ?>>
                                            <span>Primary</span>
                                        </label>
                                    </div>
                                    <div style="margin-bottom: 0.5rem;">
                                        <label style="display: block; font-size: 0.85rem; margin-bottom: 0.25rem;">Order:</label>
                                        <input type="number" name="image_orders[<?php echo (int) $img['id']; ?>]" value="<?php echo (int) $img['display_order']; ?>" min="0" style="width: 100%; padding: 0.4rem; border: 1px solid #E8E4DD; border-radius: 0.25rem;">
                                    </div>
                                    <button type="submit" name="delete_image_id" value="<?php echo (int) $img['id']; ?>" onclick="return confirm('Are you sure you want to delete this image?');" style="width: 100%; padding: 0.5rem; background: #FFEBEE; color: #C62828; border: 1px solid #FFCDD2; border-radius: 0.25rem; cursor: pointer; font-size: 0.85rem;">
                                        Delete
                                    </button>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        
                        <button type="submit" class="btn-primary">Save image order & primary</button>
                    </form>
                    
                    <hr style="margin: 2rem 0;">
                <?php endif; ?>
                
                <h3>Upload New Images</h3>
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


