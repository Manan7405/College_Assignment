<?php
/**
 * User home page.
 *  - Categories are shown as hyperlinks (index.php?category_id=2).
 *  - Products are loaded by AJAX (assets/js/products.js -> ajax/get_products.php).
 *    Without JavaScript the category links still work because the page
 *    remembers the category id in a data attribute.
 */
require_once __DIR__ . '/includes/functions.php';

$categories = get_pdo()->query('SELECT id, name FROM categories ORDER BY name')->fetchAll();

// Which category is selected? 0 = all products
$selected = filter_input(INPUT_GET, 'category_id', FILTER_VALIDATE_INT);
$selected = ($selected && $selected > 0) ? $selected : 0;

$pageTitle = 'Home';
require __DIR__ . '/includes/header.php';
?>
<div class="row">
    <!-- Category list -->
    <div class="col-md-3 mb-4">
        <div class="list-group" id="category-list">
            <a href="index.php" class="list-group-item list-group-item-action category-link <?= $selected === 0 ? 'active' : '' ?>" data-category="0">
                All Products
            </a>
            <?php foreach ($categories as $cat): ?>
                <a href="index.php?category_id=<?= (int)$cat['id'] ?>"
                   class="list-group-item list-group-item-action category-link <?= $selected === (int)$cat['id'] ? 'active' : '' ?>"
                   data-category="<?= (int)$cat['id'] ?>">
                    <?= e($cat['name']) ?>
                </a>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Products (filled by AJAX) -->
    <div class="col-md-9">
        <h4 id="products-title" class="mb-3">Products</h4>
        <div id="product-area" data-category="<?= $selected ?>" data-csrf="<?= e(csrf_token()) ?>">
            <div id="product-message" class="alert alert-info d-none"></div>
            <div id="product-grid" class="row row-cols-1 row-cols-sm-2 row-cols-lg-3 g-4"></div>
            <nav aria-label="Product pages" class="mt-4">
                <ul id="pagination" class="pagination justify-content-center flex-wrap"></ul>
            </nav>
        </div>
        <noscript><div class="alert alert-warning">Please enable JavaScript to see the products.</div></noscript>
    </div>
</div>

<script src="assets/js/products.js"></script>
<?php require __DIR__ . '/includes/footer.php'; ?>
