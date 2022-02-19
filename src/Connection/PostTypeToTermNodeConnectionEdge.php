<?php
/**
 * Registers fields on the {PostType}To{TermNode}ConnectionEdge.
 *
 * @package WPGraphQL\YoastSEO\Connection
 * @since @todo
 */

namespace WPGraphQL\YoastSEO\Connection;

use WPGraphQL\AppContext;
use WPGraphQL\Registry\TypeRegistry;
use WPGraphQL\YoastSEO\Interfaces\Registrable;
use WPSEO_Primary_Term;

/**
 * Class - PostTypeToTermNodeConnectionEdge
 */
class PostTypeToTermNodeConnectionEdge implements Registrable {
	/**
	 * {@inheritDoc}
	 */
	public static function register( TypeRegistry $type_registry = null ) : void {
		$post_types = \WPGraphQL::get_allowed_post_types();

		// If WooCommerce installed then add these post types and taxonomies
		if ( class_exists( '\WooCommerce' ) ) {
			array_push( $post_types, 'product' );
		}

		if ( empty( $post_types ) ) {
			return;
		}

		// Loop through each post type and associated taxonomy, to register the field to individual connection edges.
		foreach ( $post_types as $post_type ) {
			$post_type_object = get_post_type_object( $post_type );
			// Skip if not a GraphQL type.
			if ( ! isset( $post_type_object->graphql_single_name ) ) {
				continue;
			}

			$post_name_key = wp_gql_seo_get_field_key( $post_type_object->graphql_single_name );

			$taxonomies = get_object_taxonomies( $post_type, 'objects' );

			foreach ( $taxonomies as $tax ) {
				// Skip if not a hierarchical taxonomy registered to GraphQL.
				if ( ! isset( $tax->hierarchical ) && ! isset( $tax->graphql_single_name ) ) {
					continue;
				}

				$name = sprintf(
					'%sTo%sConnectionEdge',
					ucfirst( $post_name_key ),
					ucfirst( $tax->graphql_single_name )
				);

				register_graphql_field(
					$name,
					'isPrimary',
					[
						'type'        => 'Boolean',
						// translators: Taxonomy name.
						'description' => sprintf( __( 'The Yoast SEO primary %s.', 'wp-graphql-yoast-seo' ), $tax->labels->name ),
						'resolve'     => static function( $item, array $args, AppContext $context ) use ( $tax ) {
							$post_id = $item['source']->ID;
							$term_id = $item['node']->term_id;

							$wpseo_primary_term = new WPSEO_Primary_Term( $tax->name, $post_id );
							$primary_tax_id     = $wpseo_primary_term->get_primary_term();

							return $primary_tax_id === $term_id;
						},
					]
				);
			}
		}
	}

}
