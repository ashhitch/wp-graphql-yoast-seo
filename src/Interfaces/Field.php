<?php
/**
 * Interface for a GraphQL Field
 *
 * @package WPGraphQL\YoastSEO\Interfaces
 * @since @todo
 */

namespace WPGraphQL\YoastSEO\Interfaces;

/**
 * Interface - Field.
 */
/**
 * Interface - Field
 */
interface Field {
	/**
	 * Register field in GraphQL schema.
	 */
	public static function register_field() : void;
}


