<?php
/**
 * GraphQL Object - SEOSchema
 *
 * @package WPGraphQL\YoastSEO\Type\WPObject\User
 * @since @todo
 */

namespace WPGraphQL\YoastSEO\Type\WPObject\User;

use WPGraphQL\Registry\TypeRegistry;
use WPGraphQL\YoastSEO\Interfaces\Registrable;
use WPGraphQL\YoastSEO\Interfaces\Type;
use WPGraphQL\YoastSEO\Interfaces\TypeWithFields;

/**
 * Class - SEOSchema
 */
class Schema implements Registrable, Type, TypeWithFields {
	/**
	 * Type registered in WPGraphQL.
	 *
	 * @var string
	 */
	public static string $type = 'SEOUserSchema';

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
		return __( 'The Schema types for User', 'wp-graphql-yoast-seo' );
	}

	/**
	 * {@inheritDoc}
	 */
	public static function get_fields() : array {
		// @todo add descriptions.
		return [
			'raw'         => [ 'type' => 'String' ],
			'pageType'    => [ 'type' => [ 'list_of' => 'String' ] ],
			'articleType' => [ 'type' => [ 'list_of' => 'String' ] ],
		];
	}
}
