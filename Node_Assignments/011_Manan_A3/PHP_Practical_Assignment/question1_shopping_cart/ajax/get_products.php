<?php
/**
 * AJAX endpoint: returns one page of products as JSON.
 *
 * Request :  ajax/get_products.php?category_id=2&page=1     (category_id 0 = all)
 * Response:  { success, products:[...], page, total_pages, total, category_name }
 */
define('JSON_RESPONSE', true);     // tells db.php to answer with JSON on errors
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json; charset=utf-8');

// ---- read and validate the input ----
$categoryId = filter_input(INPUT_GET, 'category_id', FILTER_VALIDATE_INT);
$categoryId = ($categoryId && $categoryId > 0) ? $categoryId : 0;

$page = filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT);
$page = ($page && $page > 0) ? $page : 1;

$perPage = PRODUCTS_PER_PAGE;
$pdo     = get_pdo();

try {
    // ---- category name (for the heading) ----
    $categoryName = 'All Products';
    if ($categoryId > 0) {
        $stmt = $pdo->prepare('SELECT name FROM categories WHERE id = :id');
        $stmt->execute([':id' => $categoryId]);
        $row = $stmt->fetch();
        if (!$row) {
            echo json_encode(['success' => false, 'message' => 'Category not found.']);
            exit;
        }
        $categoryName = $row['name'];
    }

    // ---- 1. count the products so we know how many pages exist ----
    if ($categoryId > 0) {
        $stmt = $pdo->prepare('SELECT COUNT(*) FROM products WHERE category_id = :cid');
        $stmt->execute([':cid' => $categoryId]);
    } else {
        $stmt = $pdo->query('SELECT COUNT(*) FROM products');
    }
    $total      = (int)$stmt->fetchColumn();
    $totalPages = max(1, (int)ceil($total / $perPage));
    if ($page > $totalPages) {
        $page = $totalPages;                     // never go past the last page
    }
    $offset = ($page - 1) * $perPage;

    // ---- 2. fetch only the products of this page (LIMIT / OFFSET) ----
    $sql = 'SELECT id, name, price, picture FROM products '
         . ($categoryId > 0 ? 'WHERE category_id = :cid ' : '')
         . 'ORDER BY id ASC LIMIT :limit OFFSET :offset';
    $stmt = $pdo->prepare($sql);
    if ($categoryId > 0) {
        $stmt->bindValue(':cid', $categoryId, PDO::PARAM_INT);
    }
    $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();

    $products = [];
    foreach ($stmt->fetchAll() as $row) {
        $products[] = [
            'id'              => (int)$row['id'],
            'name'            => $row['name'],
            'price_formatted' => money($row['price']),
            'image_url'       => product_image_url($row['picture']),   // relative to the site root
        ];
    }

    echo json_encode([
        'success'       => true,
        'products'      => $products,
        'page'          => $page,
        'total_pages'   => $totalPages,
        'total'         => $total,
        'category_id'   => $categoryId,
        'category_name' => $categoryName,
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Could not load products.']);
}
