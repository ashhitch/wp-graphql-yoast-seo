<?php
/**
 * Interface for classes containing WordPress action/filter hooks.
 *
 * @package WPGraphQL\YoastSEO\Interfaces
 * @since @todo
 */

namespace WPGraphQL\YoastSEO\Interfaces;

use WPGraphQL\Registry\TypeRegistry;

/**
 * Interface - Registrable
 */
interface Registrable {
	/**
	 * Register connections to the GraphQL Schema.
	 *
	 * @param TypeRegistry $type_registry The GraphQL type registry.
	 */
	public static function register( TypeRegistry $type_registry = null ) : void;
}
