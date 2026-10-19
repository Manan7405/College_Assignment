<?php
/** Delete (DELETE) a product and its picture file. POST + CSRF token only. */
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/image_helper.php';
require_admin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrf_valid()) {
    set_flash('danger', 'Invalid request.');
    redirect('products.php');
}

$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
if (!$id) {
    set_flash('danger', 'Invalid product.');
    redirect('products.php');
}

$pdo = get_pdo();
$stmt = $pdo->prepare('SELECT picture FROM products WHERE id = :id');
$stmt->execute([':id' => $id]);
$product = $stmt->fetch();

if (!$product) {
    set_flash('warning', 'Product not found.');
    redirect('products.php');
}

$delete = $pdo->prepare('DELETE FROM products WHERE id = :id');
$delete->execute([':id' => $id]);

// Remove the picture file too (keep the bundled sample_ pictures so a re-import of the SQL still shows them)
if ($product['picture'] && strpos($product['picture'], 'sample_') !== 0) {
    delete_product_image($product['picture']);
}

set_flash('success', 'Product was deleted.');
redirect('products.php');
