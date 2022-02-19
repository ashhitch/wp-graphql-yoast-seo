<?php
/**
 * Adds filters that modify core schema.
 *
 * @package \WPGraphQL\YoastSEO
 * @since   @todo
 */

namespace WPGraphQL\YoastSEO;

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
}
