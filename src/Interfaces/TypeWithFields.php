<?php
/**
 * Interface for a GraphQL Type that has GraphQL fields.
 *
 * @package WPGraphQL\YoastSEO\Interfaces
 * @since @todo
 */

namespace WPGraphQL\YoastSEO\Interfaces;

/**
 * Interface - TypeWithFields.
 */
interface TypeWithFields {
	/**
	 * Gets the GraphQL fields for the type.
	 */
	public static function get_fields() : array;
}

