<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';

requireAdmin();

global $pdo;

$errors = [];
$success = false;

// Fetch categories for select
$categories = $pdo->query("
    SELECT c.id, ct.name
    FROM categories c
    LEFT JOIN category_translations ct 
      ON c.id = ct.category_id AND ct.language_code = 'fr'
    ORDER BY ct.name ASC
")->fetchAll();

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
            $stmt = $pdo->prepare("
                INSERT INTO products (category_id, slug, sku, status, featured)
                VALUES (:category_id, :slug, :sku, :status, :featured)
            ");
            $stmt->execute([
                'category_id' => $categoryId,
                'slug' => $slug,
                'sku' => $sku !== '' ? $sku : null,
                'status' => $status,
                'featured' => $featured,
            ]);
            $productId = (int) $pdo->lastInsertId();

            $tStmt = $pdo->prepare("
                INSERT INTO product_translations 
                    (product_id, language_code, title, description, materials, dimensions, meta_title, meta_description)
                VALUES 
                    (:product_id, :lang, :title, :description, :materials, :dimensions, :meta_title, :meta_description)
            ");

            $tStmt->execute([
                'product_id' => $productId,
                'lang' => 'fr',
                'title' => $titleFr,
                'description' => $descFr,
                'materials' => $materialsFr,
                'dimensions' => $dimensionsFr,
                'meta_title' => $metaTitleFr !== '' ? $metaTitleFr : null,
                'meta_description' => $metaDescFr !== '' ? $metaDescFr : null,
            ]);

            $tStmt->execute([
                'product_id' => $productId,
                'lang' => 'en',
                'title' => $titleEn,
                'description' => $descEn,
                'materials' => $materialsEn,
                'dimensions' => $dimensionsEn,
                'meta_title' => $metaTitleEn !== '' ? $metaTitleEn : null,
                'meta_description' => $metaDescEn !== '' ? $metaDescEn : null,
            ]);

            // Handle image uploads if any
            if (isset($_FILES['images']) && !empty($_FILES['images']['name'][0])) {
                require_once __DIR__ . '/upload-image.php';
                handleImageUpload($productId, $_FILES['images']);
            }

            $pdo->commit();
            $success = true;
            
            // Log activity
            logActivity('product_created', 'products', $productId, 'Product created: ' . $titleEn);
            
            // Redirect to edit page to allow adding more images
            header('Location: /admin/products/edit.php?id=' . $productId);
            exit;
        } catch (Throwable $e) {
            $pdo->rollBack();
            $errors[] = 'Error saving product: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Create product · Admin · Creations JY</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="/assets/css/admin.css">
</head>
<body>
    <header class="site-header">
        <div class="site-header-inner container">
            <strong>Create product</strong>
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
                <h1 class="section-title">New product</h1>
                <?php if ($success): ?>
                    <p style="margin-top: 1rem; padding: 0.75rem 1rem; background-color: #E8F5E9; border-radius: 0.5rem;">
                        Product created successfully.
                    </p>
                <?php endif; ?>
                <?php if ($errors): ?>
                    <ul style="margin-top: 1rem; padding: 0.75rem 1rem; background-color: #FFEBEE; border-radius: 0.5rem;">
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo e($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>

                <form method="post" enctype="multipart/form-data">
                    <input type="hidden" name="csrf_token" value="<?php echo e(generateCSRFToken()); ?>">

                    <h2>Basics</h2>
                    <div style="margin-bottom: 1rem;">
                        <label for="category_id">Category</label>
                        <select id="category_id" name="category_id" class="select" required>
                            <option value="">Choose…</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?php echo (int) $cat['id']; ?>"><?php echo e($cat['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div style="margin-bottom: 1rem;">
                        <label for="slug">Slug (optional)</label>
                        <input id="slug" name="slug" type="text" class="input">
                    </div>
                    <div style="margin-bottom: 1rem;">
                        <label for="sku">SKU (optional)</label>
                        <input id="sku" name="sku" type="text" class="input">
                    </div>
                    <div style="margin-bottom: 1rem;">
                        <label for="status">Status</label>
                        <select id="status" name="status" class="select">
                            <option value="available">Available</option>
                            <option value="sold">Sold</option>
                            <option value="reserved">Reserved</option>
                        </select>
                    </div>
                    <div style="margin-bottom: 1rem;">
                        <label>
                            <input type="checkbox" name="featured" value="1">
                            Featured on homepage
                        </label>
                    </div>

                    <h2>Content (FR)</h2>
                    <div style="margin-bottom: 1rem;">
                        <label for="title_fr">Title (FR)</label>
                        <input id="title_fr" name="title_fr" type="text" class="input" required>
                    </div>
                    <div style="margin-bottom: 1rem;">
                        <label for="description_fr">Description (FR)</label>
                        <textarea id="description_fr" name="description_fr" rows="4" class="textarea"></textarea>
                    </div>
                    <div style="margin-bottom: 1rem;">
                        <label for="materials_fr">Materials (FR)</label>
                        <textarea id="materials_fr" name="materials_fr" rows="3" class="textarea"></textarea>
                    </div>
                    <div style="margin-bottom: 1rem;">
                        <label for="dimensions_fr">Dimensions (FR)</label>
                        <input id="dimensions_fr" name="dimensions_fr" type="text" class="input">
                    </div>
                    <div style="margin-bottom: 1rem;">
                        <label for="meta_title_fr">Meta Title (FR) - SEO</label>
                        <input id="meta_title_fr" name="meta_title_fr" type="text" class="input" maxlength="70">
                        <small style="display: block; margin-top: 0.25rem; color: #8B7F7F;">Recommended: 50-60 characters</small>
                    </div>
                    <div style="margin-bottom: 1rem;">
                        <label for="meta_description_fr">Meta Description (FR) - SEO</label>
                        <textarea id="meta_description_fr" name="meta_description_fr" rows="2" class="textarea" maxlength="160"></textarea>
                        <small style="display: block; margin-top: 0.25rem; color: #8B7F7F;">Recommended: 150-160 characters</small>
                    </div>

                    <h2>Content (EN)</h2>
                    <div style="margin-bottom: 1rem;">
                        <label for="title_en">Title (EN)</label>
                        <input id="title_en" name="title_en" type="text" class="input" required>
                    </div>
                    <div style="margin-bottom: 1rem;">
                        <label for="description_en">Description (EN)</label>
                        <textarea id="description_en" name="description_en" rows="4" class="textarea"></textarea>
                    </div>
                    <div style="margin-bottom: 1rem;">
                        <label for="materials_en">Materials (EN)</label>
                        <textarea id="materials_en" name="materials_en" rows="3" class="textarea"></textarea>
                    </div>
                    <div style="margin-bottom: 1rem;">
                        <label for="dimensions_en">Dimensions (EN)</label>
                        <input id="dimensions_en" name="dimensions_en" type="text" class="input">
                    </div>
                    <div style="margin-bottom: 1rem;">
                        <label for="meta_title_en">Meta Title (EN) - SEO</label>
                        <input id="meta_title_en" name="meta_title_en" type="text" class="input" maxlength="70">
                        <small style="display: block; margin-top: 0.25rem; color: #8B7F7F;">Recommended: 50-60 characters</small>
                    </div>
                    <div style="margin-bottom: 1rem;">
                        <label for="meta_description_en">Meta Description (EN) - SEO</label>
                        <textarea id="meta_description_en" name="meta_description_en" rows="2" class="textarea" maxlength="160"></textarea>
                        <small style="display: block; margin-top: 0.25rem; color: #8B7F7F;">Recommended: 150-160 characters</small>
                    </div>

                    <h2>Images</h2>
                    <div style="margin-bottom: 1rem;">
                        <label for="images">Product Images</label>
                        <input id="images" name="images[]" type="file" class="input" accept="image/jpeg,image/png,image/webp" multiple>
                        <small style="display: block; margin-top: 0.25rem; color: #8B7F7F;">
                            You can select multiple images. Supported formats: JPEG, PNG, WebP (max 5MB each)
                        </small>
                        <div id="image-preview" style="margin-top: 1rem; display: grid; grid-template-columns: repeat(auto-fill, minmax(120px, 1fr)); gap: 0.5rem;"></div>
                    </div>

                    <button type="submit" class="btn-primary" style="margin-top: 1rem;">
                        Save product
                    </button>
                </form>
            </div>
        </section>
    </main>
    <script>
        // Image preview functionality
        document.getElementById('images').addEventListener('change', function(e) {
            const preview = document.getElementById('image-preview');
            preview.innerHTML = '';
            
            const files = Array.from(e.target.files);
            files.forEach(function(file) {
                if (!file.type.startsWith('image/')) {
                    return;
                }
                
                const reader = new FileReader();
                reader.onload = function(e) {
                    const img = document.createElement('img');
                    img.src = e.target.result;
                    img.style.width = '100%';
                    img.style.height = '120px';
                    img.style.objectFit = 'cover';
                    img.style.borderRadius = '0.5rem';
                    img.style.border = '1px solid #E8E4DD';
                    preview.appendChild(img);
                };
                reader.readAsDataURL(file);
            });
        });
    </script>
</body>
</html>


