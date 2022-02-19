<?php
/**
 * GraphQL Interface - TermNodeWithSEO
 *
 * @package WPGraphQL\YoastSEO\Type\WPInterface
 * @since @todo
 */

namespace WPGraphQL\YoastSEO\Type\WPInterface;

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

		// self::register_field();
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

					$meta   = WPSEO_Taxonomy_Meta::get_term_meta(
						(int) $term_obj->term_id,
						$term_obj->taxonomy
					);
					$robots = YoastSEO()->meta->for_term( $term->term_id )->robots;

					$schemaArray = YoastSEO()->meta->for_term( $term->term_id )
					->schema;

					// Get data
					$seo = [
						'title'                  => wp_gql_seo_format_string(
							html_entity_decode(
								wp_strip_all_tags(
									YoastSEO()->meta->for_term( $term->term_id )
										->title
								)
							)
						),
						'metaDesc'               => wp_gql_seo_format_string(
							YoastSEO()->meta->for_term( $term->term_id )
								->description
						),
						'focuskw'                => isset( $meta['wpseo_focuskw'] )
							? wp_gql_seo_format_string( $meta['wpseo_focuskw'] )
							: $meta['wpseo_focuskw'],
						'metaKeywords'           => isset( $meta['wpseo_metakeywords'] )
							? wp_gql_seo_format_string(
								$meta['wpseo_metakeywords']
							)
							: null,
						'metaRobotsNoindex'      => $robots['index'],
						'metaRobotsNofollow'     => $robots['follow'],
						'opengraphTitle'         => wp_gql_seo_format_string(
							YoastSEO()->meta->for_term( $term->term_id )
								->open_graph_title
						),
						'opengraphUrl'           => wp_gql_seo_format_string(
							YoastSEO()->meta->for_term( $term->term_id )
								->open_graph_url
						),
						'opengraphSiteName'      => wp_gql_seo_format_string(
							YoastSEO()->meta->for_term( $term->term_id )
								->open_graph_site_name
						),
						'opengraphType'          => wp_gql_seo_format_string(
							YoastSEO()->meta->for_term( $term->term_id )
								->open_graph_type
						),
						'opengraphAuthor'        => wp_gql_seo_format_string(
							YoastSEO()->meta->for_term( $term->term_id )
								->open_graph_article_author
						),
						'opengraphPublisher'     => wp_gql_seo_format_string(
							YoastSEO()->meta->for_term( $term->term_id )
								->open_graph_article_publisher
						),
						'opengraphPublishedTime' => wp_gql_seo_format_string(
							YoastSEO()->meta->for_term( $term->term_id )
								->open_graph_article_published_time
						),
						'opengraphModifiedTime'  => wp_gql_seo_format_string(
							YoastSEO()->meta->for_term( $term->term_id )
								->open_graph_article_modified_time
						),
						'opengraphDescription'   => wp_gql_seo_format_string(
							YoastSEO()->meta->for_term( $term->term_id )
								->open_graph_description
						),
						'opengraphImage'         => $context
							->get_loader( 'post' )
							->load_deferred(
								absint( $meta['wpseo_opengraph-image-id'] )
							),
						'twitterCardType'        => wp_gql_seo_format_string(
							YoastSEO()->meta->for_term( $term->term_id )
								->twitter_card
						),
						'twitterTitle'           => wp_gql_seo_format_string(
							YoastSEO()->meta->for_term( $term->term_id )
								->twitter_title
						),
						'twitterDescription'     => wp_gql_seo_format_string(
							YoastSEO()->meta->for_term( $term->term_id )
								->twitter_description
						),
						'twitterImage'           => $context
							->get_loader( 'post' )
							->load_deferred(
								absint( $meta['wpseo_twitter-image-id'] )
							),
						'canonical'              => isset(
							YoastSEO()->meta->for_term( $term->term_id )->canonical
						)
							? wp_gql_seo_format_string(
								YoastSEO()->meta->for_term( $term->term_id )
									->canonical
							)
							: null,
						'breadcrumbs'            => YoastSEO()->meta->for_term(
							$term->term_id
						)->breadcrumbs,
						'cornerstone'            => boolval(
							YoastSEO()->meta->for_term( $term->term_id )
								->is_cornerstone
						),
						'fullHead'               => is_string(
							YoastSEO()
								->meta->for_term( $term->term_id )
								->get_head()
						)
							? YoastSEO()
								->meta->for_term( $term->term_id )
								->get_head()
							: YoastSEO()
								->meta->for_term( $term->term_id )
								->get_head()->html,
						'schema'                 => [
							'raw' => json_encode(
								$schemaArray,
								JSON_UNESCAPED_SLASHES
							),
						],
					];
					wp_reset_query();

					return ! empty( $seo ) ? $seo : null;
				},
			],
		];
	}
}
