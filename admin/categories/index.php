<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';

requireAdmin();

global $pdo;

$stmt = $pdo->query("
    SELECT c.id, c.slug,
           ct_fr.name AS name_fr,
           ct_en.name AS name_en
    FROM categories c
    LEFT JOIN category_translations ct_fr 
      ON c.id = ct_fr.category_id AND ct_fr.language_code = 'fr'
    LEFT JOIN category_translations ct_en 
      ON c.id = ct_en.category_id AND ct_en.language_code = 'en'
    ORDER BY ct_fr.name ASC
");
$categories = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Categories · Admin · Creations JY</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="/assets/css/main.css">
</head>
<body>
    <header class="site-header">
        <div class="site-header-inner container">
            <strong>Categories</strong>
            <nav class="site-nav">
                <a href="/admin/index.php">Dashboard</a>
                <a href="/admin/products/index.php">Products</a>
                <a href="/admin/categories/create.php">Add category</a>
                <a href="/admin/logout.php">Logout</a>
            </nav>
        </div>
    </header>
    <main>
        <section class="section">
            <div class="container">
                <h1 class="section-title">Categories</h1>
                <p><a class="btn-primary" href="/admin/categories/create.php">Add new category</a></p>
                <?php if ($categories): ?>
                    <table style="width: 100%; border-collapse: collapse; margin-top: 1rem; font-size: 0.95rem;">
                        <thead>
                        <tr>
                            <th style="text-align:left;padding:0.5rem;border-bottom:1px solid #E8E4DD;">ID</th>
                            <th style="text-align:left;padding:0.5rem;border-bottom:1px solid #E8E4DD;">Slug</th>
                            <th style="text-align:left;padding:0.5rem;border-bottom:1px solid #E8E4DD;">Name (FR)</th>
                            <th style="text-align:left;padding:0.5rem;border-bottom:1px solid #E8E4DD;">Name (EN)</th>
                            <th style="text-align:right;padding:0.5rem;border-bottom:1px solid #E8E4DD;">Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($categories as $cat): ?>
                            <tr>
                                <td style="padding:0.5rem;"><?php echo (int) $cat['id']; ?></td>
                                <td style="padding:0.5rem;"><?php echo e($cat['slug']); ?></td>
                                <td style="padding:0.5rem;"><?php echo e($cat['name_fr'] ?? ''); ?></td>
                                <td style="padding:0.5rem;"><?php echo e($cat['name_en'] ?? ''); ?></td>
                                <td style="padding:0.5rem;text-align:right;">
                                    <a href="/admin/categories/edit.php?id=<?php echo (int) $cat['id']; ?>">Edit</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p>No categories yet.</p>
                <?php endif; ?>
            </div>
        </section>
    </main>
</body>
</html>


