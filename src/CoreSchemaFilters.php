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
		add_filter( 'graphql_wp_interface_type_config', [ __CLASS__, 'set_interface_type_config' ] );
	}

	/**
	 * Overwrites the GraphQL config for auto-registered object types.
	 *
	 * @param array $config .
	 */
	public static function set_interface_type_config( array $config ) : array {
		if ( ! isset( $config['name'] ) ) {
			return $config;
		}

		if ( 'ContentNode' === $config['name'] ) {
			$config['interfaces'] = array_merge(
				$config['interfaces'] ?? [],
				[
					ContentNodeWithSEO::$type,
				]
			);
		}

		// Add SEO interface to post types.
		if ( 'TermNode' === $config['name'] ) {
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
