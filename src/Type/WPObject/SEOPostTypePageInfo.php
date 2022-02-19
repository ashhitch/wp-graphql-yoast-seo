<?php
/**
 * GraphQL Object - SEOPostTypePageInfo
 *
 * @package WPGraphQL\YoastSEO\Type\WPObject
 * @since @todo
 */

namespace WPGraphQL\YoastSEO\Type\WPObject;

use WPGraphQL\Registry\TypeRegistry;
use WPGraphQL\YoastSEO\Interfaces\Field;
use WPGraphQL\YoastSEO\Type\WPObject\PageInfo;
use WPGraphQL\YoastSEO\Interfaces\Registrable;
use WPGraphQL\YoastSEO\Interfaces\Type;
use WPGraphQL\YoastSEO\Interfaces\TypeWithFields;
use YoastSEO;
/**
 * Class - SEOPostTypePageInfo
 */
class SEOPostTypePageInfo implements Registrable, Type, TypeWithFields, Field {
	/**
	 * Type registered in WPGraphQL.
	 *
	 * @var string
	 */
	public static string $type = 'SEOPostTypePageInfo';

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

		self::register_field();
	}

	/**
	 * {@inheritDoc}
	 */
	public static function get_description() : string {
		return __( 'The page info SEO details', 'wp-graphql-yoast-seo' );
	}

	/**
	 * {@inheritDoc}
	 */
	public static function get_fields() : array {
		// @todo add descriptions.
		return [
			'schema' => [ 'type' => PageInfo\Schema::$type ],
		];
	}
	/**
	 * {@inheritDoc}
	 */
	public static function register_field() : void {
		// @todo this shouldn't be on every `pageInfo`.
		register_graphql_field(
			'WPPageInfo',
			'seo',
			[
				'type'        => self::$type,
				'description' => __( 'Raw schema for archive', 'wp-graphql-yoast-seo' ),
				'resolve'     => static function() {
					$yoast_meta   = YoastSEO()->meta->for_post_type_archive();
					$schema_array = false !== $yoast_meta ? $yoast_meta->schema : [];

					return [
						'schema' => [
							'raw' => json_encode( $schema_array, JSON_UNESCAPED_SLASHES ),
						],
					];
				},
			]
		);
	}
}
