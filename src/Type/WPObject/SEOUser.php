<?php
/**
 * GraphQL Object - SEOUser
 *
 * @package WPGraphQL\YoastSEO\Type\WPObject
 * @since @todo
 */

namespace WPGraphQL\YoastSEO\Type\WPObject;

use YoastSEO;
use WPGraphQL\AppContext;
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
				'resolve'     => static function ( $user, array $args, AppContext $context ) {
					$robots = YoastSEO()->meta->for_author( $user->userId )->robots;

					$schemaArray = YoastSEO()->meta->for_author( $user->userId )->schema;
					$userSeo     = [
						'title'                => wp_gql_seo_format_string(
							YoastSEO()->meta->for_author( $user->userId )->title
						),
						'metaDesc'             => wp_gql_seo_format_string(
							YoastSEO()->meta->for_author( $user->userId )->description
						),
						'metaRobotsNoindex'    => $robots['index'],
						'metaRobotsNofollow'   => $robots['follow'],
						'canonical'            => YoastSEO()->meta->for_author( $user->userId )
							->canonical,
						'opengraphTitle'       => YoastSEO()->meta->for_author( $user->userId )
							->open_graph_title,
						'opengraphDescription' => YoastSEO()->meta->for_author(
							$user->userId
						)->open_graph_description,
						'opengraphImage'       => $context
							->get_loader( 'post' )
							->load_deferred(
								absint(
									YoastSEO()->meta->for_author( $user->userId )
										->open_graph_image_id
								)
							),
						'twitterImage'         => $context
							->get_loader( 'post' )
							->load_deferred(
								absint(
									YoastSEO()->meta->for_author( $user->userId )
										->twitter_image_id
								)
							),
						'twitterTitle'         => YoastSEO()->meta->for_author( $user->userId )
							->twitter_title,
						'twitterDescription'   => YoastSEO()->meta->for_author(
							$user->userId
						)->twitter_description,
						'language'             => YoastSEO()->meta->for_author( $user->userId )
							->language,
						'region'               => YoastSEO()->meta->for_author( $user->userId )->region,
						'breadcrumbTitle'      => YoastSEO()->meta->for_author( $user->userId )
							->breadcrumb_title,
						'fullHead'             => is_string(
							YoastSEO()
								->meta->for_author( $user->userId )
								->get_head()
						)
							? YoastSEO()
								->meta->for_author( $user->userId )
								->get_head()
							: YoastSEO()
								->meta->for_author( $user->userId )
								->get_head()->html,
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
							'raw'         => json_encode( $schemaArray, JSON_UNESCAPED_SLASHES ),
							'pageType'    => is_array(
								YoastSEO()->meta->for_author( $user->userId )
									->schema_page_type
							)
								? YoastSEO()->meta->for_author( $user->userId )
									->schema_page_type
								: [],
							'articleType' => is_array(
								YoastSEO()->meta->for_author( $user->userId )
									->schema_article_type
							)
								? YoastSEO()->meta->for_author( $user->userId )
									->schema_article_type
								: [],
						],
					];

					return ! empty( $userSeo ) ? $userSeo : [];
				},
			]
		);
	}
}
