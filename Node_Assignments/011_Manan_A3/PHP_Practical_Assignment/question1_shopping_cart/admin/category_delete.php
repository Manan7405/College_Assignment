<?php
/** Delete (DELETE) a category. Only accepts a POST request with a valid CSRF token. */
require_once __DIR__ . '/../includes/functions.php';
require_admin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrf_valid()) {
    set_flash('danger', 'Invalid request.');
    redirect('categories.php');
}

$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
if (!$id) {
    set_flash('danger', 'Invalid category.');
    redirect('categories.php');
}

$pdo = get_pdo();

// A category that still has products cannot be deleted (foreign key RESTRICT)
$count = $pdo->prepare('SELECT COUNT(*) FROM products WHERE category_id = :id');
$count->execute([':id' => $id]);
$productCount = (int)$count->fetchColumn();

if ($productCount > 0) {
    set_flash('danger', 'This category still has ' . $productCount . ' product(s). Delete or move those products first.');
    redirect('categories.php');
}

try {
    $delete = $pdo->prepare('DELETE FROM categories WHERE id = :id');
    $delete->execute([':id' => $id]);
    set_flash($delete->rowCount() > 0 ? 'success' : 'warning',
              $delete->rowCount() > 0 ? 'Category was deleted.' : 'Category not found.');
} catch (PDOException $e) {
    set_flash('danger', 'The category could not be deleted.');
}
redirect('categories.php');
