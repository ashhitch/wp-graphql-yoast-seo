<?php
/**
 * Interface for a GraphQL Type
 *
 * @package WPGraphQL\YoastSEO\Interfaces
 * @since @todo
 */

namespace WPGraphQL\YoastSEO\Interfaces;

/**
 * Interface - Type.
 */
interface Type {
	/**
	 * Gets the GraphQL type description.
	 */
	public static function get_description() : string;
}

