<?php
/**
 * Custom PHPUnit bootstrap file for Docker testing
 */

// Set the path to the PHPUnit Polyfills
define( 'WP_TESTS_PHPUNIT_POLYFILLS_PATH', '/var/www/html/wp-content/plugins/wp-graphql-yoast-seo/vendor/yoast/phpunit-polyfills' );

$_tests_dir = '/tmp/wordpress-tests-lib';

// Give access to tests_add_filter() function.
require_once $_tests_dir . '/includes/functions.php';

// Start up the WP testing environment.
require $_tests_dir . '/includes/bootstrap.php';
