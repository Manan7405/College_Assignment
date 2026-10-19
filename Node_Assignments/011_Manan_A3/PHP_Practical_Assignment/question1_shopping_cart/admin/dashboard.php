<?php
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/image_helper.php';
require_admin();                                   // protect the page

$pdo = get_pdo();
$totalCategories = (int)$pdo->query('SELECT COUNT(*) FROM categories')->fetchColumn();
$totalProducts   = (int)$pdo->query('SELECT COUNT(*) FROM products')->fetchColumn();
$totalUsers      = (int)$pdo->query('SELECT COUNT(*) FROM users')->fetchColumn();

$pageTitle = 'Dashboard';
require __DIR__ . '/_header.php';
?>
<h3 class="mb-4">Dashboard</h3>
<div class="row g-3">
    <div class="col-md-4">
        <div class="card text-center shadow-sm"><div class="card-body">
            <h2><?= $totalCategories ?></h2><p class="mb-2">Categories</p>
            <a href="categories.php" class="btn btn-sm btn-primary">Manage</a>
        </div></div>
    </div>
    <div class="col-md-4">
        <div class="card text-center shadow-sm"><div class="card-body">
            <h2><?= $totalProducts ?></h2><p class="mb-2">Products</p>
            <a href="products.php" class="btn btn-sm btn-primary">Manage</a>
        </div></div>
    </div>
    <div class="col-md-4">
        <div class="card text-center shadow-sm"><div class="card-body">
            <h2><?= $totalUsers ?></h2><p class="mb-2">Registered users</p>
            <span class="text-muted small">&nbsp;</span>
        </div></div>
    </div>
</div>
<?php if (!gd_available()): ?>
    <div class="alert alert-warning mt-4">
        <strong>GD extension is not enabled.</strong> Product pictures will be uploaded but NOT resized.
        To enable it: open XAMPP &rarr; Apache &rarr; Config &rarr; php.ini, change <code>;extension=gd</code> to <code>extension=gd</code>, then restart Apache.
    </div>
<?php endif; ?>
<?php require __DIR__ . '/_footer.php'; ?>
