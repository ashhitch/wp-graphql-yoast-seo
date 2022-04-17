<?php
/**
 * GraphQL Interface - ContentTypeWithSEO
 *
 * @package WPGraphQL\YoastSEO\Type\WPInterface
 * @since @todo
 */

namespace WPGraphQL\YoastSEO\Type\WPInterface;

use WP_Post_Type;
use WPGraphQL\YoastSEO\Interfaces\TypeWithFields;
use WPGraphQL\Registry\TypeRegistry;
use WPGraphQL\YoastSEO\Interfaces\Registrable;
use WPGraphQL\YoastSEO\Interfaces\Type;
use WPGraphQL\YoastSEO\Type\WPObject\Config\ContentType\ContentType;
/**
 * Class - ContentTypeWithSEO
 */
class ContentTypeWithSEO implements Registrable, Type, TypeWithFields {
	/**
	 * Type registered in WPGraphQL.
	 *
	 * @var string
	 */
	public static string $type = 'ContentTypeWithSEO';

	/**
	 * {@inheritDoc}
	 */
	public static function register( TypeRegistry $type_registry = null ) : void {
		// Bail early if no type registry.
		if ( null === $type_registry ) {
			return;
		}

		register_graphql_interface_type(
			static::$type,
			[
				'description' => self::get_description(),
				'fields'      => self::get_fields(),
			]
		);
	}

	/**
	 * {@inheritDoc}
	 */
	public static function get_description() : string {
		return __( 'A Post type that can have assigned to it.', 'wp-graphql-yoast-seo' );
	}

	/**
	 * {@inheritDoc}
	 */
	public static function get_fields() : array {
		return [
			'seo' => [
				'type'    => ContentType::$type,
				'resolve' => function ( $source ) {
					return $source;
				},
			],
		];
	}
}
