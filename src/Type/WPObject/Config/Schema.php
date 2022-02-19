<?php
/**
 * GraphQL Object - SEOSchema
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
 * Class - SEOSchema
 */
class Schema implements Registrable, Type, TypeWithFields {
	/**
	 * Type registered in WPGraphQL.
	 *
	 * @var string
	 */
	public static string $type = 'SEOSchema';

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
		return __( 'The Yoast SEO schema data', 'wp-graphql-yoast-seo' );
	}

	/**
	 * {@inheritDoc}
	 */
	public static function get_fields() : array {
		// @todo add descriptions.
		return [
			'companyName'       => [ 'type' => 'String' ],
			'personName'        => [ 'type' => 'String' ],
			'companyOrPerson'   => [ 'type' => 'String' ],
			'companyLogo'       => [ 'type' => 'MediaItem' ],
			'personLogo'        => [ 'type' => 'MediaItem' ],
			'logo'              => [ 'type' => 'MediaItem' ],
			'siteName'          => [ 'type' => 'String' ],
			'wordpressSiteName' => [ 'type' => 'String' ],
			'siteUrl'           => [ 'type' => 'String' ],
			'homeUrl'           => [ 'type' => 'String' ],
			'inLanguage'        => [ 'type' => 'String' ],
		];
	}
}
