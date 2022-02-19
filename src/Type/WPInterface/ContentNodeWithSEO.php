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
use function YoastSEO;

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
					$map = [
						'@id'      => 'id',
						'@type'    => 'type',
						'@graph'   => 'graph',
						'@context' => 'context',
					];

					$yoast_meta = YoastSEO()->meta->for_post( $post->ID );


					$schema_array = isset( $yoast_meta->schema ) ? $yoast_meta->schema : [];

					// @see https://developer.yoast.com/blog/yoast-seo-14-0-using-yoast-seo-surfaces/
					$robots = isset( $yoast_meta->robots ) ? $yoast_meta->robots : [];

					// Get data.
					$seo = [
						'title'                  => isset( $yoast_meta->title ) ? wp_gql_seo_format_string( $yoast_meta->title ) : null,
						'metaDesc'               => isset( $yoast_meta->description ) ? wp_gql_seo_format_string( $yoast_meta->description ) : null,
						'focuskw'                => wp_gql_seo_format_string( get_post_meta( $post->ID, '_yoast_wpseo_focuskw', true ) ) ?: null,
						'metaKeywords'           => wp_gql_seo_format_string( get_post_meta( $post->ID, '_yoast_wpseo_metakeywords', true ) ) ?: null,
						'metaRobotsNoindex'      => $robots['index'] ?? null,
						'metaRobotsNofollow'     => $robots['follow'] ?? null,
						'opengraphTitle'         => isset( $yoast_meta->open_graph_title ) ? wp_gql_seo_format_string( $yoast_meta->open_graph_title ) : null,
						'opengraphUrl'           => isset( $yoast_meta->open_graph_url ) ? wp_gql_seo_format_string( $yoast_meta->open_graph_url ) : null,
						'opengraphSiteName'      => isset( $yoast_meta->open_graph_site_name ) ? wp_gql_seo_format_string( $yoast_meta->open_graph_site_name ) : null,
						'opengraphType'          => isset( $yoast_meta->open_graph_type ) ? wp_gql_seo_format_string( $yoast_meta->open_graph_type ) : null,
						'opengraphAuthor'        => isset( $yoast_meta->open_graph_article_author ) ? wp_gql_seo_format_string( $yoast_meta->open_graph_article_author ) : null,
						'opengraphPublisher'     => isset( $yoast_meta->open_graph_article_publisher ) ? wp_gql_seo_format_string( $yoast_meta->open_graph_article_publisher ) : null,
						'opengraphPublishedTime' => isset( $yoast_meta->open_graph_article_published_time ) ? wp_gql_seo_format_string( $yoast_meta->open_graph_article_published_time ) : null,
						'opengraphModifiedTime'  => isset( $yoast_meta->open_graph_article_modified_time ) ? wp_gql_seo_format_string( $yoast_meta->open_graph_article_modified_time ) : null,
						'opengraphDescription'   => isset( $yoast_meta->open_graph_description ) ? wp_gql_seo_format_string( $yoast_meta->open_graph_description ) : null,
						'opengraphImage'         => static function () use ( $context, $yoast_meta ) {
							$id = ! empty( $yoast_meta->open_graph_images ) ? wp_gql_seo_get_og_image( $yoast_meta->open_graph_images ) : null;

							return null !== $id ? $context->get_loader( 'post' )->load_deferred( absint( $id ) ) : null;
						},
						'twitterCardType'        => isset( $yoast_meta->twitter_card ) ? wp_gql_seo_format_string( $yoast_meta->twitter_card ) : null,
						'twitterTitle'           => isset( $yoast_meta->twitter_title ) ? wp_gql_seo_format_string( $yoast_meta->twitter_title ) : null,
						'twitterDescription'     => isset( $yoast_meta->twitter_description ) ? wp_gql_seo_format_string( $yoast_meta->twitter_description ) : null,
						'twitterImage'           => static function () use ( $context, $yoast_meta ) {
							$id = isset( $yoast_meta->twitter_image ) ? wpcom_vip_attachment_url_to_postid( $yoast_meta->twitter_image ) : null;

							return null !== $id ? $context->get_loader( 'post' )->load_deferred( absint( $id ) ) : null;
						},
						'canonical'              => isset( $yoast_meta->canonical ) ? wp_gql_seo_format_string( $yoast_meta->canonical ) : null,
						'readingTime'            => isset( $yoast_meta->estimated_reading_time_minutes ) ? floatval( $yoast_meta->estimated_reading_time_minutes ) : null,
						'breadcrumbs'            => isset( $yoast_meta->breadcrumbs ) ? $yoast_meta->breadcrumbs : null,
						'cornerstone'            => isset( $yoast_meta->indexable ) && isset( $yoast_meta->indexable->is_cornerstone ) ? boolval( $yoast_meta->indexable->is_cornerstone ) : null,
						'fullHead'               => is_string( $yoast_meta->get_head() ) ? $yoast_meta->get_head() : $yoast_meta->get_head()->html,
						'schema'                 => [
							'pageType'    => isset( $yoast_meta->indexable ) && isset( $yoast_meta->indexable->schema_page_type ) ? $yoast_meta->schema_page_type : [],
							'articleType' => isset( $yoast_meta->indexable ) && isset( $yoast_meta->indexable->schema_article_type ) ? $yoast_meta->schema_article_type : [],
							'raw'         => json_encode( $schema_array, JSON_UNESCAPED_SLASHES ),
						],
					];

					return $seo;
				},
			],
		];
	}
}
