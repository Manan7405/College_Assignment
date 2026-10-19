<?php
/**
 * Database helper for Questions 2, 3 and 6 (the "students" table).
 *
 * get_db_connection():
 *   1. connects to the MySQL server,
 *   2. creates the database if it does not exist yet,
 *   3. creates the students table if it does not exist yet,
 *   4. returns a PDO object that uses prepared statements.
 *
 * Because of this, Questions 2, 3 and 6 can run even if you have not
 * imported any .sql file.  (Question 1 still needs shopping_cart.sql.)
 *
 * It throws a PDOException on failure; the calling script catches it.
 */
require_once __DIR__ . '/db_config.php';

function get_db_connection(): PDO
{
    static $pdo = null;          // keep one connection per request
    if ($pdo !== null) {
        return $pdo;
    }

    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,   // throw exceptions on errors
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,                    // real prepared statements
    ];

    // Step 1 + 2: connect to the server (no database yet) and create the database.
    $server = new PDO(
        'mysql:host=' . DB_HOST . ';port=' . DB_PORT . ';charset=utf8mb4',
        DB_USER, DB_PASS, $options
    );
    $server->exec('CREATE DATABASE IF NOT EXISTS `' . DB_NAME . '` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');

    // Step 3: connect to the database itself.
    $pdo = new PDO(
        'mysql:host=' . DB_HOST . ';port=' . DB_PORT . ';dbname=' . DB_NAME . ';charset=utf8mb4',
        DB_USER, DB_PASS, $options
    );

    ensure_students_table($pdo);
    return $pdo;
}

/** Creates the students table when it is missing. */
function ensure_students_table(PDO $pdo): void
{
    $pdo->exec(
        'CREATE TABLE IF NOT EXISTS students (
            student_id INT UNSIGNED NOT NULL,
            name       VARCHAR(100) NOT NULL,
            email      VARCHAR(150) NOT NULL,
            course     VARCHAR(100) NOT NULL,
            marks      DECIMAL(5,2) NOT NULL DEFAULT 0.00,
            PRIMARY KEY (student_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4'
    );
}
