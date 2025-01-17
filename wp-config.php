<?php
define('DB_NAME', 'exampledb');
define('DB_USER', 'exampleuser');
define('DB_PASSWORD', 'examplepass');
define('DB_HOST', 'db'); // Ensure this matches your Docker service name for the database
define('DB_CHARSET', 'utf8');
define('DB_COLLATE', '');

$table_prefix = 'wp_';

define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
define('WP_DEBUG_DISPLAY', true);

if (!defined('ABSPATH')) {
    define('ABSPATH', dirname(__FILE__) . '/');
}

require_once ABSPATH . 'wp-settings.php';
