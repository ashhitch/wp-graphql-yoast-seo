<?php
/**
 * GraphQL Object - SEOUserSocial
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
 * Class - SEOSocial
 */
class Social implements Registrable, Type, TypeWithFields {
	/**
	 * Type registered in WPGraphQL.
	 *
	 * @var string
	 */
	public static string $type = 'SEOUserSocial';

	/**
	 * {@inheritDoc}
	 */
	public static function register( TypeRegistry $type_registry = null ) : void { // phpcs:ignore VariableAnalysis.CodeAnalysis.VariableAnalysis.UnusedVariable
		register_graphql_object_type(
			static::$type,
			[
				// phpcs:ignore
				// 'description' => static::get_description(),
				'fields' => static::get_fields(),
			]
		);
	}

	/**
	 * {@inheritDoc}
	 */
	public static function get_description() : string {
		// @todo add description
		return __( 'The Social types for User', 'wp-graphql-yoast-seo' );
	}

	/**
	 * {@inheritDoc}
	 */
	public static function get_fields() : array {
		// @todo add descriptions.
		return [
			'facebook'   => [ 'type' => 'String' ],
			'twitter'    => [ 'type' => 'String' ],
			'instagram'  => [ 'type' => 'String' ],
			'linkedIn'   => [ 'type' => 'String' ],
			'mySpace'    => [ 'type' => 'String' ],
			'pinterest'  => [ 'type' => 'String' ],
			'youTube'    => [ 'type' => 'String' ],
			'soundCloud' => [ 'type' => 'String' ],
			'wikipedia'  => [ 'type' => 'String' ],
		];
	}
}
