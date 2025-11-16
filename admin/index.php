<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

requireAdmin();

global $pdo;

// Basic stats
$counts = [
    'total_products' => 0,
    'available' => 0,
    'sold' => 0,
    'reserved' => 0,
    'inquiries' => 0,
];

$stmt = $pdo->query("SELECT COUNT(*) AS c FROM products");
$counts['total_products'] = (int) $stmt->fetchColumn();

$statusStmt = $pdo->query("SELECT status, COUNT(*) AS c FROM products GROUP BY status");
foreach ($statusStmt as $row) {
    $counts[$row['status']] = (int) $row['c'];
}

$inqStmt = $pdo->query("SELECT COUNT(*) AS c FROM whatsapp_inquiries");
$counts['inquiries'] = (int) $inqStmt->fetchColumn();

// Recent inquiries
$recentInquiries = $pdo->query("
    SELECT wi.*, pt.title 
    FROM whatsapp_inquiries wi
    LEFT JOIN products p ON wi.product_id = p.id
    LEFT JOIN product_translations pt 
      ON p.id = pt.product_id AND pt.language_code = 'fr'
    ORDER BY wi.inquiry_date DESC
    LIMIT 10
")->fetchAll();

// Top viewed products
$topProducts = $pdo->query("
    SELECT p.id, p.view_count, pt.title
    FROM products p
    JOIN product_translations pt 
      ON p.id = pt.product_id AND pt.language_code = 'fr'
    ORDER BY p.view_count DESC
    LIMIT 5
")->fetchAll();

// Recent activity logs
$recentActivities = $pdo->query("
    SELECT al.*, au.username
    FROM activity_logs al
    LEFT JOIN admin_users au ON al.admin_id = au.id
    ORDER BY al.created_at DESC
    LIMIT 10
")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Admin Dashboard · Creations JY</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="/assets/css/admin.css">
</head>
<body>
    <header class="site-header">
        <div class="site-header-inner container">
            <strong>Creations JY · Admin</strong>
            <nav class="site-nav">
                <a href="/admin/index.php">Dashboard</a>
                <a href="/admin/products/index.php">Products</a>
                <a href="/admin/categories/index.php">Categories</a>
                <a href="/admin/settings/general.php">Settings</a>
                <a href="/admin/logout.php">Logout</a>
            </nav>
        </div>
    </header>
    <main>
        <section class="section">
            <div class="container">
                <h1 class="section-title">Dashboard</h1>
                <div class="product-grid" style="margin-top: 1.5rem;">
                    <div class="product-card">
                        <div class="container" style="padding: 1.25rem;">
                            <h2>Total products</h2>
                            <p style="font-size: 2rem;"><?php echo $counts['total_products']; ?></p>
                        </div>
                    </div>
                    <div class="product-card">
                        <div class="container" style="padding: 1.25rem;">
                            <h2>Available</h2>
                            <p style="font-size: 2rem;"><?php echo $counts['available']; ?></p>
                        </div>
                    </div>
                    <div class="product-card">
                        <div class="container" style="padding: 1.25rem;">
                            <h2>Sold</h2>
                            <p style="font-size: 2rem;"><?php echo $counts['sold']; ?></p>
                        </div>
                    </div>
                    <div class="product-card">
                        <div class="container" style="padding: 1.25rem;">
                            <h2>Reserved</h2>
                            <p style="font-size: 2rem;"><?php echo $counts['reserved']; ?></p>
                        </div>
                    </div>
                    <div class="product-card">
                        <div class="container" style="padding: 1.25rem;">
                            <h2>WhatsApp inquiries</h2>
                            <p style="font-size: 2rem;"><?php echo $counts['inquiries']; ?></p>
                        </div>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: minmax(0, 1fr); gap: 2rem; margin-top: 2rem;">
                    <div>
                        <h2>Recent WhatsApp inquiries</h2>
                        <?php if ($recentInquiries): ?>
                            <ul>
                                <?php foreach ($recentInquiries as $inq): ?>
                                    <li>
                                        <?php echo e($inq['inquiry_date']); ?> ·
                                        <?php echo e($inq['title'] ?: 'Unknown product'); ?>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php else: ?>
                            <p>No inquiries yet.</p>
                        <?php endif; ?>
                    </div>
                    <div>
                        <h2>Most viewed products</h2>
                        <?php if ($topProducts): ?>
                            <ul>
                                <?php foreach ($topProducts as $p): ?>
                                    <li>
                                        <?php echo e($p['title']); ?> · <?php echo (int) $p['view_count']; ?> views
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php else: ?>
                            <p>No products yet.</p>
                        <?php endif; ?>
                    </div>
                    <div>
                        <h2>Recent Activity</h2>
                        <?php if ($recentActivities): ?>
                            <ul style="list-style: none; padding: 0;">
                                <?php foreach ($recentActivities as $activity): ?>
                                    <li style="padding: 0.5rem 0; border-bottom: 1px solid #E8E4DD;">
                                        <strong><?php echo e($activity['action']); ?></strong>
                                        <?php if ($activity['username']): ?>
                                            <span style="color: #8B7F7F;">by <?php echo e($activity['username']); ?></span>
                                        <?php endif; ?>
                                        <br>
                                        <small style="color: #8B7F7F;">
                                            <?php echo e($activity['created_at']); ?>
                                            <?php if ($activity['details']): ?>
                                                · <?php echo e(mb_substr($activity['details'], 0, 60, 'UTF-8')); ?>
                                            <?php endif; ?>
                                        </small>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php else: ?>
                            <p>No activity yet.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </section>
    </main>
</body>
</html>


