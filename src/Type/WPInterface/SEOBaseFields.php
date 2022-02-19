<?php
/**
 * GraphQL Interface - SEOBaseFields
 *
 * @package WPGraphQL\YoastSEO\Type\WPInterface
 * @since @todo
 */

namespace WPGraphQL\YoastSEO\Type\WPInterface;

use WPGraphQL\Registry\TypeRegistry;
use WPGraphQL\YoastSEO\Interfaces\Registrable;
use WPGraphQL\YoastSEO\Interfaces\Type;
use WPGraphQL\YoastSEO\Type\WPObject\SEOPostTypeBreadcrumbs;

/**
 * Class - SEOBaseFields
 */
class SEOBaseFields implements Registrable, Type {
	/**
	 * Type registered in WPGraphQL.
	 *
	 * @var string
	 */
	public static string $type = 'SEOBaseFields';

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
		return __( 'Base SEO Fields.', 'wp-graphql-yoast-seo' );
	}

	/**
	 * {@inheritDoc}
	 */
	public static function get_fields() : array {
		return [
			'title'                  => [ 'type' => 'String' ],
			'metaDesc'               => [ 'type' => 'String' ],
			'focuskw'                => [ 'type' => 'String' ],
			'metaKeywords'           => [ 'type' => 'String' ],
			'metaRobotsNoindex'      => [ 'type' => 'String' ],
			'metaRobotsNofollow'     => [ 'type' => 'String' ],
			'opengraphTitle'         => [ 'type' => 'String' ],
			'opengraphUrl'           => [ 'type' => 'String' ],
			'opengraphSiteName'      => [ 'type' => 'String' ],
			'opengraphType'          => [ 'type' => 'String' ],
			'opengraphAuthor'        => [ 'type' => 'String' ],
			'opengraphPublisher'     => [ 'type' => 'String' ],
			'opengraphPublishedTime' => [ 'type' => 'String' ],
			'opengraphModifiedTime'  => [ 'type' => 'String' ],
			'opengraphDescription'   => [ 'type' => 'String' ],
			'opengraphImage'         => [ 'type' => 'MediaItem' ],
			'twitterTitle'           => [ 'type' => 'String' ],
			'twitterDescription'     => [ 'type' => 'String' ],
			'twitterImage'           => [ 'type' => 'MediaItem' ],
			'canonical'              => [ 'type' => 'String' ],
			'breadcrumbs'            => [ 'type' => [ 'list_of' => SEOPostTypeBreadcrumbs::$type ] ],
			'cornerstone'            => [ 'type' => 'Boolean' ],
			'fullHead'               => [ 'type' => 'String' ],
		];
	}
}
