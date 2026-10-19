<?php
/** List all products (READ) with picture thumbnails. */
require_once __DIR__ . '/../includes/functions.php';
require_admin();

$products = get_pdo()->query(
    'SELECT p.id, p.name, p.price, p.picture, c.name AS category_name
     FROM products p JOIN categories c ON c.id = p.category_id
     ORDER BY p.id DESC'
)->fetchAll();

$pageTitle = 'Products';
require __DIR__ . '/_header.php';
?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="mb-0">Manage Products</h3>
    <a href="product_form.php" class="btn btn-primary">+ Add Product</a>
</div>

<div class="table-responsive">
    <table class="table table-striped table-bordered align-middle bg-white">
        <thead class="table-light">
            <tr><th>#</th><th>Picture</th><th>Name</th><th>Category</th><th>Price</th><th style="width:170px">Actions</th></tr>
        </thead>
        <tbody>
        <?php if (!$products): ?>
            <tr><td colspan="6" class="text-center text-muted">No products yet.</td></tr>
        <?php endif; ?>
        <?php foreach ($products as $i => $p): ?>
            <tr>
                <td><?= $i + 1 ?></td>
                <td><img src="<?= e(product_image_url($p['picture'], '../')) ?>" alt="<?= e($p['name']) ?>" class="admin-thumb"></td>
                <td><?= e($p['name']) ?></td>
                <td><?= e($p['category_name']) ?></td>
                <td><?= e(money($p['price'])) ?></td>
                <td>
                    <a class="btn btn-sm btn-outline-primary" href="product_form.php?id=<?= (int)$p['id'] ?>">Edit</a>
                    <form method="post" action="product_delete.php" class="d-inline"
                          onsubmit="return confirm('Delete this product?');">
                        <?= csrf_field() ?>
                        <input type="hidden" name="id" value="<?= (int)$p['id'] ?>">
                        <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php require __DIR__ . '/_footer.php'; ?>
