<?php

define('DB_HOST', 'localhost');
define('DB_NAME', 'cineall');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

define('APP_NAME', 'CineAll');
define('APP_VERSION', '1.0.0');
define('APP_URL', 'http://localhost/cineall');

define('BASE_PATH', dirname(__DIR__));
define('CONFIG_PATH', BASE_PATH . '/config');
define('PHP_PATH', BASE_PATH . '/php');
define('INCLUDES_PATH', PHP_PATH . '/includes');
define('API_PATH', PHP_PATH . '/api');

define('SESSION_NAME', 'cineall_session');
define('SESSION_LIFETIME', 86400);

define('HASH_ALGO', PASSWORD_BCRYPT);
define('HASH_COST', 10);

define('ITEMS_PER_PAGE', 20);
define('MAX_ITEMS_PER_PAGE', 100);

define('DEBUG_MODE', true);

if (DEBUG_MODE) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

date_default_timezone_set('UTC');
