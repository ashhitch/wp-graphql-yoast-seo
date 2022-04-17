<?php
/**
 * GraphQL Object - SEOContentType
 *
 * @package WPGraphQL\YoastSEO\Type\WPObject\Config\ContentType
 * @since @todo
 */

namespace WPGraphQL\YoastSEO\Type\WPObject\Config\ContentType;

use WPGraphQL\Model\PostType;
use WPGraphQL\Registry\TypeRegistry;
use WPGraphQL\YoastSEO\Interfaces\Registrable;
use WPGraphQL\YoastSEO\Interfaces\Type;
use WPGraphQL\YoastSEO\Interfaces\TypeWithFields;
use WPGraphQL\YoastSEO\Type\WPObject\ContentType\Archive;
use WPSEO_Options;

/**
 * Class - SEOContentType
 */
class ContentType implements Registrable, Type, TypeWithFields {
	/**
	 * Type registered in WPGraphQL.
	 *
	 * @var string
	 */
	public static string $type = 'SEOContentType';

	/**
	 * {@inheritDoc}
	 */
	public static function register( TypeRegistry $type_registry = null ) : void { // phpcs:ignore VariableAnalysis.CodeAnalysis.VariableAnalysis.UnusedVariable
		register_graphql_object_type(
			static::$type,
			[
				'description' => static::get_description(),
				'fields'      => static::get_fields(),
				'resolve'     => function ( $source ) {
					if ( ! $source instanceof PostType ) {
						return null;
					}

					$type = $source->name;


					$yoast_meta   = YoastSEO()->meta->for_post_type_archive( $type );
					$schema_array = false !== $yoast_meta ? $yoast_meta->schema : null;
				},
			]
		);
	}

	/**
	 * {@inheritDoc}
	 */
	public static function get_description() : string {
		return __( 'The Yoast SEO search appearance content types fields', 'wp-graphql-yoast-seo' );
	}

	/**
	 * {@inheritDoc}
	 */
	public static function get_fields() : array {
		$wpseo_options = WPSEO_Options::get_all();

		return [
			'archive'           => [
				'type'    => Archive::$type,
				'resolve' => function ( $source ) use ( $wpseo_options ) {
					if ( empty( $source->hasArchive ) ) {
						return null;
					}
					$yoast_meta = YoastSEO()->meta->for_post_type_archive( $source->name );

					$default_values = [
						'archiveLink' => get_post_type_archive_link( $source->name ),
						'fullHead'    => is_string( $yoast_meta->get_head() ) ? $yoast_meta->get_head() : $yoast_meta->get_head()->html,
					];

					// Set archive values and merge with existing.
					$post_values = 'post' === $source->name ? [
						'hasArchive'        => true,
						'title'             => $wpseo_options['title-archive-wpseo'] ?? null,
						'metaDesc'          => $wpseo_options['metadesc-archive-wpseo'] ?? null,
						'metaRobotsNoindex' => $wpseo_options['noindex-archive-wpseo'] ?? null,
						'breadcrumbTitle'   => $wpseo_options['bctitle-archive-wpseo'] ?? null,
					] : [
						'hasArchive'        => boolval( $source->hasArchive ),
						'title'             => ! empty( $wpseo_options[ 'title-ptarchive-' . $source->name ] ) ? $wpseo_options[ 'title-ptarchive-' . $source->name ] : null,
						'metaDesc'          => ! empty( $wpseo_options[ 'metadesc-ptarchive-' . $source->name ] ) ? $wpseo_options[ 'metadesc-ptarchive-' . $source->name ] : null,
						'metaRobotsNoindex' => ! empty( $wpseo_options[ 'noindex-ptarchive-' . $source->name ] ) ? boolval( $wpseo_options[ 'noindex-ptarchive-' . $source->name ] ) : false,
						'breadcrumbTitle'   => ! empty( $wpseo_options[ 'bctitle-ptarchive-' . $source->name ] ) ? $wpseo_options[ 'bctitle-ptarchive-' . $source->name ] : null,
					];

					return array_merge( $default_values, $post_values );
				},
			],
			'metaDesc'          => [ 
				'type'    => 'String',
				'resolve' => function ( $source ) use ( $wpseo_options ) {
					return ! empty( $wpseo_options[ 'metadesc-' . $source->name ] ) ? $wpseo_options[ 'metadesc-' . $source->name ] : null;
				},
			],
			'metaRobotsNoindex' => [
				'type'    => 'Boolean',
				'resolve' => function ( $source ) use ( $wpseo_options ) {
					return ! empty( $wpseo_options[ 'noindex-' . $source->name ] );
				},
			],
			'schema'            => [
				'type'    => 'SEOPageInfoSchema',
				'resolve' => function ( $source ) {
					$yoast_meta   = YoastSEO()->meta->for_post_type_archive( $source->name );
					$schema_array = false !== $yoast_meta ? $yoast_meta->schema : null;
					return [
						'raw' => ! empty( $schema_array ) ? wp_json_encode( $schema_array, JSON_UNESCAPED_SLASHES ) : null,
					];
				},
			],
			'schemaType'        => [
				'type'    => 'String',
				'resolve' => function ( $source ) use ( $wpseo_options ) {
					return ! empty( $wpseo_options[ 'schema-page-type-' . $source->name ] ) ? $wpseo_options[ 'schema-page-type-' . $source->name ] : null;
				},
			],
			'title'             => [
				'type'    => 'String',
				'resolve' => function ( $source ) use ( $wpseo_options ) {
					return ! empty( $wpseo_options[ 'title-' . $source->name ] ) ? $wpseo_options[ 'title-' . $source->name ] : null;
				}, 
			],
		];
	}
}
