<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';

requireAdmin();

global $pdo;

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
            $stmt = $pdo->prepare('INSERT INTO categories (slug) VALUES (:slug)');
            $stmt->execute(['slug' => $slug]);
            $categoryId = (int) $pdo->lastInsertId();

            $tStmt = $pdo->prepare('
                INSERT INTO category_translations (category_id, language_code, name, description)
                VALUES (:category_id, :lang, :name, :description)
            ');

            $tStmt->execute([
                'category_id' => $categoryId,
                'lang' => 'fr',
                'name' => $nameFr,
                'description' => $descFr,
            ]);

            $tStmt->execute([
                'category_id' => $categoryId,
                'lang' => 'en',
                'name' => $nameEn,
                'description' => $descEn,
            ]);

            $pdo->commit();
            $success = true;
        } catch (Throwable $e) {
            $pdo->rollBack();
            $errors[] = 'Error saving category: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Create category · Admin · Creations JY</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="/assets/css/main.css">
</head>
<body>
    <header class="site-header">
        <div class="site-header-inner container">
            <strong>Create category</strong>
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
                <h1 class="section-title">New category</h1>
                <?php if ($success): ?>
                    <p style="margin-top:1rem;padding:0.75rem 1rem;background-color:#E8F5E9;border-radius:0.5rem;">
                        Category created successfully.
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
                        <label for="slug">Slug (optional)</label>
                        <input id="slug" name="slug" type="text" class="input">
                    </div>

                    <h2>FR</h2>
                    <div style="margin-bottom:1rem;">
                        <label for="name_fr">Name (FR)</label>
                        <input id="name_fr" name="name_fr" type="text" class="input" required>
                    </div>
                    <div style="margin-bottom:1rem;">
                        <label for="description_fr">Description (FR)</label>
                        <textarea id="description_fr" name="description_fr" rows="3" class="textarea"></textarea>
                    </div>

                    <h2>EN</h2>
                    <div style="margin-bottom:1rem;">
                        <label for="name_en">Name (EN)</label>
                        <input id="name_en" name="name_en" type="text" class="input" required>
                    </div>
                    <div style="margin-bottom:1rem;">
                        <label for="description_en">Description (EN)</label>
                        <textarea id="description_en" name="description_en" rows="3" class="textarea"></textarea>
                    </div>

                    <button type="submit" class="btn-primary" style="margin-top:1rem;">
                        Save category
                    </button>
                </form>
            </div>
        </section>
    </main>
</body>
</html>


