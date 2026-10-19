<?php
/**
 * Common helper functions: sessions, flash messages, CSRF, login checks, cart.
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/db.php';

/* ---------- output escaping (prevents XSS) ---------- */
function e($value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

/* ---------- redirect ---------- */
function redirect(string $url): void
{
    header('Location: ' . $url);
    exit;
}

/* ---------- flash messages (shown once, then removed) ---------- */
function set_flash(string $type, string $message): void
{
    $_SESSION['flash'][] = ['type' => $type, 'message' => $message];
}

function show_flash(): void
{
    if (empty($_SESSION['flash'])) {
        return;
    }
    foreach ($_SESSION['flash'] as $flash) {
        echo '<div class="alert alert-' . e($flash['type']) . ' alert-dismissible fade show" role="alert">'
           . e($flash['message'])
           . '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>';
    }
    unset($_SESSION['flash']);
}

/* ---------- CSRF protection for forms ---------- */
function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">';
}

function csrf_valid(): bool
{
    return isset($_POST['csrf_token'], $_SESSION['csrf_token'])
        && is_string($_POST['csrf_token'])
        && hash_equals($_SESSION['csrf_token'], $_POST['csrf_token']);
}

/* ---------- admin session ---------- */
function is_admin_logged_in(): bool
{
    return !empty($_SESSION['admin_id']);
}

/** Call at the top of every admin page. */
function require_admin(): void
{
    if (!is_admin_logged_in()) {
        set_flash('warning', 'Please log in as admin first.');
        redirect('login.php');          // admin pages live in /admin, so this is admin/login.php
    }
}

/* ---------- user session ---------- */
function is_user_logged_in(): bool
{
    return !empty($_SESSION['user_id']);
}

/** Call at the top of user pages that need a login. */
function require_user(): void
{
    if (!is_user_logged_in()) {
        set_flash('warning', 'Please log in to continue.');
        redirect('login.php');          // user pages live in the project root
    }
}

/* ---------- money ---------- */
function money($amount): string
{
    return CURRENCY_SYMBOL . number_format((float)$amount, 2);
}

/* ---------- product picture URL ---------- */
/**
 * Returns the URL of a product picture, or the "no image" picture
 * if the file name is empty or the file is missing on disk.
 * $prefix is '' for pages in the root and '../' for pages in /admin.
 */
function product_image_url(?string $filename, string $prefix = ''): string
{
    if ($filename !== null && $filename !== '') {
        $safe = basename($filename);
        if (is_file(UPLOAD_DIR . $safe)) {
            return $prefix . UPLOAD_URL . rawurlencode($safe);
        }
    }
    return $prefix . 'assets/img/no-image.png';
}

/* =====================================================
 *  SHOPPING CART  (stored in $_SESSION['cart'])
 *  Format: [ product_id => quantity, ... ]
 *  Only ids and quantities are stored. Prices are ALWAYS read
 *  from the database so a user can never change a price.
 * ===================================================== */
function cart_count(): int
{
    return empty($_SESSION['cart']) ? 0 : (int)array_sum($_SESSION['cart']);
}

/** Add $qty items of a product. Returns false if the product does not exist. */
function cart_add(int $productId, int $qty = 1): bool
{
    $qty = max(1, min(MAX_CART_QTY, $qty));

    $stmt = get_pdo()->prepare('SELECT id FROM products WHERE id = :id');
    $stmt->execute([':id' => $productId]);
    if (!$stmt->fetch()) {
        return false;
    }

    $current = $_SESSION['cart'][$productId] ?? 0;
    $_SESSION['cart'][$productId] = min(MAX_CART_QTY, $current + $qty);
    return true;
}

/** Set an exact quantity. Quantity 0 (or less) removes the item. */
function cart_set_quantity(int $productId, int $qty): void
{
    if (!isset($_SESSION['cart'][$productId])) {
        return;
    }
    if ($qty <= 0) {
        unset($_SESSION['cart'][$productId]);
    } else {
        $_SESSION['cart'][$productId] = min(MAX_CART_QTY, $qty);
    }
}

function cart_remove(int $productId): void
{
    unset($_SESSION['cart'][$productId]);
}

/**
 * Loads full product details for everything in the cart and calculates totals.
 * Returns [ 'items' => [...], 'grand_total' => float ].
 */
function cart_details(): array
{
    $result = ['items' => [], 'grand_total' => 0.0];
    if (empty($_SESSION['cart'])) {
        return $result;
    }

    $ids = array_map('intval', array_keys($_SESSION['cart']));
    $placeholders = implode(',', array_fill(0, count($ids), '?'));   // ?,?,?
    $stmt = get_pdo()->prepare("SELECT id, name, price, picture FROM products WHERE id IN ($placeholders)");
    $stmt->execute($ids);
    $rows = $stmt->fetchAll();

    $found = [];
    foreach ($rows as $row) {
        $qty = (int)$_SESSION['cart'][$row['id']];
        $subtotal = (float)$row['price'] * $qty;
        $row['quantity'] = $qty;
        $row['subtotal'] = $subtotal;
        $result['items'][] = $row;
        $result['grand_total'] += $subtotal;
        $found[$row['id']] = true;
    }

    // Remove cart entries whose product was deleted by the admin meanwhile
    foreach ($ids as $id) {
        if (!isset($found[$id])) {
            unset($_SESSION['cart'][$id]);
        }
    }
    return $result;
}
