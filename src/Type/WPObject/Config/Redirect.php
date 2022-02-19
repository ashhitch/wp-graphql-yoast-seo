<?php
/**
 * GraphQL Object - SEORedirect
 *
 * @package WPGraphQL\YoastSEO\Type\WPObject\Config
 * @since @todo
 * 
 * @todo only register on Yoast Premium
 */

namespace WPGraphQL\YoastSEO\Type\WPObject\Config;

use WPGraphQL\Registry\TypeRegistry;
use WPGraphQL\YoastSEO\Interfaces\Registrable;
use WPGraphQL\YoastSEO\Interfaces\Type;
use WPGraphQL\YoastSEO\Interfaces\TypeWithFields;


/**
 * Class - SEORedirect
 */
class Redirect implements Registrable, Type, TypeWithFields {
	/**
	 * Type registered in WPGraphQL.
	 *
	 * @var string
	 */
	public static string $type = 'SEORedirect';

	/**
	 * {@inheritDoc}
	 */
	public static function register( TypeRegistry $type_registry = null ) : void { // phpcs:ignore VariableAnalysis.CodeAnalysis.VariableAnalysis.UnusedVariable
		register_graphql_object_type(
			static::$type,
			[
				'description' => static::get_description(),
				'fields'      => static::get_fields(),
			]
		);
	}

	/**
	 * {@inheritDoc}
	 */
	public static function get_description() : string {
		return __( 'The Yoast redirect data  (Yoast Premium only)', 'wp-graphql-yoast-seo' );
	}

	/**
	 * {@inheritDoc}
	 */
	public static function get_fields() : array {
		// @todo add descriptions.
		return [
			'origin' => [ 'type' => 'String' ],
			'target' => [ 'type' => 'String' ],
			'type'   => [ 'type' => 'Int' ],
			'format' => [ 'type' => 'String' ],
		];
	}
}
