<?php
/**
 * GraphQL Object - SEOConfig
 *
 * @package WPGraphQL\YoastSEO\Type\WPObject
 * @since @todo
 */

namespace WPGraphQL\YoastSEO\Type\WPObject;

use WPGraphQL\AppContext;
use WPGraphQL\Registry\TypeRegistry;
use WPGraphQL\YoastSEO\Interfaces\Field;
use WPGraphQL\YoastSEO\Interfaces\Registrable;
use WPGraphQL\YoastSEO\Interfaces\Type;
use WPGraphQL\YoastSEO\Interfaces\TypeWithFields;
use WPGraphQL\YoastSEO\Type\WPObject\Config;
use WPSEO_Options;

/**
 * Class - SEOConfig
 */
class SEOConfig implements Registrable, Type, TypeWithFields, Field {
	/**
	 * Type registered in WPGraphQL.
	 *
	 * @var string
	 */
	public static string $type = 'SEOConfig';

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
				'description' => static::get_description(),
				'fields'      => static::get_fields(),
			]
		);

		self::register_field();
	}

	/**
	 * {@inheritDoc}
	 */
	public static function get_description() : string {
		return __( 'The Yoast SEO site level configuration data.', 'wp-graphql-yoast-seo' );
	}

	/**
	 * {@inheritDoc}
	 */
	public static function get_fields() : array {
		// @todo add descriptions.
		return [
			'schema'       => [ 'type' => Config\Schema::$type ],
			'webmaster'    => [ 'type' => Config\Webmaster::$type ],
			'social'       => [ 'type' => Config\Social::$type ],
			'breadcrumbs'  => [ 'type' => Config\Breadcrumbs::$type ],
			'redirects'    => [
				'type' => [ 'list_of' => Config\Redirect::$type ],
			],
			'openGraph'    => [ 'type' => Config\OpenGraph::$type ],
			'contentTypes' => [ 'type' => Config\ContentTypes::$type ],
		];
	}

	/**
	 * {@inheritDoc}
	 */
	public static function register_field() : void {
		$post_types = \WPGraphQL::get_allowed_post_types();

		register_graphql_field(
			'RootQuery',
			static::$fieldname,
			[
				'type'        => static::$type,
				'description' => __( 'Returns seo site data.', 'wp-graphql-yoast-seo' ),
				'resolve'     => static function ( $source, array $args, AppContext $context ) use ( $post_types ) {
					$wpseo_options = WPSEO_Options::get_instance();
					$all           = $wpseo_options->get_all();

					$redirects_obj = class_exists( 'WPSEO_Redirect_Option' ) ? new WPSEO_Redirect_Option() : false;
					$redirects     = $redirects_obj ? $redirects_obj->get_from_option() : [];

					$user_id = $all['company_or_person_user_id'];
					$user    = get_userdata( $user_id );

					$mappedRedirects = function ( $value ) {
						return [
							'origin' => $value['origin'],
							'target' => $value['url'],
							'type'   => $value['type'],
							'format' => $value['format'],
						];
					};

					$contentTypes = self::build_content_type_data( $post_types, $all );

					return [
						'contentTypes' => $contentTypes,
						'webmaster'    => [
							'baiduVerify'  => wp_gql_seo_format_string(
								$all['baiduverify']
							),
							'googleVerify' => wp_gql_seo_format_string(
								$all['googleverify']
							),
							'msVerify'     => wp_gql_seo_format_string( $all['msverify'] ),
							'yandexVerify' => wp_gql_seo_format_string(
								$all['yandexverify']
							),
						],
						'social'       => [
							'facebook'  => [
								'url'          => wp_gql_seo_format_string( $all['facebook_site'] ),
								'defaultImage' => $context
									->get_loader( 'post' )
									->load_deferred( $all['og_default_image_id'] ),
							],
							'twitter'   => [
								'username' => wp_gql_seo_format_string(
									$all['twitter_site']
								),
								'cardType' => wp_gql_seo_format_string(
									$all['twitter_card_type']
								),
							],
							'instagram' => [
								'url' => wp_gql_seo_format_string( $all['instagram_url'] ),
							],
							'linkedIn'  => [
								'url' => wp_gql_seo_format_string( $all['linkedin_url'] ),
							],
							'mySpace'   => [
								'url' => wp_gql_seo_format_string( $all['myspace_url'] ),
							],
							'pinterest' => [
								'url'     => wp_gql_seo_format_string( $all['pinterest_url'] ),
								'metaTag' => wp_gql_seo_format_string(
									$all['pinterestverify']
								),
							],
							'youTube'   => [
								'url' => wp_gql_seo_format_string( $all['youtube_url'] ),
							],
							'wikipedia' => [
								'url' => wp_gql_seo_format_string( $all['wikipedia_url'] ),
							],
						],
						'breadcrumbs'  => [
							'enabled'       => wp_gql_seo_format_string(
								$all['breadcrumbs-enable']
							),
							'boldLast'      => wp_gql_seo_format_string(
								$all['breadcrumbs-boldlast']
							),
							'showBlogPage'  => wp_gql_seo_format_string(
								$all['breadcrumbs-display-blog-page']
							),
							'archivePrefix' => wp_gql_seo_format_string(
								$all['breadcrumbs-archiveprefix']
							),
							'prefix'        => wp_gql_seo_format_string(
								$all['breadcrumbs-prefix']
							),
							'notFoundText'  => wp_gql_seo_format_string(
								$all['breadcrumbs-404crumb']
							),
							'homeText'      => wp_gql_seo_format_string(
								$all['breadcrumbs-home']
							),
							'searchPrefix'  => wp_gql_seo_format_string(
								$all['breadcrumbs-searchprefix']
							),
							'separator'     => wp_gql_seo_format_string(
								$all['breadcrumbs-sep']
							),
						],
						'schema'       => [
							'companyName'       => wp_gql_seo_format_string(
								$all['company_name']
							),
							'personName'        => wp_gql_seo_format_string(
								$user->user_nicename
							),
							'companyLogo'       => $context
								->get_loader( 'post' )
								->load_deferred( absint( $all['company_logo_id'] ) ),
							'personLogo'        => $context
								->get_loader( 'post' )
								->load_deferred( absint( $all['person_logo_id'] ) ),
							'logo'              => $context
								->get_loader( 'post' )
								->load_deferred(
									$all['company_or_person'] === 'company'
										? absint( $all['company_logo_id'] )
										: absint( $all['person_logo_id'] )
								),
							'companyOrPerson'   => wp_gql_seo_format_string(
								$all['company_or_person']
							),
							'siteName'          => wp_gql_seo_format_string(
								YoastSEO()->helpers->site->get_site_name()
							),
							'wordpressSiteName' => wp_gql_seo_format_string(
								get_bloginfo( 'name' )
							),
							'siteUrl'           => wp_gql_seo_format_string(
								apply_filters( 'wp_gql_seo_site_url', get_site_url() )
							),
							'homeUrl'           => wp_gql_seo_format_string(
								apply_filters( 'wp_gql_seo_home_url', get_home_url() )
							),
							'inLanguage'        => wp_gql_seo_format_string(
								get_bloginfo( 'language' )
							),
						],
						'redirects'    => array_map( $mappedRedirects, $redirects ),
						'openGraph'    => [
							'defaultImage' => $context
								->get_loader( 'post' )
								->load_deferred( absint( $all['og_default_image_id'] ) ),
							'frontPage'    => [
								'title'       => wp_gql_seo_format_string(
									$all['og_frontpage_title']
								),
								'description' => wp_gql_seo_format_string(
									$all['og_frontpage_desc']
								),
								'image'       => $context
									->get_loader( 'post' )
									->load_deferred(
										absint( $all['og_frontpage_image_id'] )
									),
							],
						],
					];
				},
			]
		);
	}

	private static function build_content_type_data( $types, $all ) : array {
		$carry = [];
		foreach ( $types as $type ) {
			$post_type_object = get_post_type_object( $type );

			if ( $post_type_object->graphql_single_name ) {
				$tag = wp_gql_seo_get_field_key(
					$post_type_object->graphql_single_name
				);

				$schemaArray = YoastSEO()->meta->for_post_type_archive( $type )
					->schema;

				$carry[ $tag ] = [
					'title'             => ! empty( $all[ 'title-' . $type ] )
					? $all[ 'title-' . $type ]
					: null,
					'metaDesc'          => ! empty( $all[ 'metadesc-' . $type ] )
					? $all[ 'metadesc-' . $type ]
					: null,
					'metaRobotsNoindex' => ! empty( $all[ 'noindex-' . $type ] )
					? boolval( $all[ 'noindex-' . $type ] )
					: false,
					'schemaType'        => ! empty( $all[ 'schema-page-type-' . $type ] )
					? $all[ 'schema-page-type-' . $type ]
					: null,

					'schema'            => [
						'raw' => ! empty( $schemaArray )
							? json_encode( $schemaArray, JSON_UNESCAPED_SLASHES )
							: null,
					],
					'archive'           =>
					$tag == 'post' // Posts are stored like this
						? [
							'hasArchive'        => true,
							'archiveLink'       => get_post_type_archive_link( $type ),
							'title'             => $all['title-archive-wpseo'] ?? null,
							'metaDesc'          => $all['metadesc-archive-wpseo'] ?? null,
							'metaRobotsNoindex' => $all['noindex-archive-wpseo'] ?? null,
							'breadcrumbTitle'   => $all['bctitle-archive-wpseo'] ?? null,
							'metaRobotsNoindex' => boolval(
								$all['noindex-archive-wpseo']
							),
							'fullHead'          => is_string(
								YoastSEO()
									->meta->for_post_type_archive( $type )
									->get_head()
							)
								? YoastSEO()
									->meta->for_post_type_archive( $type )
									->get_head()
								: YoastSEO()
									->meta->for_post_type_archive( $type )
									->get_head()->html,
						]
						: [
							'hasArchive'        => boolval(
								$post_type_object->has_archive
							),
							'archiveLink'       => get_post_type_archive_link( $type ),
							'title'             => ! empty( $all[ 'title-ptarchive-' . $type ] )
								? $all[ 'title-ptarchive-' . $type ]
								: null,
							'metaDesc'          => ! empty(
								$all[ 'metadesc-ptarchive-' . $type ]
							)
								? $all[ 'metadesc-ptarchive-' . $type ]
								: null,
							'metaRobotsNoindex' => ! empty(
								$all[ 'noindex-ptarchive-' . $type ]
							)
								? boolval( $all[ 'noindex-ptarchive-' . $type ] )
								: false,
							'breadcrumbTitle'   => ! empty(
								$all[ 'bctitle-ptarchive-' . $type ]
							)
								? $all[ 'bctitle-ptarchive-' . $type ]
								: null,
							'fullHead'          => is_string(
								YoastSEO()
									->meta->for_post_type_archive( $type )
									->get_head()
							)
								? YoastSEO()
									->meta->for_post_type_archive( $type )
									->get_head()
								: YoastSEO()
									->meta->for_post_type_archive( $type )
									->get_head()->html,
						],
				];
			}
		}
		return $carry;
	}
}
