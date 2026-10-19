<?php
/**
 * Helper: creates a password hash that you can paste into the admins table.
 *
 * Run from the command line :  php generate_hash.php mySecret123
 * or open in the browser    :  http://localhost/PHP_Practical_Assignment/database/generate_hash.php?pw=mySecret123
 *
 * Then run in phpMyAdmin:
 *   UPDATE admins SET password_hash = '<paste hash here>' WHERE username = 'admin';
 */
$password = PHP_SAPI === 'cli' ? ($argv[1] ?? 'admin123') : ($_GET['pw'] ?? 'admin123');

// password_hash() adds a random salt, so the output is different every time,
// but password_verify() can still check it.
$hash = password_hash($password, PASSWORD_DEFAULT);

if (PHP_SAPI === 'cli') {
    echo $hash . PHP_EOL;
} else {
    header('Content-Type: text/plain; charset=utf-8');
    echo "Password : " . $password . "\n";
    echo "Hash     : " . $hash . "\n";
}
