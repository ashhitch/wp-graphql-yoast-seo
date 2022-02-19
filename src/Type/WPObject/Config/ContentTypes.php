<?php
/**
 * GraphQL Object - SEOContentTypes
 *
 * @package WPGraphQL\YoastSEO\Type\WPObject\Config
 * @since @todo
 */

namespace WPGraphQL\YoastSEO\Type\WPObject\Config;

use WPGraphQL;
use WPGraphQL\Registry\TypeRegistry;
use WPGraphQL\YoastSEO\Interfaces\Registrable;
use WPGraphQL\YoastSEO\Interfaces\Type;
use WPGraphQL\YoastSEO\Interfaces\TypeWithFields;
use WPGraphQL\YoastSEO\Type\WPObject\Config\ContentType\ContentType;

/**
 * Class - SEOContentTypes
 */
class ContentTypes implements Registrable, Type, TypeWithFields {
	/**
	 * Type registered in WPGraphQL.
	 *
	 * @var string
	 */
	public static string $type = 'SEOContentTypes';

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
		return __( 'The Yoast SEO search appearance content types', 'wp-graphql-yoast-seo' );
	}

	/**
	 * {@inheritDoc}
	 */
	public static function get_fields() : array {
		$post_types = WPGraphQL::get_allowed_post_types();

		return self::build_content_types( $post_types );
	}

	private static function build_content_types( $types ) : array {
		$carry = [];

		foreach ( $types as $type ) {
			$post_type_object = get_post_type_object( $type );
			if ( $post_type_object->graphql_single_name ) {
				$carry[ wp_gql_seo_get_field_key( $post_type_object->graphql_single_name ) ] = [ 'type' => ContentType::$type ];
			}
		}
	
		return $carry;
	}
}
