#!/bin/bash

# Set up WordPress test environment
cd /var/www/html/wp-content/plugins/wp-graphql-yoast-seo

# Install WordPress test environment if not already installed
if [ ! -d /tmp/wordpress-tests-lib ]; then
  ./bin/install-wp-tests.sh exampledb exampleuser examplepass db latest true
fi

# Download and install WPGraphQL plugin if not already installed
if [ ! -d /tmp/wordpress/wp-content/plugins/wp-graphql ]; then
  echo 'Downloading WPGraphQL plugin...'
  mkdir -p /tmp/wordpress/wp-content/plugins/wp-graphql
  curl -L https://github.com/wp-graphql/wp-graphql/archive/refs/tags/v1.14.0.zip -o /tmp/wpgraphql.zip
  unzip -q /tmp/wpgraphql.zip -d /tmp
  cp -r /tmp/wp-graphql-1.14.0/* /tmp/wordpress/wp-content/plugins/wp-graphql/
  rm -rf /tmp/wp-graphql-1.14.0 /tmp/wpgraphql.zip
fi

# Download and install Yoast SEO plugin if not already installed
if [ ! -d /tmp/wordpress/wp-content/plugins/wordpress-seo ]; then
  echo 'Downloading Yoast SEO plugin...'
  mkdir -p /tmp/wordpress/wp-content/plugins/wordpress-seo
  curl -L https://downloads.wordpress.org/plugin/wordpress-seo.latest-stable.zip -o /tmp/yoast.zip
  unzip -q /tmp/yoast.zip -d /tmp
  cp -r /tmp/wordpress-seo/* /tmp/wordpress/wp-content/plugins/wordpress-seo/
  rm -rf /tmp/wordpress-seo /tmp/yoast.zip
fi

# Create a custom wp-tests-config.php file that loads our plugins
cat > /tmp/wordpress-tests-lib/wp-tests-config-custom.php << 'EOCFG'
<?php
/* Path to the WordPress codebase you'd like to test. Add a forward slash in the end. */
define( 'ABSPATH', '/tmp/wordpress/' );
define( 'WP_DEFAULT_THEME', 'default' );
define( 'WP_DEBUG', true );
define( 'DB_NAME', 'exampledb' );
define( 'DB_USER', 'exampleuser' );
define( 'DB_PASSWORD', 'examplepass' );
define( 'DB_HOST', 'db' );
define( 'DB_CHARSET', 'utf8' );
define( 'DB_COLLATE', '' );
define( 'AUTH_KEY',         'put your unique phrase here' );
define( 'SECURE_AUTH_KEY',  'put your unique phrase here' );
define( 'LOGGED_IN_KEY',    'put your unique phrase here' );
define( 'NONCE_KEY',        'put your unique phrase here' );
define( 'AUTH_SALT',        'put your unique phrase here' );
define( 'SECURE_AUTH_SALT', 'put your unique phrase here' );
define( 'LOGGED_IN_SALT',   'put your unique phrase here' );
define( 'NONCE_SALT',       'put your unique phrase here' );
 = 'wpgraphql_test_';
define( 'WP_TESTS_DOMAIN', 'example.org' );
define( 'WP_TESTS_EMAIL', 'admin@example.org' );
define( 'WP_TESTS_TITLE', 'Test Blog' );
define( 'WP_PHP_BINARY', 'php' );
define( 'WPLANG', '' );

// Load the plugins for testing
function _manually_load_plugin() {
    // Load our plugin
    require_once '/var/www/html/wp-content/plugins/wp-graphql-yoast-seo/wp-graphql-yoast-seo.php';
    
    // Load WPGraphQL
    if (file_exists('/tmp/wordpress/wp-content/plugins/wp-graphql/wp-graphql.php')) {
        require_once '/tmp/wordpress/wp-content/plugins/wp-graphql/wp-graphql.php';
    }
    
    // Load Yoast SEO
    if (file_exists('/tmp/wordpress/wp-content/plugins/wordpress-seo/wp-seo.php')) {
        require_once '/tmp/wordpress/wp-content/plugins/wordpress-seo/wp-seo.php';
    }
}
tests_add_filter( 'muplugins_loaded', '_manually_load_plugin' );
EOCFG

# Replace the original wp-tests-config.php with our custom one
cp /tmp/wordpress-tests-lib/wp-tests-config-custom.php /tmp/wordpress-tests-lib/wp-tests-config.php

# Create a custom bootstrap file for our tests
cat > ./tests/custom-bootstrap.php << 'EOBOOT'
<?php
/**
 * Custom PHPUnit bootstrap file for Docker testing
 */

// Set the path to the PHPUnit Polyfills
define( 'WP_TESTS_PHPUNIT_POLYFILLS_PATH', '/var/www/html/wp-content/plugins/wp-graphql-yoast-seo/vendor/yoast/phpunit-polyfills' );

 = '/tmp/wordpress-tests-lib';

// Give access to tests_add_filter() function.
require_once  . '/includes/functions.php';

// Start up the WP testing environment.
require  . '/includes/bootstrap.php';
EOBOOT

# Run the tests with our custom bootstrap
if [ -n  ]; then
  # Run specific test file
  ./vendor/bin/phpunit --bootstrap=tests/custom-bootstrap.php 
else
  # Run all tests
  ./vendor/bin/phpunit --bootstrap=tests/custom-bootstrap.php
fi
