<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';

requireAdmin();

global $pdo;

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
if ($id <= 0) {
    http_response_code(404);
    exit('Category not found.');
}

$stmt = $pdo->prepare('SELECT * FROM categories WHERE id = :id LIMIT 1');
$stmt->execute(['id' => $id]);
$category = $stmt->fetch();

if (!$category) {
    http_response_code(404);
    exit('Category not found.');
}

$tStmt = $pdo->prepare('SELECT * FROM category_translations WHERE category_id = :id');
$tStmt->execute(['id' => $id]);
$translations = [];
foreach ($tStmt as $row) {
    $translations[$row['language_code']] = $row;
}

$trFr = $translations['fr'] ?? ['name' => '', 'description' => ''];
$trEn = $translations['en'] ?? ['name' => '', 'description' => ''];

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf_token'] ?? null;
    if (!verifyCSRFToken($token)) {
        $errors[] = 'Security check failed, please try again.';
    }

    $slug = trim($_POST['slug'] ?? '');
    $nameFr = trim($_POST['name_fr'] ?? '');
    $nameEn = trim($_POST['name_en'] ?? '');
    $descFr = trim($_POST['description_fr'] ?? '');
    $descEn = trim($_POST['description_en'] ?? '');

    if ($nameFr === '' || $nameEn === '') {
        $errors[] = 'Names in both FR and EN are required.';
    }
    if ($slug === '') {
        $slug = strtolower(preg_replace('/[^a-z0-9]+/i', '-', $nameEn));
    }

    if (!$errors) {
        $pdo->beginTransaction();
        try {
            $uStmt = $pdo->prepare('UPDATE categories SET slug = :slug WHERE id = :id');
            $uStmt->execute([
                'slug' => $slug,
                'id' => $id,
            ]);

            $upStmt = $pdo->prepare('
                UPDATE category_translations
                   SET name = :name,
                       description = :description
                 WHERE category_id = :category_id
                   AND language_code = :lang
            ');

            $upStmt->execute([
                'category_id' => $id,
                'lang' => 'fr',
                'name' => $nameFr,
                'description' => $descFr,
            ]);

            $upStmt->execute([
                'category_id' => $id,
                'lang' => 'en',
                'name' => $nameEn,
                'description' => $descEn,
            ]);

            $pdo->commit();
            $success = true;
        } catch (Throwable $e) {
            $pdo->rollBack();
            $errors[] = 'Error updating category: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Edit category · Admin · Creations JY</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="/assets/css/main.css">
</head>
<body>
    <header class="site-header">
        <div class="site-header-inner container">
            <strong>Edit category</strong>
            <nav class="site-nav">
                <a href="/admin/index.php">Dashboard</a>
                <a href="/admin/categories/index.php">Categories</a>
                <a href="/admin/logout.php">Logout</a>
            </nav>
        </div>
    </header>
    <main>
        <section class="section">
            <div class="container">
                <h1 class="section-title">Edit category #<?php echo (int) $category['id']; ?></h1>
                <?php if ($success): ?>
                    <p style="margin-top:1rem;padding:0.75rem 1rem;background-color:#E8F5E9;border-radius:0.5rem;">
                        Category updated successfully.
                    </p>
                <?php endif; ?>
                <?php if ($errors): ?>
                    <ul style="margin-top:1rem;padding:0.75rem 1rem;background-color:#FFEBEE;border-radius:0.5rem;">
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo e($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>

                <form method="post">
                    <input type="hidden" name="csrf_token" value="<?php echo e(generateCSRFToken()); ?>">
                    <div style="margin-bottom:1rem;">
                        <label for="slug">Slug</label>
                        <input id="slug" name="slug" type="text" class="input" value="<?php echo e($category['slug']); ?>">
                    </div>

                    <h2>FR</h2>
                    <div style="margin-bottom:1rem;">
                        <label for="name_fr">Name (FR)</label>
                        <input id="name_fr" name="name_fr" type="text" class="input" value="<?php echo e($trFr['name']); ?>">
                    </div>
                    <div style="margin-bottom:1rem;">
                        <label for="description_fr">Description (FR)</label>
                        <textarea id="description_fr" name="description_fr" rows="3" class="textarea"><?php echo e($trFr['description']); ?></textarea>
                    </div>

                    <h2>EN</h2>
                    <div style="margin-bottom:1rem;">
                        <label for="name_en">Name (EN)</label>
                        <input id="name_en" name="name_en" type="text" class="input" value="<?php echo e($trEn['name']); ?>">
                    </div>
                    <div style="margin-bottom:1rem;">
                        <label for="description_en">Description (EN)</label>
                        <textarea id="description_en" name="description_en" rows="3" class="textarea"><?php echo e($trEn['description']); ?></textarea>
                    </div>

                    <button type="submit" class="btn-primary" style="margin-top:1rem;">
                        Save changes
                    </button>
                </form>
            </div>
        </section>
    </main>
</body>
</html>


