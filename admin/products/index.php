<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';

requireAdmin();

global $pdo;

$stmt = $pdo->query("
    SELECT p.id, p.sku, p.status, p.featured, p.created_at,
           pt_fr.title AS title_fr
    FROM products p
    LEFT JOIN product_translations pt_fr 
      ON p.id = pt_fr.product_id AND pt_fr.language_code = 'fr'
    ORDER BY p.created_at DESC
");
$products = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Products · Admin · Creations JY</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="/assets/css/admin.css">
</head>
<body>
    <header class="site-header">
        <div class="site-header-inner container">
            <strong>Products</strong>
            <nav class="site-nav">
                <a href="/admin/index.php">Dashboard</a>
                <a href="/admin/products/create.php">Add product</a>
                <a href="/admin/categories/index.php">Categories</a>
                <a href="/admin/settings/general.php">Settings</a>
                <a href="/admin/logout.php">Logout</a>
            </nav>
        </div>
    </header>
    <main>
        <section class="section">
            <div class="container">
                <h1 class="section-title">Products</h1>
                <p><a class="btn-primary" href="/admin/products/create.php">Add new product</a></p>
                <?php if ($products): ?>
                    <table style="width: 100%; border-collapse: collapse; margin-top: 1rem; font-size: 0.95rem;">
                        <thead>
                            <tr>
                                <th style="text-align: left; padding: 0.5rem; border-bottom: 1px solid #E8E4DD;">ID</th>
                                <th style="text-align: left; padding: 0.5rem; border-bottom: 1px solid #E8E4DD;">Title (FR)</th>
                                <th style="text-align: left; padding: 0.5rem; border-bottom: 1px solid #E8E4DD;">SKU</th>
                                <th style="text-align: left; padding: 0.5rem; border-bottom: 1px solid #E8E4DD;">Status</th>
                                <th style="text-align: left; padding: 0.5rem; border-bottom: 1px solid #E8E4DD;">Featured</th>
                                <th style="text-align: left; padding: 0.5rem; border-bottom: 1px solid #E8E4DD;">Created</th>
                                <th style="text-align: right; padding: 0.5rem; border-bottom: 1px solid #E8E4DD;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($products as $product): ?>
                            <tr>
                                <td style="padding: 0.5rem;"><?php echo (int) $product['id']; ?></td>
                                <td style="padding: 0.5rem;"><?php echo e($product['title_fr'] ?? '(no title)'); ?></td>
                                <td style="padding: 0.5rem;"><?php echo e($product['sku']); ?></td>
                                <td style="padding: 0.5rem;"><?php echo e($product['status']); ?></td>
                                <td style="padding: 0.5rem;"><?php echo $product['featured'] ? 'Yes' : 'No'; ?></td>
                                <td style="padding: 0.5rem;"><?php echo e($product['created_at']); ?></td>
                                <td style="padding: 0.5rem; text-align: right;">
                                    <a href="/admin/products/edit.php?id=<?php echo (int) $product['id']; ?>">Edit</a>
                                    <form method="post" action="/admin/products/delete.php" style="display:inline;" onsubmit="return confirm('Delete this product?');">
                                        <input type="hidden" name="csrf_token" value="<?php echo e(generateCSRFToken()); ?>">
                                        <input type="hidden" name="id" value="<?php echo (int) $product['id']; ?>">
                                        <button type="submit" style="background:none;border:none;color:#8B0000;cursor:pointer;margin-left:0.5rem;">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p>No products yet.</p>
                <?php endif; ?>
            </div>
        </section>
    </main>
</body>
</html>


