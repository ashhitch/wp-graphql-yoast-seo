<?php
/**
 * GraphQL Interface - TermNodeWithSEO
 *
 * @package WPGraphQL\YoastSEO\Type\WPInterface
 * @since @todo
 */

namespace WPGraphQL\YoastSEO\Type\WPInterface;

use WP_Term;
use WPGraphQL\AppContext;
use WPGraphQL\YoastSEO\Interfaces\TypeWithFields;
use WPGraphQL\Registry\TypeRegistry;
use WPGraphQL\YoastSEO\Interfaces\Registrable;
use WPGraphQL\YoastSEO\Interfaces\Type;
use WPGraphQL\YoastSEO\Type\WPObject\TaxonomySEO;
use WPSEO_Taxonomy_Meta;

/**
 * Class - TermNodeWithSEO
 */
class TermNodeWithSEO implements Registrable, Type, TypeWithFields {
	/**
	 * Type registered in WPGraphQL.
	 *
	 * @var string
	 */
	public static string $type = 'TermNodeWithSEO';

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
		return __( 'A term node that can have Yoast SEO data assigned to it.', 'wp-graphql-yoast-seo' );
	}

	/**
	 * {@inheritDoc}
	 */
	public static function get_fields() : array {
		return [
			'seo' => [
				'type'    => TaxonomySEO::$type,
				'resolve' => static function( $term, array $args, AppContext $context ) {
					$term_obj = get_term( $term->term_id );
					// Bail early if no term exists.
					if ( ! $term_obj instanceof WP_Term ) {
						return null;
					}

					$meta = WPSEO_Taxonomy_Meta::get_term_meta(
						(int) $term_obj->term_id,
						$term_obj->taxonomy
					);

					$yoast_meta = YoastSEO()->meta->for_term( $term->term_id );

					$robots = isset( $yoast_meta->robots ) ? $yoast_meta->robots : [];

					$schema_array = isset( $yoast_meta->schema ) ? $yoast_meta->schema : [];

					// Get data.
					$seo = [
						'title'                  => isset( $yoast_meta->title ) ? wp_gql_seo_format_string( wp_strip_all_tags( $yoast_meta->title ) ) : null,
						'metaDesc'               => isset( $yoast_meta->description ) ? wp_gql_seo_format_string( $yoast_meta->description ) : null,
						'focuskw'                => isset( $meta['wpseo_focuskw'] ) ? wp_gql_seo_format_string( $meta['wpseo_focuskw'] ) : null,
						'metaKeywords'           => isset( $meta['wpseo_metakeywords'] ) ? wp_gql_seo_format_string( $meta['wpseo_metakeywords'] ) : null,
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
						'opengraphImage'         => isset( $meta['wpseo_opengraph-image-id'] ) ? $context->get_loader( 'post' )->load_deferred( absint( $meta['wpseo_opengraph-image-id'] ) ) : null,
						'twitterCardType'        => isset( $yoast_meta->twitter_card ) ? wp_gql_seo_format_string( $yoast_meta->twitter_card ) : null,
						'twitterTitle'           => isset( $yoast_meta->twitter_title ) ? wp_gql_seo_format_string( $yoast_meta->twitter_title ) : null,
						'twitterDescription'     => isset( $yoast_meta->twitter_description ) ? wp_gql_seo_format_string( $yoast_meta->twitter_description ) : null,
						'twitterImage'           => isset( $meta['wpseo_twitter-image-id'] ) ? $context->get_loader( 'post' )->load_deferred( absint( $meta['wpseo_twitter-image-id'] ) ) : null,
						'canonical'              => isset( $yoast_meta->canonical ) ? wp_gql_seo_format_string( $yoast_meta->canonical ) : null,
						'breadcrumbs'            => isset( $yoast_meta->breadcrumbs ) ? $yoast_meta->breadcrumbs : null,
						'cornerstone'            => ! empty( $yoast_meta->is_cornerstone ),
						'fullHead'               => is_string( $yoast_meta->get_head() ) ? $yoast_meta->get_head() : $yoast_meta->get_head()->html,
						'schema'                 => [
							'raw' => json_encode( $schema_array, JSON_UNESCAPED_SLASHES ),
						],
					];
					wp_reset_query();

					return $seo;
				},
			],
		];
	}
}
