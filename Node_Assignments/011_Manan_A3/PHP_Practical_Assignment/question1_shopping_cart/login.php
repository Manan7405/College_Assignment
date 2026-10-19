<?php
/** User login with username OR email + password (password_verify). */
require_once __DIR__ . '/includes/functions.php';

if (is_user_logged_in()) {
    redirect('index.php');
}

$error = '';
$login = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login    = trim($_POST['login'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!csrf_valid()) {
        $error = 'Invalid form submission. Please try again.';
    } elseif ($login === '' || $password === '') {
        $error = 'Please enter your username (or email) and password.';
    } else {
        // Two different placeholder names are required because real prepared statements
        // do not allow the same named placeholder twice.
        $stmt = get_pdo()->prepare(
            'SELECT id, full_name, password_hash FROM users WHERE username = :login_name OR email = :login_email LIMIT 1'
        );
        $stmt->execute([':login_name' => $login, ':login_email' => $login]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {
            session_regenerate_id(true);
            $_SESSION['user_id']   = (int)$user['id'];
            $_SESSION['user_name'] = $user['full_name'];
            set_flash('success', 'Welcome back, ' . $user['full_name'] . '!');
            redirect('index.php');
        } else {
            $error = 'Invalid username/email or password.';
        }
    }
}

$pageTitle = 'Login';
require __DIR__ . '/includes/header.php';
?>
<div class="row justify-content-center">
    <div class="col-md-6 col-lg-4">
        <div class="card shadow-sm"><div class="card-body p-4">
            <h4 class="mb-3">User Login</h4>
            <?php if ($error !== ''): ?><div class="alert alert-danger"><?= e($error) ?></div><?php endif; ?>
            <form method="post" action="login.php">
                <?= csrf_field() ?>
                <div class="mb-3">
                    <label for="login" class="form-label">Username or email</label>
                    <input type="text" class="form-control" id="login" name="login" value="<?= e($login) ?>" required autofocus>
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" class="form-control" id="password" name="password" required>
                </div>
                <button type="submit" class="btn btn-primary w-100">Login</button>
            </form>
            <p class="text-center mt-3 mb-0">New here? <a href="register.php">Create an account</a></p>
        </div></div>
    </div>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
