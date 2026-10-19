<?php
/** User logout: clears the user's login and cart, then goes back to the home page. */
require_once __DIR__ . '/includes/functions.php';

unset($_SESSION['user_id'], $_SESSION['user_name'], $_SESSION['cart']);
session_regenerate_id(true);
set_flash('success', 'You have been logged out.');
redirect('index.php');
