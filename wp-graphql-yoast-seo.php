<?php
/**
 * Plugin Name:     Add WPGraphQL SEO
 * Plugin URI:      https://github.com/ashhitch/wp-graphql-yoast-seo
 * Description: Adds support for The Events Calendar suite of plugins to WPGraphQL
 * Author:          Ash Hitchcock
 * Author URI:      https://www.ashleyhitchcock.com
 * Text Domain:     wp-graphql-yoast-seo
 * Domain Path:     /languages
 * Version:         4.16.1
 * Requires at least: 5.4.1
 * Tested up to: 5.9.0
 * Requires PHP: 7.4
 * License: GPL-3
 * License URI: https://www.gnu.org/licenses/gpl-3.0.html
 * WPGraphQL requires at least: 1.6.4+
 *
 * @package     WPGraphQL\YoastSEO
 * @author      ashhitch
 * @license     GPL-3
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit();
}

/**
 * Define plugin constants.
 */
function wp_gql_seo_constants() : void {
		// Plugin version.
	if ( ! defined( 'WPGRAPHQL_SEO_VERSION' ) ) {
		define( 'WPGRAPHQL_SEO_VERSION', '0.0.1' );
	}

			// Plugin Folder Path.
	if ( ! defined( 'WPGRAPHQL_SEO_PLUGIN_DIR' ) ) {
		define( 'WPGRAPHQL_SEO_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
	}

			// Plugin Folder URL.
	if ( ! defined( 'WPGRAPHQL_SEO_PLUGIN_URL' ) ) {
		define( 'WPGRAPHQL_SEO_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
	}

			// Plugin Root File.
	if ( ! defined( 'WPGRAPHQL_SEO_PLUGIN_FILE' ) ) {
		define( 'WPGRAPHQL_SEO_PLUGIN_FILE', __FILE__ );
	}

			// Whether to autoload the files or not.
	if ( ! defined( 'WPGRAPHQL_SEO_AUTOLOAD' ) ) {
		define( 'WPGRAPHQL_SEO_AUTOLOAD', true );
	}
}

/**
 * Checks if all the the required plugins are installed and activated.
 */
function wp_gql_seo_dependencies_not_ready() : array {
	$deps = [];
	if ( ! class_exists( 'WPGraphQL' ) ) {
		$deps[] = 'WPGraphQL';
	}

	if ( ! function_exists( 'YoastSEO' ) ) {
		$deps[] = 'Yoast SEO';
	}

	return $deps;
}

/**
 * Initializes WPGraphQL SEO
 * 
 * @return \WPGraphQL\YoastSEO\Seo|false
 */
function wp_gql_seo_init() {
	wp_gql_seo_constants();

	$not_ready = wp_gql_seo_dependencies_not_ready();

	if ( empty( $not_ready ) && defined( 'WPGRAPHQL_SEO_PLUGIN_DIR' ) ) {
		require_once WPGRAPHQL_SEO_PLUGIN_DIR . 'src/Seo.php';
		return \WPGraphQL\YoastSEO\Seo::instance();
	}

	foreach ( $not_ready as $dep ) {
		$display_admin_notice = static function() use ( $dep ) {
			?>
				<div class="notice notice-error>
					<p>
						<?php
							printf(
								/* translators: dependency not ready error message */
								esc_html__( '%1$s must be active for the WPGraphQL Yoast SEO plugin to work', 'wp-graphql-yoast-seo' ),
								esc_html( $dep )
							);
						?>
					</p>
				</div>
			<?php
		};
		add_action( 'network_admin_notices', $display_admin_notice );
		add_action( 'admin_notices', $display_admin_notice );
	}

	return false;
}
add_action( 'graphql_init', 'wp_gql_seo_init' );
