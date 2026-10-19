<?php
/** Admin logout: remove admin data from the session and go to the login page. */
require_once __DIR__ . '/../includes/functions.php';

unset($_SESSION['admin_id'], $_SESSION['admin_username']);
session_regenerate_id(true);
set_flash('success', 'You have been logged out.');
redirect('login.php');
