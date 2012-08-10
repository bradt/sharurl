<?php
$theme = 'default';

// MySQL settings
define('DB_NAME', '');     // The name of the database
define('DB_USER', '');     // Your MySQL username
define('DB_PASSWORD', ''); // ...and password
define('DB_HOST', 'localhost');     // 99% chance you won't need to change this value

define('DEBUG', 3);

// Filters
include_once('inc/filters.php');

// Navigation
include('inc/nav.php');

// Functions
include_once('inc/functions.php');

// Configuration Variables
include_once('inc/config.php');
