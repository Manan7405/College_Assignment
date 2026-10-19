<?php
/** Edit (UPDATE) one category. */
require_once __DIR__ . '/../includes/functions.php';
require_admin();

$pdo = get_pdo();
$id  = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if ($id === null || $id === false) {
    $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);   // the form sends id again
}
if (!$id) {
    set_flash('danger', 'Invalid category.');
    redirect('categories.php');
}

$stmt = $pdo->prepare('SELECT id, name FROM categories WHERE id = :id');
$stmt->execute([':id' => $id]);
$category = $stmt->fetch();
if (!$category) {
    set_flash('danger', 'Category not found.');
    redirect('categories.php');
}

$error = '';
$name  = $category['name'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');

    if (!csrf_valid()) {
        $error = 'Invalid form submission. Please try again.';
    } elseif ($name === '') {
        $error = 'Category name is required.';
    } elseif (mb_strlen($name) < 2 || mb_strlen($name) > 50) {
        $error = 'Category name must be 2 to 50 characters long.';
    } elseif (!preg_match('/^[\p{L}\p{N} &\-]+$/u', $name)) {
        $error = 'Category name may contain only letters, numbers, spaces, "&" and "-".';
    } else {
        // Duplicate check: another category (different id) with the same name?
        $check = $pdo->prepare('SELECT id FROM categories WHERE name = :name AND id <> :id');
        $check->execute([':name' => $name, ':id' => $id]);
        if ($check->fetch()) {
            $error = 'Another category already uses this name.';
        } else {
            $update = $pdo->prepare('UPDATE categories SET name = :name WHERE id = :id');
            $update->execute([':name' => $name, ':id' => $id]);
            set_flash('success', 'Category was updated.');
            redirect('categories.php');
        }
    }
}

$pageTitle = 'Edit Category';
require __DIR__ . '/_header.php';
?>
<h3 class="mb-3">Edit Category</h3>
<div class="row"><div class="col-md-6 col-lg-4">
    <div class="card shadow-sm"><div class="card-body">
        <?php if ($error !== ''): ?><div class="alert alert-danger"><?= e($error) ?></div><?php endif; ?>
        <form method="post" action="category_edit.php?id=<?= (int)$id ?>">
            <?= csrf_field() ?>
            <input type="hidden" name="id" value="<?= (int)$id ?>">
            <div class="mb-3">
                <label for="name" class="form-label">Category name</label>
                <input type="text" class="form-control" id="name" name="name" maxlength="50" value="<?= e($name) ?>" required>
            </div>
            <button type="submit" class="btn btn-primary">Save Changes</button>
            <a href="categories.php" class="btn btn-secondary">Cancel</a>
        </form>
    </div></div>
</div></div>
<?php require __DIR__ . '/_footer.php'; ?>
