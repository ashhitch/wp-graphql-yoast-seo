<?php
// Set the path to the PHPUnit Polyfills
define( 'WP_TESTS_PHPUNIT_POLYFILLS_PATH', '/var/www/html/wp-content/plugins/wp-graphql-yoast-seo/vendor/yoast/phpunit-polyfills' );

// Include the original bootstrap file
require_once __DIR__ . '/bootstrap.php';

// Include the custom loader
require_once __DIR__ . '/custom-loader.php';
