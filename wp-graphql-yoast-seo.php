<?php // phpcs:ignore

/**
 * Plugin Name:     Add WPGraphQL SEO
 * Plugin URI:      https://github.com/ashhitch/wp-graphql-yoast-seo
 * Description:     A WPGraphQL Extension that adds support for Yoast SEO
 * Author:          Ash Hitchcock
 * Author URI:      https://www.ashleyhitchcock.com
 * Text Domain:     wp-graphql-yoast-seo
 * Domain Path:     /languages
 * Version:         4.19.0
 *
 * @package         WP_Graphql_YOAST_SEO
 */

if (!defined('ABSPATH')) {
    exit();
}

define('SRC_PATH', plugin_dir_path(__FILE__) . '/src');
require_once SRC_PATH . '/init.php';

add_action('admin_init', 'init');

add_action('graphql_init', function () {
    add_action('graphql_register_types', function () {
        require_once SRC_PATH . '/utils.php';
        require_once SRC_PATH . '/types.php';
        require_once SRC_PATH . '/fields.php';
    });
});
