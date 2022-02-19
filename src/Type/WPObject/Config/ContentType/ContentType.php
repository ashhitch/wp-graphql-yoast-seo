<?php
/**
 * GraphQL Object - SEOContentType
 *
 * @package WPGraphQL\YoastSEO\Type\WPObject\Config\ContentType
 * @since @todo
 */

namespace WPGraphQL\YoastSEO\Type\WPObject\Config\ContentType;

use WPGraphQL\Registry\TypeRegistry;
use WPGraphQL\YoastSEO\Interfaces\Registrable;
use WPGraphQL\YoastSEO\Interfaces\Type;
use WPGraphQL\YoastSEO\Interfaces\TypeWithFields;
use WPGraphQL\YoastSEO\Type\WPObject\ContentType\Archive;

/**
 * Class - SEOContentType
 */
class ContentType implements Registrable, Type, TypeWithFields {
	/**
	 * Type registered in WPGraphQL.
	 *
	 * @var string
	 */
	public static string $type = 'SEOContentType';

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
		return __( 'The Yoast SEO search appearance content types fields', 'wp-graphql-yoast-seo' );
	}

	/**
	 * {@inheritDoc}
	 */
	public static function get_fields() : array {
		// @todo add descriptions.
		return [
			'title'             => [ 'type' => 'String' ],
			'metaDesc'          => [ 'type' => 'String' ],
			'metaRobotsNoindex' => [ 'type' => 'Boolean' ],
			'schemaType'        => [ 'type' => 'String' ],
			'schema'            => [ 'type' => 'SEOPageInfoSchema' ],
			'archive'           => [ 'type' => Archive::$type ],
		];
	}
}
