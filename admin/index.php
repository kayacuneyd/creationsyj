<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

requireAdmin();

global $pdo;

$siteName = getSiteSetting('site_name_en', 'Creations JY');
$brandTagline = getSiteSetting('brand_tagline_en', 'Upcycling studio in Gruyère · Unique and sustainable decor.');

$totalProducts = (int) $pdo->query('SELECT COUNT(*) FROM products')->fetchColumn();
$totalInquiries = (int) $pdo->query('SELECT COUNT(*) FROM whatsapp_inquiries')->fetchColumn();

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
                        <p class="stat-value"><?php echo $totalProducts; ?></p>
                    </article>
                    <article class="stat-card" data-status="whatsapp">
                        <p class="stat-label">WhatsApp requests</p>
                        <p class="stat-value"><?php echo $totalInquiries; ?></p>
                    </article>
                </div>

                <div class="dashboard-links">
                    <a class="dashboard-link-card" href="/admin/products/index.php">
                        <h3>Manage products</h3>
                        <p>Create, edit and publish the latest pieces.</p>
                    </a>
                    <a class="dashboard-link-card" href="/admin/categories/index.php">
                        <h3>Manage categories</h3>
                        <p>Keep the catalogue organised for clients.</p>
                    </a>
                    <a class="dashboard-link-card" href="/admin/settings/general.php">
                        <h3>Site settings</h3>
                        <p>Update branding, messaging and contact info.</p>
                    </a>
                    <a class="dashboard-link-card" href="/admin/settings/general.php#messaging">
                        <h3>Brand assets</h3>
                        <p>Upload logo and about page media.</p>
                    </a>
                </div>
            </div>
        </section>
    </main>
</body>
</html>
