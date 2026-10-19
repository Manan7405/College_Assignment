<?php
/**
 * Common top part of every admin page.
 * Every admin page calls require_admin() BEFORE including this file.
 */
$pageTitle = $pageTitle ?? 'Admin Panel';
$current   = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($pageTitle) ?> - Admin</title>
    <link rel="stylesheet" href="../assets/bootstrap/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
    <div class="container">
        <a class="navbar-brand" href="dashboard.php">Shop Admin</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#adminNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="adminNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item"><a class="nav-link <?= $current === 'dashboard.php' ? 'active' : '' ?>" href="dashboard.php">Dashboard</a></li>
                <li class="nav-item"><a class="nav-link <?= in_array($current, ['categories.php', 'category_edit.php']) ? 'active' : '' ?>" href="categories.php">Categories</a></li>
                <li class="nav-item"><a class="nav-link <?= in_array($current, ['products.php', 'product_form.php']) ? 'active' : '' ?>" href="products.php">Products</a></li>
                <li class="nav-item"><a class="nav-link" href="../index.php" target="_blank">View Shop</a></li>
            </ul>
            <span class="navbar-text me-3">Logged in as <strong><?= e($_SESSION['admin_username'] ?? 'admin') ?></strong></span>
            <a class="btn btn-outline-light btn-sm" href="logout.php">Logout</a>
        </div>
    </div>
</nav>
<div class="container mb-5">
<?php show_flash(); ?>
