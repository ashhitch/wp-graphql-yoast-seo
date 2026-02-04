<?php // phpcs:ignore

/**
 * Plugin Name: Add WPGraphQL SEO
 * Plugin URI: https://github.com/ashhitch/wp-graphql-yoast-seo
 * Description: A WPGraphQL Extension that adds support for Yoast SEO
 * Author: Ash Hitchcock
 * Author URI: https://www.ashleyhitchcock.com
 * Text Domain: wp-graphql-yoast-seo
 * Domain Path: /languages
 * Version: v5.0.2
 * Requires Plugins: wp-graphql, wordpress-seo
 *
 * @package WP_Graphql_YOAST_SEO
 */

if (!defined('ABSPATH')) {
    exit();
}

/**
 * Define plugin constants
 */
define('WPGRAPHQL_YOAST_SEO_VERSION', 'v5.0.0');
define('WPGRAPHQL_YOAST_SEO_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('WPGRAPHQL_YOAST_SEO_PLUGIN_URL', plugin_dir_url(__FILE__));

/**
 * Include dependencies
 */
require_once WPGRAPHQL_YOAST_SEO_PLUGIN_DIR . 'includes/admin/dependencies.php';
require_once WPGRAPHQL_YOAST_SEO_PLUGIN_DIR . 'includes/helpers/functions.php';

/**
 * Initialize the plugin
 */
add_action('graphql_init', function () {
    // Include schema and resolvers only when WPGraphQL is active
    require_once WPGRAPHQL_YOAST_SEO_PLUGIN_DIR . 'includes/schema/types.php';
    require_once WPGRAPHQL_YOAST_SEO_PLUGIN_DIR . 'includes/resolvers/post-type.php';
    require_once WPGRAPHQL_YOAST_SEO_PLUGIN_DIR . 'includes/resolvers/taxonomy.php';
    require_once WPGRAPHQL_YOAST_SEO_PLUGIN_DIR . 'includes/resolvers/user.php';
    require_once WPGRAPHQL_YOAST_SEO_PLUGIN_DIR . 'includes/resolvers/root-query.php';
});
