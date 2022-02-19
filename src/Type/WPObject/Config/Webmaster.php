<?php
/**
 * GraphQL Object - SEOWebmaster
 *
 * @package WPGraphQL\YoastSEO\Type\WPObject\Config
 * @since @todo
 */

namespace WPGraphQL\YoastSEO\Type\WPObject\Config;

use WPGraphQL\Registry\TypeRegistry;
use WPGraphQL\YoastSEO\Interfaces\Registrable;
use WPGraphQL\YoastSEO\Interfaces\Type;
use WPGraphQL\YoastSEO\Interfaces\TypeWithFields;

/**
 * Class - SEOWebmaster
 */
class Webmaster implements Registrable, Type, TypeWithFields {
	/**
	 * Type registered in WPGraphQL.
	 *
	 * @var string
	 */
	public static string $type = 'SEOWebmaster';

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
		return __( 'The Yoast SEO webmaster fields', 'wp-graphql-yoast-seo' );
	}

	/**
	 * {@inheritDoc}
	 */
	public static function get_fields() : array {
		// @todo add descriptions.
		return [
			'baiduVerify'  => [ 'type' => 'String' ],
			'googleVerify' => [ 'type' => 'String' ],
			'msVerify'     => [ 'type' => 'String' ],
			'yandexVerify' => [ 'type' => 'String' ],
		];
	}
}
