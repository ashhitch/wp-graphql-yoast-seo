<?php
/**
 * GraphQL Object - SEOSocial
 *
 * @package WPGraphQL\YoastSEO\Type\WPObject\Config
 * @since @todo
 */

namespace WPGraphQL\YoastSEO\Type\WPObject\Config;

use WPGraphQL\Registry\TypeRegistry;
use WPGraphQL\YoastSEO\Interfaces\Registrable;
use WPGraphQL\YoastSEO\Interfaces\Type;
use WPGraphQL\YoastSEO\Interfaces\TypeWithFields;
use WPGraphQL\YoastSEO\Type\WPObject\Config\Social as SEOSocial;

/**
 * Class - SEOSocial
 */
class Social implements Registrable, Type, TypeWithFields {
	/**
	 * Type registered in WPGraphQL.
	 *
	 * @var string
	 */
	public static string $type = 'SEOSocial';

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
		return __( 'The Yoast SEO Social media links', 'wp-graphql-yoast-seo' );
	}

	/**
	 * {@inheritDoc}
	 */
	public static function get_fields() : array {
		// @todo add descriptions.
		return [
			'facebook'  => [ 'type' => SEOSocial\Facebook::$type ],
			'twitter'   => [ 'type' => SEOSocial\Twitter::$type ],
			'instagram' => [ 'type' => SEOSocial\Instagram::$type ],
			'linkedIn'  => [ 'type' => SEOSocial\LinkedIn::$type ],
			'mySpace'   => [ 'type' => SEOSocial\MySpace::$type ],
			'pinterest' => [ 'type' => SEOSocial\Pinterest::$type ],
			'youTube'   => [ 'type' => SEOSocial\Youtube::$type ],
			'wikipedia' => [ 'type' => SEOSocial\Wikipedia::$type ],
		];
	}
}
