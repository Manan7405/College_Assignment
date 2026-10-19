<?php
/**
 * User registration with a simple arithmetic CAPTCHA.
 *
 * CAPTCHA idea:  the server makes a question like "7 + 4", keeps the
 * answer (11) in $_SESSION and the browser only ever sees the question.
 * When the form is submitted we compare the typed answer with the session.
 */
require_once __DIR__ . '/includes/functions.php';

if (is_user_logged_in()) {
    redirect('index.php');
}

$errors = [];
$old    = ['full_name' => '', 'email' => '', 'username' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $old['full_name'] = trim($_POST['full_name'] ?? '');
    $old['email']     = trim($_POST['email'] ?? '');
    $old['username']  = trim($_POST['username'] ?? '');
    $password         = $_POST['password'] ?? '';
    $confirm          = $_POST['confirm_password'] ?? '';
    $captchaInput     = trim($_POST['captcha'] ?? '');

    if (!csrf_valid()) {
        $errors[] = 'Invalid form submission. Please try again.';
    }

    // ---- validation ----
    if ($old['full_name'] === '' || mb_strlen($old['full_name']) < 2 || mb_strlen($old['full_name']) > 100) {
        $errors[] = 'Full name is required (2 to 100 characters).';
    }
    if ($old['email'] === '' || !filter_var($old['email'], FILTER_VALIDATE_EMAIL) || mb_strlen($old['email']) > 150) {
        $errors[] = 'Please enter a valid email address.';
    }
    if (!preg_match('/^[A-Za-z0-9_]{3,30}$/', $old['username'])) {
        $errors[] = 'Username must be 3 to 30 characters: letters, numbers and underscore only.';
    }
    if (strlen($password) < 8) {
        $errors[] = 'Password must be at least 8 characters long.';
    }
    if ($password !== $confirm) {
        $errors[] = 'Password and confirm password do not match.';
    }

    // ---- CAPTCHA check (server side) ----
    $expected = $_SESSION['captcha_answer'] ?? null;
    if ($captchaInput === '' || $expected === null
        || filter_var($captchaInput, FILTER_VALIDATE_INT) === false
        || (int)$captchaInput !== (int)$expected) {
        $errors[] = 'CAPTCHA answer is wrong. Please solve the new question.';
    }

    // ---- duplicate username / email ----
    if (!$errors) {
        $stmt = get_pdo()->prepare('SELECT username, email FROM users WHERE username = :username OR email = :email');
        $stmt->execute([':username' => $old['username'], ':email' => $old['email']]);
        foreach ($stmt->fetchAll() as $row) {
            if (strcasecmp($row['username'], $old['username']) === 0) {
                $errors[] = 'This username is already taken.';
            }
            if (strcasecmp($row['email'], $old['email']) === 0) {
                $errors[] = 'This email address is already registered.';
            }
        }
        $errors = array_values(array_unique($errors));
    }

    // ---- save the user ----
    if (!$errors) {
        try {
            $insert = get_pdo()->prepare(
                'INSERT INTO users (full_name, email, username, password_hash)
                 VALUES (:full_name, :email, :username, :password_hash)'
            );
            $insert->execute([
                ':full_name'     => $old['full_name'],
                ':email'         => $old['email'],
                ':username'      => $old['username'],
                ':password_hash' => password_hash($password, PASSWORD_DEFAULT),   // never store plain text
            ]);
            unset($_SESSION['captcha_answer']);
            set_flash('success', 'Registration successful! You can log in now.');
            redirect('login.php');
        } catch (PDOException $e) {
            // 23000 = unique constraint (someone registered the same name a moment ago)
            $errors[] = ($e->getCode() === '23000')
                ? 'This username or email is already registered.'
                : 'Registration failed. Please try again.';
        }
    }
}

// ---- make a NEW captcha question every time the page is shown ----
$a  = random_int(1, 9);
$b  = random_int(1, 9);
if (random_int(0, 1) === 1) {
    $captchaQuestion = "$a + $b";
    $_SESSION['captcha_answer'] = $a + $b;
} else {
    if ($a < $b) { [$a, $b] = [$b, $a]; }            // keep the answer positive
    $captchaQuestion = "$a - $b";
    $_SESSION['captcha_answer'] = $a - $b;
}

$pageTitle = 'Register';
require __DIR__ . '/includes/header.php';
?>
<div class="row justify-content-center">
    <div class="col-md-8 col-lg-6">
        <div class="card shadow-sm"><div class="card-body p-4">
            <h4 class="mb-3">Create an account</h4>

            <?php if ($errors): ?>
                <div class="alert alert-danger">
                    <ul class="mb-0"><?php foreach ($errors as $err): ?><li><?= e($err) ?></li><?php endforeach; ?></ul>
                </div>
            <?php endif; ?>

            <form method="post" action="register.php" autocomplete="off">
                <?= csrf_field() ?>
                <div class="mb-3">
                    <label for="full_name" class="form-label">Full name</label>
                    <input type="text" class="form-control" id="full_name" name="full_name" maxlength="100" value="<?= e($old['full_name']) ?>" required>
                </div>
                <div class="mb-3">
                    <label for="email" class="form-label">Email address</label>
                    <input type="email" class="form-control" id="email" name="email" maxlength="150" value="<?= e($old['email']) ?>" required>
                </div>
                <div class="mb-3">
                    <label for="username" class="form-label">Username</label>
                    <input type="text" class="form-control" id="username" name="username" maxlength="30" value="<?= e($old['username']) ?>" required>
                    <div class="form-text">3-30 characters: letters, numbers, underscore.</div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control" id="password" name="password" minlength="8" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="confirm_password" class="form-label">Confirm password</label>
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" minlength="8" required>
                    </div>
                </div>
                <div class="mb-3">
                    <label for="captcha" class="form-label">CAPTCHA: what is <strong><?= e($captchaQuestion) ?></strong> ?</label>
                    <input type="text" class="form-control" id="captcha" name="captcha" inputmode="numeric" required>
                </div>
                <button type="submit" class="btn btn-primary w-100">Register</button>
            </form>
            <p class="text-center mt-3 mb-0">Already registered? <a href="login.php">Login</a></p>
        </div></div>
    </div>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
