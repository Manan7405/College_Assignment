<?php
/**
 * AJAX endpoint: add one product to the session cart.
 * POST fields: product_id, csrf_token.     Response: JSON.
 */
define('JSON_RESPONSE', true);
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Use POST.']);
    exit;
}
if (!csrf_valid()) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Invalid request. Please refresh the page.']);
    exit;
}
if (!is_user_logged_in()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'login_required' => true, 'message' => 'Please log in to add items to your cart.']);
    exit;
}

$productId = filter_input(INPUT_POST, 'product_id', FILTER_VALIDATE_INT);
if (!$productId || $productId < 1 || !cart_add($productId, 1)) {
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'Product not found.']);
    exit;
}

echo json_encode(['success' => true, 'message' => 'Added to cart.', 'cart_count' => cart_count()]);
