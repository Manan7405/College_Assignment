<?php
/**
 * Shopping cart page.
 * POST actions: increase, decrease, remove, clear   (each with a CSRF token).
 * The cart itself lives in $_SESSION['cart'] (see includes/functions.php).
 */
require_once __DIR__ . '/includes/functions.php';
require_user();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_valid()) {
        set_flash('danger', 'Invalid request. Please try again.');
        redirect('cart.php');
    }

    $action    = $_POST['action'] ?? '';
    $productId = filter_input(INPUT_POST, 'product_id', FILTER_VALIDATE_INT);

    if ($action === 'clear') {
        unset($_SESSION['cart']);
        set_flash('info', 'Your cart is now empty.');
    } elseif ($productId && $productId > 0 && isset($_SESSION['cart'][$productId])) {
        $currentQty = (int)$_SESSION['cart'][$productId];
        switch ($action) {
            case 'increase':
                cart_set_quantity($productId, $currentQty + 1);
                break;
            case 'decrease':
                cart_set_quantity($productId, $currentQty - 1);      // 0 removes the item
                break;
            case 'remove':
                cart_remove($productId);
                set_flash('info', 'Item removed from the cart.');
                break;
            default:
                set_flash('danger', 'Unknown action.');
        }
    }
    redirect('cart.php');
}

$cart = cart_details();

$pageTitle = 'My Cart';
require __DIR__ . '/includes/header.php';
?>
<h3 class="mb-3">My Shopping Cart</h3>

<?php if (!$cart['items']): ?>
    <div class="alert alert-info">Your cart is empty. <a href="index.php">Continue shopping</a></div>
<?php else: ?>
    <div class="table-responsive">
        <table class="table table-bordered align-middle bg-white">
            <thead class="table-light">
                <tr><th>Product</th><th class="text-end">Price</th><th class="text-center" style="width:190px">Quantity</th><th class="text-end">Subtotal</th><th></th></tr>
            </thead>
            <tbody>
            <?php foreach ($cart['items'] as $item): ?>
                <tr>
                    <td>
                        <img src="<?= e(product_image_url($item['picture'])) ?>" alt="" class="cart-thumb me-2">
                        <?= e($item['name']) ?>
                    </td>
                    <td class="text-end"><?= e(money($item['price'])) ?></td>
                    <td class="text-center">
                        <form method="post" action="cart.php" class="d-inline-flex align-items-center">
                            <?= csrf_field() ?>
                            <input type="hidden" name="product_id" value="<?= (int)$item['id'] ?>">
                            <button type="submit" name="action" value="decrease" class="btn btn-sm btn-outline-secondary">&minus;</button>
                            <span class="mx-3"><?= (int)$item['quantity'] ?></span>
                            <button type="submit" name="action" value="increase" class="btn btn-sm btn-outline-secondary">+</button>
                        </form>
                    </td>
                    <td class="text-end"><?= e(money($item['subtotal'])) ?></td>
                    <td class="text-center">
                        <form method="post" action="cart.php">
                            <?= csrf_field() ?>
                            <input type="hidden" name="product_id" value="<?= (int)$item['id'] ?>">
                            <button type="submit" name="action" value="remove" class="btn btn-sm btn-outline-danger">Remove</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr class="table-light">
                    <th colspan="3" class="text-end">Grand Total</th>
                    <th class="text-end"><?= e(money($cart['grand_total'])) ?></th>
                    <th></th>
                </tr>
            </tfoot>
        </table>
    </div>

    <div class="d-flex justify-content-between">
        <a href="index.php" class="btn btn-outline-primary">&larr; Continue shopping</a>
        <form method="post" action="cart.php" onsubmit="return confirm('Remove everything from the cart?');">
            <?= csrf_field() ?>
            <button type="submit" name="action" value="clear" class="btn btn-outline-danger">Clear cart</button>
        </form>
    </div>
    <p class="text-muted small mt-3">No payment gateway is included in this practical, so there is no checkout button.</p>
<?php endif; ?>
<?php require __DIR__ . '/includes/footer.php'; ?>
