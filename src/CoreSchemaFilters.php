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
use WPGraphQL\YoastSEO\Type\WPInterface\ContentTypeWithSEO;
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
		add_Filter( 'graphql_wp_object_type_config', [ __CLASS__, 'set_object_type_config' ] );
	}

	/**
	 * Overwrites the GraphQL config for auto-registered interface types.
	 *
	 * @param array $config .
	 */
	public static function set_interface_type_config( array $config ) : array {
		if ( ! isset( $config['name'] ) ) {
			return $config;
		}
		// @todo Remove product check once it implements 'ContentNode'.
		if ( 'ContentNode' === $config['name'] || 'Product' === $config['name'] ) {
			$config['interfaces'] = array_merge(
				$config['interfaces'] ?? [],
				[
					ContentNodeWithSEO::$type,
				]
			);
		}



		// Add SEO interface to term nodes.
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

	/**
	 * Overwrites the GraphQL config for auto-registered object types.
	 *
	 * @param array $config .
	 */
	public static function set_object_type_config( array $config ) {
		if ( ! isset( $config['name'] ) ) {
			return $config;
		}
		
		// Add SEO interface to content types.
		if ( 'ContentType' === $config['name'] ) {
			$config['interfaces'] = array_merge(
				$config['interfaces'] ?? [],
				[
					ContentTypeWithSEO::$type,
				]
			);
		}

		return $config;
	}
}
