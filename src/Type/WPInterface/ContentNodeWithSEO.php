<?php
/**
 * GraphQL Interface - NodeWithSEO
 *
 * @package WPGraphQL\YoastSEO\Type\WPInterface
 * @since @todo
 */

namespace WPGraphQL\YoastSEO\Type\WPInterface;

use WPGraphQL\AppContext;
use WPGraphQL\Registry\TypeRegistry;
use WPGraphQL\YoastSEO\Interfaces\Registrable;
use WPGraphQL\YoastSEO\Interfaces\Type;
use WPGraphQL\YoastSEO\Type\WPObject\PostTypeSEO;
use WPGraphQL\YoastSEO\Type\WPObject\SEOBaseFields;
use WPGraphQL\YoastSEO\Type\WPObject\SEOPostTypeBreadcrumbs;

/**
 * Class - ContentNodeWithSEO
 */
class ContentNodeWithSEO implements Registrable, Type {
	/**
	 * Type registered in WPGraphQL.
	 *
	 * @var string
	 */
	public static string $type = 'ContentNodeWithSeo';

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
		return __( 'A node that can have Yoast SEO data assigned to it.', 'wp-graphql-yoast-seo' );
	}

	/**
	 * {@inheritDoc}
	 */
	public static function get_fields() : array {
		return [
			'seo' => [
				'type'    => PostTypeSEO::$type,
				'resolve' => static function( $post, array $args, AppContext $context ) {
					// Base array.
					$seo = [];

					$map = [
						'@id'      => 'id',
						'@type'    => 'type',
						'@graph'   => 'graph',
						'@context' => 'context',
					];

					$schemaArray = YoastSEO()->meta->for_post( $post->ID )
						->schema;

					// https://developer.yoast.com/blog/yoast-seo-14-0-using-yoast-seo-surfaces/
					$robots = YoastSEO()->meta->for_post( $post->ID )
						->robots;

					// Get data
					$seo = [
						'title'                  => wp_gql_seo_format_string(
							YoastSEO()->meta->for_post( $post->ID )->title
						),
						'metaDesc'               => wp_gql_seo_format_string(
							YoastSEO()->meta->for_post( $post->ID )
								->description
						),
						'focuskw'                => wp_gql_seo_format_string(
							get_post_meta(
								$post->ID,
								'_yoast_wpseo_focuskw',
								true
							)
						),
						'metaKeywords'           => wp_gql_seo_format_string(
							get_post_meta(
								$post->ID,
								'_yoast_wpseo_metakeywords',
								true
							)
						),
						'metaRobotsNoindex'      => $robots['index'],
						'metaRobotsNofollow'     => $robots['follow'],
						'opengraphTitle'         => wp_gql_seo_format_string(
							YoastSEO()->meta->for_post( $post->ID )
								->open_graph_title
						),
						'opengraphUrl'           => wp_gql_seo_format_string(
							YoastSEO()->meta->for_post( $post->ID )
								->open_graph_url
						),
						'opengraphSiteName'      => wp_gql_seo_format_string(
							YoastSEO()->meta->for_post( $post->ID )
								->open_graph_site_name
						),
						'opengraphType'          => wp_gql_seo_format_string(
							YoastSEO()->meta->for_post( $post->ID )
								->open_graph_type
						),
						'opengraphAuthor'        => wp_gql_seo_format_string(
							YoastSEO()->meta->for_post( $post->ID )
								->open_graph_article_author
						),
						'opengraphPublisher'     => wp_gql_seo_format_string(
							YoastSEO()->meta->for_post( $post->ID )
								->open_graph_article_publisher
						),
						'opengraphPublishedTime' => wp_gql_seo_format_string(
							YoastSEO()->meta->for_post( $post->ID )
								->open_graph_article_published_time
						),
						'opengraphModifiedTime'  => wp_gql_seo_format_string(
							YoastSEO()->meta->for_post( $post->ID )
								->open_graph_article_modified_time
						),
						'opengraphDescription'   => wp_gql_seo_format_string(
							YoastSEO()->meta->for_post( $post->ID )
								->open_graph_description
						),
						'opengraphImage'         => function () use (
							$post,
							$context
						) {
							$id = wp_gql_seo_get_og_image(
								YoastSEO()->meta->for_post( $post->ID )
									->open_graph_images
							);

							return $context
								->get_loader( 'post' )
								->load_deferred( absint( $id ) );
						},
						'twitterCardType'        => wp_gql_seo_format_string(
							YoastSEO()->meta->for_post( $post->ID )
								->twitter_card
						),
						'twitterTitle'           => wp_gql_seo_format_string(
							YoastSEO()->meta->for_post( $post->ID )
								->twitter_title
						),
						'twitterDescription'     => wp_gql_seo_format_string(
							YoastSEO()->meta->for_post( $post->ID )
								->twitter_description
						),
						'twitterImage'           => function () use (
							$post,
							$context
						) {
							$id = wpcom_vip_attachment_url_to_postid(
								YoastSEO()->meta->for_post( $post->ID )
									->twitter_image
							);

							return $context
								->get_loader( 'post' )
								->load_deferred( absint( $id ) );
						},
						'canonical'              => wp_gql_seo_format_string(
							YoastSEO()->meta->for_post( $post->ID )
								->canonical
						),
						'readingTime'            => floatval(
							YoastSEO()->meta->for_post( $post->ID )
								->estimated_reading_time_minutes
						),
						'breadcrumbs'            => YoastSEO()->meta->for_post(
							$post->ID
						)->breadcrumbs,
						'cornerstone'            => boolval(
							YoastSEO()->meta->for_post( $post->ID )
								->indexable->is_cornerstone
						),
						'fullHead'               => is_string(
							YoastSEO()
								->meta->for_post( $post->ID )
								->get_head()
						)
							? YoastSEO()
								->meta->for_post( $post->ID )
								->get_head()
							: YoastSEO()
								->meta->for_post( $post->ID )
								->get_head()->html,
						'schema'                 => [
							'pageType'    => is_array(
								YoastSEO()->meta->for_post( $post->ID )
									->schema_page_type
							)
								? YoastSEO()->meta->for_post( $post->ID )
									->schema_page_type
								: [],
							'articleType' => is_array(
								YoastSEO()->meta->for_post( $post->ID )
									->schema_article_type
							)
								? YoastSEO()->meta->for_post( $post->ID )
									->schema_article_type
								: [],
							'raw'         => json_encode(
								$schemaArray,
								JSON_UNESCAPED_SLASHES
							),
						],
					];

					return ! empty( $seo ) ? $seo : null;
				},
			],
		];
	}
}
