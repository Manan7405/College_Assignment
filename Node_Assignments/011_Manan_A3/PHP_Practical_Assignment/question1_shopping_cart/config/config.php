<?php
/**
 * Question 1 settings.
 * Database login details are shared with the other questions
 * and live in  PHP_Practical_Assignment/common/db_config.php
 */
require_once __DIR__ . '/../../common/db_config.php';

// ---- Image upload settings ----
define('UPLOAD_DIR', __DIR__ . '/../uploads/products/');   // folder on the server's disk
define('UPLOAD_URL', 'uploads/products/');                  // path used in <img src> (relative to the site root)
define('MAX_UPLOAD_SIZE', 2 * 1024 * 1024);                 // 2 MB
define('MAX_IMAGE_DIMENSION', 800);                         // longest side after resizing (pixels)

// ---- Shop settings ----
define('PRODUCTS_PER_PAGE', 6);                             // products on one AJAX page
define('MAX_CART_QTY', 99);                                 // max quantity of one product
define('CURRENCY_SYMBOL', 'Rs. ');
