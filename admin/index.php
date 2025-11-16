<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

requireAdmin();

global $pdo;

$brandTagline = getSiteSetting('brand_tagline_en', 'Upcycling studio in Gruyère · Unique and sustainable decor.');
$siteName = getSiteSetting('site_name_en', 'Creations JY');
$whatsAppNumber = getWhatsAppNumber();
$whatsAppLinkBase = 'https://wa.me/' . preg_replace('/\D+/', '', $whatsAppNumber);

// Basic stats
$counts = [
    'total_products' => 0,
    'available' => 0,
    'sold' => 0,
    'reserved' => 0,
    'inquiries' => 0,
    'wp_today' => 0,
    'wp_week' => 0,
];

$stmt = $pdo->query("SELECT COUNT(*) AS c FROM products");
$counts['total_products'] = (int) $stmt->fetchColumn();

$statusStmt = $pdo->query("SELECT status, COUNT(*) AS c FROM products GROUP BY status");
foreach ($statusStmt as $row) {
    $counts[$row['status']] = (int) $row['c'];
}

$counts['inquiries'] = (int) $pdo->query("SELECT COUNT(*) FROM whatsapp_inquiries")->fetchColumn();
$counts['wp_today'] = (int) $pdo->query("SELECT COUNT(*) FROM whatsapp_inquiries WHERE DATE(inquiry_date) = CURDATE()")->fetchColumn();
$counts['wp_week'] = (int) $pdo->query("SELECT COUNT(*) FROM whatsapp_inquiries WHERE inquiry_date >= DATE_SUB(NOW(), INTERVAL 7 DAY)")->fetchColumn();

// Recent inquiries
$recentInquiries = $pdo->query("
    SELECT wi.*, pt.title 
    FROM whatsapp_inquiries wi
    LEFT JOIN products p ON wi.product_id = p.id
    LEFT JOIN product_translations pt 
      ON p.id = pt.product_id AND pt.language_code = 'fr'
    ORDER BY wi.inquiry_date DESC
    LIMIT 8
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
    LIMIT 8
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
            <strong><?php echo e($siteName); ?> · Ops</strong>
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
        <section class="section dashboard-section">
            <div class="container">
                <div class="dashboard-hero">
                    <p class="eyebrow">Creations JY studio</p>
                    <h1>Welcome back, <?php echo e(currentAdmin()['full_name'] ?? 'admin'); ?>.</h1>
                    <p><?php echo e($brandTagline); ?></p>
                    <div class="hero-actions">
                        <a class="btn-primary" href="/admin/products/create.php">+ Add new product</a>
                        <a class="btn-secondary" href="/" target="_blank" rel="noopener">View storefront</a>
                    </div>
                </div>

                <div class="stats-grid">
                    <article class="stat-card">
                        <p class="stat-label">Total products</p>
                        <p class="stat-value"><?php echo (int) $counts['total_products']; ?></p>
                    </article>
                    <article class="stat-card" data-status="available">
                        <p class="stat-label">Available</p>
                        <p class="stat-value"><?php echo (int) $counts['available']; ?></p>
                    </article>
                    <article class="stat-card" data-status="reserved">
                        <p class="stat-label">Reserved</p>
                        <p class="stat-value"><?php echo (int) $counts['reserved']; ?></p>
                    </article>
                    <article class="stat-card" data-status="sold">
                        <p class="stat-label">Sold</p>
                        <p class="stat-value"><?php echo (int) $counts['sold']; ?></p>
                    </article>
                    <article class="stat-card" data-status="whatsapp">
                        <p class="stat-label">WP requests (7d)</p>
                        <p class="stat-value"><?php echo (int) $counts['wp_week']; ?></p>
                        <small><?php echo (int) $counts['wp_today']; ?> today · <?php echo (int) $counts['inquiries']; ?> total</small>
                    </article>
                </div>

                <div class="dashboard-grid">
                    <section class="insight-card">
                        <div class="insight-card-header">
                            <div>
                                <h2>WhatsApp pipeline</h2>
                                <p>Latest leads coming through WP.</p>
                            </div>
                            <span class="badge-light"><?php echo (int) $counts['wp_today']; ?> today</span>
                        </div>
                        <?php if ($recentInquiries): ?>
                            <div class="wp-feed">
                                <?php foreach ($recentInquiries as $inq): ?>
                                    <article class="wp-card">
                                        <div class="wp-card-header">
                                            <strong><?php echo e($inq['customer_name'] ?: 'WhatsApp lead'); ?></strong>
                                            <span><?php echo e(date('d M H:i', strtotime($inq['inquiry_date']))); ?></span>
                                        </div>
                                        <p class="wp-product"><?php echo e($inq['title'] ?: 'Unknown product'); ?></p>
                                        <?php if (!empty($inq['message'])): ?>
                                            <p class="wp-message">
                                                “<?php echo e(mb_strimwidth($inq['message'], 0, 140, '…', 'UTF-8')); ?>”
                                            </p>
                                        <?php endif; ?>
                                        <div class="wp-card-footer">
                                            <a class="btn-outline" href="<?php echo e($whatsAppLinkBase); ?>" target="_blank" rel="noopener">Open chat</a>
                                            <?php if (!empty($inq['product_id'])): ?>
                                                <a class="ghost-link" href="/admin/products/edit.php?id=<?php echo (int) $inq['product_id']; ?>">Product sheet</a>
                                            <?php endif; ?>
                                        </div>
                                    </article>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <p class="empty-state">No WhatsApp requests recorded yet.</p>
                        <?php endif; ?>
                    </section>

                    <section class="insight-card">
                        <div class="insight-card-header">
                            <h2>Most viewed products</h2>
                            <p>Based on view counter from the storefront.</p>
                        </div>
                        <?php if ($topProducts): ?>
                            <ul class="simple-list">
                                <?php foreach ($topProducts as $p): ?>
                                    <li>
                                        <div>
                                            <strong><?php echo e($p['title']); ?></strong>
                                            <span><?php echo (int) $p['view_count']; ?> views</span>
                                        </div>
                                        <a href="/admin/products/edit.php?id=<?php echo (int) $p['id']; ?>" class="ghost-link">Edit</a>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php else: ?>
                            <p class="empty-state">No product activity yet.</p>
                        <?php endif; ?>
                    </section>

                    <section class="insight-card">
                        <div class="insight-card-header">
                            <h2>Recent activity</h2>
                            <p>Last updates performed by the team.</p>
                        </div>
                        <?php if ($recentActivities): ?>
                            <ul class="activity-list">
                                <?php foreach ($recentActivities as $activity): ?>
                                    <li>
                                        <div>
                                            <strong><?php echo e($activity['action']); ?></strong>
                                            <span><?php echo e($activity['username'] ?? 'System'); ?></span>
                                        </div>
                                        <p>
                                            <?php echo e(date('d M H:i', strtotime($activity['created_at']))); ?>
                                            <?php if ($activity['details']): ?>
                                                · <?php echo e(mb_strimwidth($activity['details'], 0, 80, '…', 'UTF-8')); ?>
                                            <?php endif; ?>
                                        </p>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php else: ?>
                            <p class="empty-state">No admin actions logged yet.</p>
                        <?php endif; ?>
                    </section>
                </div>
            </div>
        </section>
    </main>
</body>
</html>

