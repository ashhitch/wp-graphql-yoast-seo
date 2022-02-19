<?php
/**
 * GraphQL Object - SEOContentTypeArchive
 *
 * @package WPGraphQL\YoastSEO\Type\WPObject\ContentType
 * @since @todo
 */

namespace WPGraphQL\YoastSEO\Type\WPObject\ContentType;

use WPGraphQL\Registry\TypeRegistry;
use WPGraphQL\YoastSEO\Interfaces\Registrable;
use WPGraphQL\YoastSEO\Interfaces\Type;
use WPGraphQL\YoastSEO\Interfaces\TypeWithFields;

/**
 * Class - SEOContentTypeArchive
 */
class Archive implements Registrable, Type, TypeWithFields {
	/**
	 * Type registered in WPGraphQL.
	 *
	 * @var string
	 */
	public static string $type = 'SEOContentTypeArchive';

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
		return __( 'The Yoast SEO search appearance content types fields.', 'wp-graphql-yoast-seo' );
	}

	/**
	 * {@inheritDoc}
	 */
	public static function get_fields() : array {
		// @todo add descriptions.
		return [
			'hasArchive'        => [ 'type' => 'Boolean' ],
			'title'             => [ 'type' => 'String' ],
			'archiveLink'       => [ 'type' => 'String' ],
			'metaDesc'          => [ 'type' => 'String' ],
			'metaRobotsNoindex' => [ 'type' => 'Boolean' ],
			'breadcrumbTitle'   => [ 'type' => 'String' ],
			'fullHead'          => [ 'type' => 'String' ],
		];
	}
}
