<?php
/**
 * GraphQL Object - SEOOpenGraphFrontPage
 *
 * @package WPGraphQL\YoastSEO\Type\WPObject\Config\OpenGraph
 * @since @todo
 * 
 * @todo only register on Yoast Premium
 */

namespace WPGraphQL\YoastSEO\Type\WPObject\Config\OpenGraph;

use WPGraphQL\Registry\TypeRegistry;
use WPGraphQL\YoastSEO\Interfaces\Registrable;
use WPGraphQL\YoastSEO\Interfaces\Type;
use WPGraphQL\YoastSEO\Interfaces\TypeWithFields;


/**
 * Class - SEOOpenGraphFrontPage
 */
class FrontPage implements Registrable, Type, TypeWithFields {
	/**
	 * Type registered in WPGraphQL.
	 *
	 * @var string
	 */
	public static string $type = 'SEOOpenGraphFrontPage';

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
		return __( 'The Open Graph Front page data', 'wp-graphql-yoast-seo' );
	}

	/**
	 * {@inheritDoc}
	 */
	public static function get_fields() : array {
		// @todo add descriptions.
		return [
			'title'       => [ 'type' => 'String' ],
			'description' => [ 'type' => 'String' ],
			'image'       => [ 'type' => 'MediaItem' ],
		];
	}
}
