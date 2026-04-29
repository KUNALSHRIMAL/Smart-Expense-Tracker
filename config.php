<?php
define('APP_NAME', 'Smart Expense Tracker');
define('BASE_URL', '/money');          // Change to '' if deployed in web root
define('DB_HOST', 'localhost');
define('DB_NAME', 'expense_tracker');
define('DB_USER', 'root');
define('DB_PASS', '');

// Category list with emoji icons
global $CATEGORIES;
$CATEGORIES = [
    'Grocery' => '🛒',
    'Milk'    => '🥛',
    'Fuel'    => '⛽',
    'Food'    => '🍜',
    'Bills'   => '📋',
    'Medical' => '💊',
    'Other'   => '📦',
];
