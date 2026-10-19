<?php
/**
 * Manage categories: shows all categories (READ) and an "Add" form (CREATE).
 * Edit -> category_edit.php      Delete -> category_delete.php
 */
require_once __DIR__ . '/../includes/functions.php';
require_admin();

$pdo   = get_pdo();
$error = '';
$name  = '';

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
        // Duplicate check
        $check = $pdo->prepare('SELECT id FROM categories WHERE name = :name');
        $check->execute([':name' => $name]);
        if ($check->fetch()) {
            $error = 'A category with this name already exists.';
        } else {
            $insert = $pdo->prepare('INSERT INTO categories (name) VALUES (:name)');
            $insert->execute([':name' => $name]);
            set_flash('success', 'Category "' . $name . '" was added.');
            redirect('categories.php');       // redirect after POST avoids double submit
        }
    }
}

// Read all categories together with how many products each one has
$categories = $pdo->query(
    'SELECT c.id, c.name, COUNT(p.id) AS product_count
     FROM categories c LEFT JOIN products p ON p.category_id = c.id
     GROUP BY c.id, c.name ORDER BY c.name'
)->fetchAll();

$pageTitle = 'Categories';
require __DIR__ . '/_header.php';
?>
<h3 class="mb-3">Manage Categories</h3>

<div class="row g-4">
    <div class="col-lg-4">
        <div class="card shadow-sm">
            <div class="card-header">Add a category</div>
            <div class="card-body">
                <?php if ($error !== ''): ?><div class="alert alert-danger"><?= e($error) ?></div><?php endif; ?>
                <form method="post" action="categories.php">
                    <?= csrf_field() ?>
                    <div class="mb-3">
                        <label for="name" class="form-label">Category name</label>
                        <input type="text" class="form-control" id="name" name="name" maxlength="50" value="<?= e($name) ?>" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Add Category</button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <div class="table-responsive">
            <table class="table table-striped table-bordered align-middle bg-white">
                <thead class="table-light">
                    <tr><th>#</th><th>Name</th><th>Products</th><th style="width:170px">Actions</th></tr>
                </thead>
                <tbody>
                <?php if (!$categories): ?>
                    <tr><td colspan="4" class="text-center text-muted">No categories yet.</td></tr>
                <?php endif; ?>
                <?php foreach ($categories as $i => $cat): ?>
                    <tr>
                        <td><?= $i + 1 ?></td>
                        <td><?= e($cat['name']) ?></td>
                        <td><?= (int)$cat['product_count'] ?></td>
                        <td>
                            <a class="btn btn-sm btn-outline-primary" href="category_edit.php?id=<?= (int)$cat['id'] ?>">Edit</a>
                            <form method="post" action="category_delete.php" class="d-inline"
                                  onsubmit="return confirm('Delete this category?');">
                                <?= csrf_field() ?>
                                <input type="hidden" name="id" value="<?= (int)$cat['id'] ?>">
                                <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php require __DIR__ . '/_footer.php'; ?>
