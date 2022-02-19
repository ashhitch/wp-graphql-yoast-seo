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
 * Interface - Hookable
 */
interface Hookable {
	/**
	 * Register hooks with WordPress.
	 */
	public static function register_hooks() : void;
}
