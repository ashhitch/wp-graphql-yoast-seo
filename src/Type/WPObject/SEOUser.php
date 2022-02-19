<?php
/**
 * GraphQL Object - SEOUser
 *
 * @package WPGraphQL\YoastSEO\Type\WPObject
 * @since @todo
 */

namespace WPGraphQL\YoastSEO\Type\WPObject;

use function YoastSEO;
use WPGraphQL\AppContext;
use WPGraphQL\Model\User as ModelUser;
use WPGraphQL\Registry\TypeRegistry;
use WPGraphQL\YoastSEO\Interfaces\Field;
use WPGraphQL\YoastSEO\Interfaces\Registrable;
use WPGraphQL\YoastSEO\Interfaces\Type;
use WPGraphQL\YoastSEO\Interfaces\TypeWithFields;
use WPGraphQL\YoastSEO\Type\WPObject\User;

/**
 * Class - SEOUser
 */
class SEOUser implements Registrable, Type, TypeWithFields, Field {
	/**
	 * Type registered in WPGraphQL.
	 *
	 * @var string
	 */
	public static string $type = 'SEOUser';

	/**
	 * The field name registered to the schema.
	 *
	 * @var string
	 */
	public static string $fieldname = 'seo';

	/**
	 * {@inheritDoc}
	 */
	public static function register( TypeRegistry $type_registry = null ) : void { // phpcs:ignore VariableAnalysis.CodeAnalysis.VariableAnalysis.UnusedVariable
		register_graphql_object_type(
			static::$type,
			[
				// phpcs:ignore
				// 'description' => static::get_description(),
				'fields' => static::get_fields(),
			]
		);

		self::register_field();
	}

	/**
	 * {@inheritDoc}
	 */
	public static function get_description() : string {
		// @todo add description.
		return __( 'The Yoast SEO site level configuration data.', 'wp-graphql-yoast-seo' );
	}

	/**
	 * {@inheritDoc}
	 */
	public static function get_fields() : array {
		// @todo add descriptions.
		return [
			'title'                => [ 'type' => 'String' ],
			'metaDesc'             => [ 'type' => 'String' ],
			'metaRobotsNoindex'    => [ 'type' => 'String' ],
			'metaRobotsNofollow'   => [ 'type' => 'String' ],
			'canonical'            => [ 'type' => 'String' ],
			'opengraphTitle'       => [ 'type' => 'String' ],
			'opengraphDescription' => [ 'type' => 'String' ],
			'opengraphImage'       => [ 'type' => 'MediaItem' ],
			'twitterImage'         => [ 'type' => 'MediaItem' ],
			'twitterTitle'         => [ 'type' => 'String' ],
			'twitterDescription'   => [ 'type' => 'String' ],
			'language'             => [ 'type' => 'String' ],
			'region'               => [ 'type' => 'String' ],
			'breadcrumbTitle'      => [ 'type' => 'String' ],
			'fullHead'             => [ 'type' => 'String' ],
			'social'               => [ 'type' => User\Social::$type ],
			'schema'               => [ 'type' => User\Schema::$type ],
		];
	}
	/**
	 * {@inheritDoc}
	 */
	public static function register_field() : void {
		register_graphql_field(
			'User',
			static::$fieldname,
			[
				'type'        => static::$type,
				'description' => __( 'The Yoast SEO data of a user', 'wp-graphql-yoast-seo' ),
				'resolve'     => static function ( ModelUser $user, array $args, AppContext $context ) {
					$yoast_meta = YoastSEO()->meta->for_author( $user->userId );
					$robots     = isset( $yoast_meta->robots ) ? $yoast_meta->robots : [];

					$schema_array = isset( $yoast_meta->schema ) ? $yoast_meta->schema : [];

					$userSeo = [
						'title'                => wp_gql_seo_format_string( $yoast_meta->title ),
						'metaDesc'             => wp_gql_seo_format_string( $yoast_meta->description ),
						'metaRobotsNoindex'    => $robots['index'] ?? null,
						'metaRobotsNofollow'   => $robots['follow'] ?? null,
						'canonical'            => isset( $yoast_meta->canonical ) ? $yoast_meta->canonical : null,
						'opengraphTitle'       => isset( $yoast_meta->open_graph_title ) ? $yoast_meta->open_graph_title : null,
						'opengraphDescription' => isset( $yoast_meta->open_graph_description ) ? $yoast_meta->open_graph_description : null,
						'opengraphImage'       => isset( $yoast_meta->open_graph_image_id ) ? $context->get_loader( 'post' )->load_deferred( absint( $yoast_meta->open_graph_image_id ) ) : null,
						'twitterImage'         => isset( $yoast_meta->twitter_image_id ) ? $context->get_loader( 'post' )->load_deferred( absint( $yoast_meta->twitter_image_id ) ) : null, 
						'twitterDescription'   => isset( $yoast_meta->twitter_description ) ? $yoast_meta->twitter_description : null,
						'language'             => isset( $yoast_meta->language ) ? $yoast_meta->language : null,
						'region'               => isset( $yoast_meta->region ) ? $yoast_meta->region : null,
						'breadcrumbTitle'      => isset( $yoast_meta->breadcrumb_title ) ? $yoast_meta->breadcrumb_title : null,
						'fullHead'             => is_string( $yoast_meta->get_head() ) ? $yoast_meta->get_head() : $yoast_meta->get_head()->html,
						'social'               => [
							'facebook'   => wp_gql_seo_format_string(
								get_the_author_meta( 'facebook', $user->userId )
							),
							'twitter'    => wp_gql_seo_format_string(
								get_the_author_meta( 'twitter', $user->userId )
							),
							'instagram'  => wp_gql_seo_format_string(
								get_the_author_meta( 'instagram', $user->userId )
							),
							'linkedIn'   => wp_gql_seo_format_string(
								get_the_author_meta( 'linkedin', $user->userId )
							),
							'mySpace'    => wp_gql_seo_format_string(
								get_the_author_meta( 'myspace', $user->userId )
							),
							'pinterest'  => wp_gql_seo_format_string(
								get_the_author_meta( 'pinterest', $user->userId )
							),
							'youTube'    => wp_gql_seo_format_string(
								get_the_author_meta( 'youtube', $user->userId )
							),
							'soundCloud' => wp_gql_seo_format_string(
								get_the_author_meta( 'soundcloud', $user->userId )
							),
							'wikipedia'  => wp_gql_seo_format_string(
								get_the_author_meta( 'wikipedia', $user->userId )
							),
						],
						'schema'               => [
							'raw'         => json_encode( $schema_array, JSON_UNESCAPED_SLASHES ),
							'pageType'    => isset( $yoast_meta->schema_page_type ) ? $yoast_meta->schema_page_type : [],
							'articleType' => isset( $yoast_meta->indexable ) && isset( $yoast_meta->indexable->schema_article_type ) ? $yoast_meta->indexable->schema_article_type : [],
						],
					];

					return $userSeo;
				},
			]
		);
	}
}
