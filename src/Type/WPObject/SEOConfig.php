<?php
/**
 * GraphQL Object - SEOConfig
 *
 * @package WPGraphQL\YoastSEO\Type\WPObject
 * @since @todo
 */

namespace WPGraphQL\YoastSEO\Type\WPObject;

use WP_Post_Type;
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
					$wpseo_options = WPSEO_Options::get_all();

					$redirects_obj = class_exists( 'WPSEO_Redirect_Option' ) ? new \WPSEO_Redirect_Option() : false;
					$redirects     = $redirects_obj ? $redirects_obj->get_from_option() : [];

					$user_id = $wpseo_options['company_or_person_user_id'];
					$user    = get_userdata( $user_id );

					$mapped_redirects = function ( $value ) {
						return [
							'origin' => $value['origin'],
							'target' => $value['url'],
							'type'   => $value['type'],
							'format' => $value['format'],
						];
					};

					$contentTypes = self::get_content_types( $post_types );

					return [
						'contentTypes' => $contentTypes,
						'webmaster'    => [
							'baiduVerify'  => isset( $wpseo_options['baiduverify'] ) ? wp_gql_seo_format_string( $wpseo_options['baiduverify'] ) : null,
							'googleVerify' => isset( $wpseo_options['googleverify'] ) ? wp_gql_seo_format_string( $wpseo_options['googleverify'] ) : null,
							'msVerify'     => isset( $wpseo_options['msverify'] ) ? wp_gql_seo_format_string( $wpseo_options['msverify'] ) : null,
							'yandexVerify' => isset( $wpseo_options['yandexverify'] ) ? wp_gql_seo_format_string( $wpseo_options['yandexverify'] ) : null,
						],
						'social'       => [
							'facebook'  => [
								'url'          => isset( $wpseo_options['facebook_site'] ) ? wp_gql_seo_format_string( $wpseo_options['facebook_site'] ) : null,
								'defaultImage' => isset( $wpseo_options['og_default_image_id'] ) ? $context->get_loader( 'post' )->load_deferred( $wpseo_options['og_default_image_id'] ) : null,
							],
							'twitter'   => [
								'username' => isset( $wpseo_options['twitter_site'] ) ? wp_gql_seo_format_string( $wpseo_options['twitter_site'] ) : null,
								'cardType' => isset( $wpseo_options['twitter_card_type'] ) ? wp_gql_seo_format_string( $wpseo_options['twitter_card_type'] ) : null,
							],
							'instagram' => [
								'url' => isset( $wpseo_options['instagram_url'] ) ? wp_gql_seo_format_string( $wpseo_options['instagram_url'] ) : null,
							],
							'linkedIn'  => [
								'url' => isset( $wpseo_options['linkedin_url'] ) ? wp_gql_seo_format_string( $wpseo_options['linkedin_url'] ) : null,
							],
							'mySpace'   => [
								'url' => isset( $wpseo_options['myspace_url'] ) ? wp_gql_seo_format_string( $wpseo_options['myspace_url'] ) : null,
							],
							'pinterest' => [
								'url'     => isset( $wpseo_options['pinterest_url'] ) ? wp_gql_seo_format_string( $wpseo_options['pinterest_url'] ) : null,
								'metaTag' => isset( $wpseo_options['pinterestverify'] ) ? wp_gql_seo_format_string( $wpseo_options['pinterestverify'] ) : null,
							],
							'youTube'   => [
								'url' => isset( $wpseo_options['youtube_url'] ) ? wp_gql_seo_format_string( $wpseo_options['youtube_url'] ) : null,
							],
							'wikipedia' => [
								'url' => isset( $wpseo_options['wikipedia_url'] ) ? wp_gql_seo_format_string( $wpseo_options['wikipedia_url'] ) : null,
							],
						],
						'breadcrumbs'  => [
							'enabled'       => isset( $wpseo_options['breadcrumbs-enable'] ) ? wp_gql_seo_format_string( $wpseo_options['breadcrumbs-enable'] ) : null,
							'boldLast'      => isset( $wpseo_options['breadcrumbs-boldlast'] ) ? wp_gql_seo_format_string( $wpseo_options['breadcrumbs-boldlast'] ) : null,
							'showBlogPage'  => isset( $wpseo_options['breadcrumbs-display-blog-page'] ) ? wp_gql_seo_format_string( $wpseo_options['breadcrumbs-display-blog-page'] ) : null,
							'archivePrefix' => isset( $wpseo_options['breadcrumbs-archiveprefix'] ) ? wp_gql_seo_format_string( $wpseo_options['breadcrumbs-archiveprefix'] ) : null,
							'prefix'        => isset( $wpseo_options['breadcrumbs-prefix'] ) ? wp_gql_seo_format_string( $wpseo_options['breadcrumbs-prefix'] ) : null,
							'notFoundText'  => isset( $wpseo_options['breadcrumbs-404crumb'] ) ? wp_gql_seo_format_string( $wpseo_options['breadcrumbs-404crumb'] ) : null,
							'homeText'      => isset( $wpseo_options['breadcrumbs-home'] ) ? wp_gql_seo_format_string( $wpseo_options['breadcrumbs-home'] ) : null,
							'searchPrefix'  => isset( $wpseo_options['breadcrumbs-searchprefix'] ) ? wp_gql_seo_format_string( $wpseo_options['breadcrumbs-searchprefix'] ) : null,
							'separator'     => isset( $wpseo_options['breadcrumbs-sep'] ) ? wp_gql_seo_format_string( $wpseo_options['breadcrumbs-sep'] ) : null,
						],
						'schema'       => [
							'companyName'       => isset( $wpseo_options['company_name'] ) ? wp_gql_seo_format_string( $wpseo_options['company_name'] ) : null,
							'personName'        => $user instanceof WP_User ? wp_gql_seo_format_string( $user->user_nicename ) : null,
							'companyLogo'       => isset( $wpseo_options['company_logo_id'] ) ? $context->get_loader( 'post' )->load_deferred( absint( $wpseo_options['company_logo_id'] ) ) : null,
							'personLogo'        => isset( $wpseo_options['person_logo_id'] ) ? $context->get_loader( 'post' )->load_deferred( absint( $wpseo_options['person_logo_id'] ) ) : null,
							'logo'              => $context->get_loader( 'post' )->load_deferred(
								'company' === $wpseo_options['company_or_person']
										? absint( $wpseo_options['company_logo_id'] )
										: absint( $wpseo_options['person_logo_id'] )
							),
							'companyOrPerson'   => isset( $wpseo_options['company_or_person'] ) ? wp_gql_seo_format_string( $wpseo_options['company_or_person'] ) : null,
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
						'redirects'    => array_map( $mapped_redirects, $redirects ),
						'openGraph'    => [
							'defaultImage' => isset( $wpseo_options['og_default_image_id'] ) ? $context->get_loader( 'post' )->load_deferred( absint( $wpseo_options['og_default_image_id'] ) ) : null,
							'frontPage'    => [
								'title'       => isset( $wpseo_options['og_frontpage_title'] ) ? wp_gql_seo_format_string( $wpseo_options['og_frontpage_title'] ) : null,
								'description' => isset( $wpseo_options['og_frontpage_desc'] ) ? wp_gql_seo_format_string( $wpseo_options['og_frontpage_desc'] ) : null,
								'image'       => isset( $wpseo_options['og_frontpage_image_id'] ) ? $context->get_loader( 'post' )->load_deferred( absint( $wpseo_options['og_frontpage_image_id'] ) ) : null,
							],
						],
					];
				},
			]
		);
	}

	/**
	 * Builds the resolver data for all SEOContentTypes
	 *
	 * @param string[] $types The post types.
	 */
	private static function get_content_types( $types ) : array {
		$carry = [];
		foreach ( $types as $type ) {
			$post_type_object = get_post_type_object( $type );

			if ( ! $post_type_object instanceof WP_Post_Type || empty( $post_type_object->graphql_single_name ) ) {
				continue;
			}
			$tag = wp_gql_seo_get_field_key(
				$post_type_object->graphql_single_name
			);

			$carry[ $tag ] = $post_type_object;
		}

		return $carry;
	}
}
