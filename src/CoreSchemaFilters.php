<?php
/**
 * Adds filters that modify core schema.
 *
 * @package \WPGraphQL\YoastSEO
 * @since   @todo
 */

namespace WPGraphQL\YoastSEO;

use WPGraphQL;
use WPGraphQL\YoastSEO\Interfaces\Hookable;
use WPGraphQL\YoastSEO\Type\WPInterface\ContentNodeWithSEO;
use WPGraphQL\YoastSEO\Type\WPInterface\TermNodeWithSEO;

/**
 * Class - CoreSchemaFilters
 */
class CoreSchemaFilters implements Hookable {
	/**
	 * {@inheritDoc}
	 */
	public static function register_hooks(): void {
		add_filter( 'graphql_wp_object_type_config', [ __CLASS__, 'set_object_type_config' ] );
		add_filter( 'graphql_wp_connection_type_config', [ __CLASS__, 'set_connection_type_config' ] );
	}

	/**
	 * Overwrites the GraphQL config for auto-registered object types.
	 *
	 * @param array $config .
	 */
	public static function set_object_type_config( array $config ) : array {
		if ( ! isset( $config['interfaces'] ) ) {
			return $config;
		}

		// Add SEO interface to post types.
		if ( in_array( 'ContentNode', $config['interfaces'], true ) ) {
			$config['interfaces'] = array_merge(
				$config['interfaces'] ?? [],
				[
					ContentNodeWithSEO::$type,
				]
			);
		}

		// Add SEO interface to post types.
		if ( in_array( 'TermNode', $config['interfaces'], true ) ) {
			$config['interfaces'] = array_merge(
				$config['interfaces'] ?? [],
				[
					TermNodeWithSEO::$type,
				]
			);
		}

		return $config;
	}

	public static function set_connection_type_config( array $config ) : array {
		global $wp_post_types, $wp_taxonomies;
		$post_types = WPGraphQL::get_allowed_post_types();
		$taxonomies = WPGraphQL::get_allowed_taxonomies();
		// If WooCommerce installed then add these post types and taxonomies
		if ( class_exists( '\WooCommerce' ) ) {
			array_push( $post_types, 'product' );
			array_push( $taxonomies, 'productCategory' );
		}
		if ( ! in_array( $config['fromType'], $post_types, true ) || ! in_array( $config['toType'], $taxonomies, true ) ) {
			error_log( 'BAD:' . $config['fromType'] . ' to ' . $config['toType'] );
			return $config;
		}

		error_log( 'GOOD:' . $config['fromType'] . ' to ' . $config['toType'] );




		return $config;
	}
}
