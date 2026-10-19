<?php
/**
 * Shared database settings used by ALL questions that need MySQL
 * (Question 1, 2, 3 and 6).  Change them here once if your XAMPP
 * MySQL uses a different user, password or port.
 */
define('DB_HOST', 'localhost');
define('DB_PORT', '3306');            // XAMPP default. Change if MySQL runs on another port.
define('DB_NAME', 'practical_db');    // One database is used for every question.
define('DB_USER', 'root');            // XAMPP default user
define('DB_PASS', '');                // XAMPP default password is empty
