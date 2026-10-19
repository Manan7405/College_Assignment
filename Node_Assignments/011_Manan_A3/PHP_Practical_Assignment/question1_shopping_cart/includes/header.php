<?php
/** Top part of every user page (navbar). Include AFTER functions.php. */
$pageTitle = $pageTitle ?? 'Online Shop';
$current   = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($pageTitle) ?> - Online Shop</title>
    <link rel="stylesheet" href="assets/bootstrap/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-primary mb-4">
    <div class="container">
        <a class="navbar-brand" href="index.php">Online Shop</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="mainNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item"><a class="nav-link <?= $current === 'index.php' ? 'active' : '' ?>" href="index.php">Home</a></li>
                <li class="nav-item">
                    <a class="nav-link <?= $current === 'cart.php' ? 'active' : '' ?>" href="cart.php">
                        Cart <span class="badge bg-light text-dark" id="cart-count"><?= cart_count() ?></span>
                    </a>
                </li>
            </ul>
            <ul class="navbar-nav">
                <?php if (is_user_logged_in()): ?>
                    <li class="nav-item"><span class="navbar-text me-3">Hello, <?= e($_SESSION['user_name'] ?? '') ?></span></li>
                    <li class="nav-item"><a class="nav-link" href="logout.php">Logout</a></li>
                <?php else: ?>
                    <li class="nav-item"><a class="nav-link <?= $current === 'login.php' ? 'active' : '' ?>" href="login.php">Login</a></li>
                    <li class="nav-item"><a class="nav-link <?= $current === 'register.php' ? 'active' : '' ?>" href="register.php">Register</a></li>
                <?php endif; ?>
                <li class="nav-item"><a class="nav-link" href="admin/login.php">Admin</a></li>
            </ul>
        </div>
    </div>
</nav>
<div class="container mb-5">
<?php show_flash(); ?>
