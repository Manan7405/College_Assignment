<?php
/**
 * One form for BOTH adding and editing a product.
 *   product_form.php        -> add a new product
 *   product_form.php?id=5   -> edit product 5
 */
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/image_helper.php';
require_admin();

$pdo = get_pdo();

// Are we editing?
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if ($id === null || $id === false) {
    $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
}
$isEdit = (bool)$id;

$product = ['name' => '', 'description' => '', 'price' => '', 'category_id' => '', 'picture' => ''];
if ($isEdit) {
    $stmt = $pdo->prepare('SELECT * FROM products WHERE id = :id');
    $stmt->execute([':id' => $id]);
    $found = $stmt->fetch();
    if (!$found) {
        set_flash('danger', 'Product not found.');
        redirect('products.php');
    }
    $product = $found;
}

$categories = $pdo->query('SELECT id, name FROM categories ORDER BY name')->fetchAll();
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Keep what the admin typed so the form is not emptied on errors
    $product['name']        = trim($_POST['name'] ?? '');
    $product['description'] = trim($_POST['description'] ?? '');
    $product['price']       = trim($_POST['price'] ?? '');
    $product['category_id'] = $_POST['category_id'] ?? '';

    if (!csrf_valid()) {
        $errors[] = 'Invalid form submission. Please try again.';
    }

    // ---- validation ----
    if ($product['name'] === '' || mb_strlen($product['name']) < 2 || mb_strlen($product['name']) > 100) {
        $errors[] = 'Product name is required (2 to 100 characters).';
    }
    if (mb_strlen($product['description']) > 1000) {
        $errors[] = 'Description can be at most 1000 characters.';
    }
    if (!is_numeric($product['price']) || (float)$product['price'] <= 0 || (float)$product['price'] > 9999999.99) {
        $errors[] = 'Price must be a number greater than 0.';
    }
    $categoryId = filter_var($product['category_id'], FILTER_VALIDATE_INT);
    if (!$categoryId) {
        $errors[] = 'Please choose a category.';
    } else {
        $check = $pdo->prepare('SELECT id FROM categories WHERE id = :id');
        $check->execute([':id' => $categoryId]);
        if (!$check->fetch()) {
            $errors[] = 'The selected category does not exist.';
        }
    }

    // ---- picture upload ----
    $newPicture = '';
    if (!$errors) {
        $uploadError = null;
        $result = save_product_image($_FILES['picture'] ?? ['error' => UPLOAD_ERR_NO_FILE], $uploadError);
        if ($result === null) {
            $errors[] = $uploadError;
        } else {
            $newPicture = $result;      // '' means no new picture was chosen
        }
    }

    // ---- save ----
    if (!$errors) {
        $price = round((float)$product['price'], 2);
        try {
            if ($isEdit) {
                $oldPicture = $product['picture'];
                $pictureToSave = ($newPicture !== '') ? $newPicture : $oldPicture;

                $update = $pdo->prepare(
                    'UPDATE products SET name = :name, description = :description, price = :price,
                            category_id = :category_id, picture = :picture WHERE id = :id'
                );
                $update->execute([
                    ':name' => $product['name'], ':description' => $product['description'],
                    ':price' => $price, ':category_id' => $categoryId,
                    ':picture' => $pictureToSave, ':id' => $id,
                ]);
                // delete the replaced picture file (but never the shared sample pictures)
                if ($newPicture !== '' && $oldPicture && strpos($oldPicture, 'sample_') !== 0) {
                    delete_product_image($oldPicture);
                }
                set_flash('success', 'Product was updated.');
            } else {
                $insert = $pdo->prepare(
                    'INSERT INTO products (category_id, name, description, price, picture)
                     VALUES (:category_id, :name, :description, :price, :picture)'
                );
                $insert->execute([
                    ':category_id' => $categoryId, ':name' => $product['name'],
                    ':description' => $product['description'], ':price' => $price,
                    ':picture' => ($newPicture !== '') ? $newPicture : null,
                ]);
                set_flash('success', 'Product was added.');
            }
            redirect('products.php');
        } catch (PDOException $e) {
            // The database failed: remove the picture we just saved so no orphan file stays
            if ($newPicture !== '') {
                delete_product_image($newPicture);
            }
            $errors[] = 'The product could not be saved. Please try again.';
        }
    }
}

$pageTitle = $isEdit ? 'Edit Product' : 'Add Product';
require __DIR__ . '/_header.php';
?>
<h3 class="mb-3"><?= e($pageTitle) ?></h3>

<?php if (!gd_available()): ?>
    <div class="alert alert-warning">
        <strong>GD extension is not enabled.</strong> Pictures will be uploaded but not resized.
        Enable <code>extension=gd</code> in php.ini and restart Apache.
    </div>
<?php endif; ?>

<?php if ($errors): ?>
    <div class="alert alert-danger">
        <ul class="mb-0"><?php foreach ($errors as $err): ?><li><?= e($err) ?></li><?php endforeach; ?></ul>
    </div>
<?php endif; ?>

<div class="card shadow-sm"><div class="card-body">
    <form method="post" action="product_form.php<?= $isEdit ? '?id=' . (int)$id : '' ?>" enctype="multipart/form-data">
        <?= csrf_field() ?>
        <?php if ($isEdit): ?><input type="hidden" name="id" value="<?= (int)$id ?>"><?php endif; ?>

        <div class="row g-3">
            <div class="col-md-6">
                <label for="name" class="form-label">Product name</label>
                <input type="text" class="form-control" id="name" name="name" maxlength="100" value="<?= e($product['name']) ?>" required>
            </div>
            <div class="col-md-3">
                <label for="price" class="form-label">Price</label>
                <input type="number" step="0.01" min="0.01" class="form-control" id="price" name="price" value="<?= e($product['price']) ?>" required>
            </div>
            <div class="col-md-3">
                <label for="category_id" class="form-label">Category</label>
                <select class="form-select" id="category_id" name="category_id" required>
                    <option value="">-- choose --</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= (int)$cat['id'] ?>" <?= ((string)$product['category_id'] === (string)$cat['id']) ? 'selected' : '' ?>>
                            <?= e($cat['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-12">
                <label for="description" class="form-label">Description</label>
                <textarea class="form-control" id="description" name="description" rows="3" maxlength="1000"><?= e($product['description']) ?></textarea>
            </div>
            <div class="col-md-6">
                <label for="picture" class="form-label">Picture (JPEG, PNG or WebP, max 2 MB)</label>
                <input type="file" class="form-control" id="picture" name="picture" accept="image/jpeg,image/png,image/webp">
                <?php if ($isEdit): ?><div class="form-text">Leave empty to keep the current picture.</div><?php endif; ?>
            </div>
            <?php if ($isEdit): ?>
            <div class="col-md-6">
                <label class="form-label d-block">Current picture</label>
                <img src="<?= e(product_image_url($product['picture'], '../')) ?>" alt="Current picture" class="admin-thumb-large">
            </div>
            <?php endif; ?>
        </div>

        <div class="mt-4">
            <button type="submit" class="btn btn-primary"><?= $isEdit ? 'Save Changes' : 'Add Product' ?></button>
            <a href="products.php" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div></div>
<?php require __DIR__ . '/_footer.php'; ?>
