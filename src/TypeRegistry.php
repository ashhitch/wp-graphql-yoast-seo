<?php
/**
 * Registers Yoast SEO types to the schema.
 *
 * @package \WPGraphQL\YoastSEO
 * @since   @todo
 */

namespace WPGraphQL\YoastSEO;

use WPGraphQL\Registry\TypeRegistry as GraphQLRegistry;
use WPGraphQL\YoastSEO\Type\Enum;
use WPGraphQL\YoastSEO\Type\WPInterface;
use WPGraphQL\YoastSEO\Type\WPObject;

/**
 * Class - TypeRegistry
 */
class TypeRegistry {
	/**
	 * Registers the types, connections, unions, and mutations to GraphQL schema
	 *
	 * @param GraphQLRegistry $type_registry Instance of the WPGraphQL TypeRegistry.
	 */
	public static function init( GraphQLRegistry $type_registry ) : void {
		/**
		 * Fires before all types have been registered.
		 *
		 * @param GraphQLRegistry $type_registry Instance of the WPGraphQL TypeRegistry.
		 */
		do_action( 'graphql_seo_before_register_types', $type_registry );

		self::register_enums( $type_registry );
		self::register_interfaces( $type_registry );
		self::register_objects( $type_registry );
		self::register_fields( $type_registry );
		self::register_connections( $type_registry );

		/**
		 * Fires after all types have been registered.
		 *
		 * @param GraphQLRegistry $type_registry Instance of the WPGraphQL TypeRegistry.
		 */
		do_action( 'graphql_seo_after_register_types', $type_registry );
	}
	
	/**
	 * Fires hooks responsible for registering Enum types.
	 *
	 * @param GraphQLRegistry $type_registry .
	 */
	public static function register_enums( GraphQLRegistry $type_registry ) : void {
		Enum\SEOCardTypeEnum::register();

			/**
		 * Fires after enums have been registered.
		 *
		 * @param GraphQLRegistry $type_registry Instance of the WPGraphQL TypeRegistry.
		 */
		do_action( 'graphql_seo_after_register_enums', $type_registry );
	}

	/**
	 * Fires hooks responsible for registering Interface types.
	 *
	 * @param GraphQLRegistry $type_registry .
	 */
	public static function register_interfaces( GraphQLRegistry $type_registry ) : void {
		WPInterface\SEOBaseFields::register( $type_registry );
		WPInterface\ContentNodeWithSEO::register( $type_registry );
		WPInterface\ContentTypeWithSEO::register( $type_registry );
		WPInterface\TermNodeWithSEO::register( $type_registry );

		/**
		 * Fires after interfaces have been registered.
		 *
		 * @param GraphQLRegistry $type_registry Instance of the WPGraphQL TypeRegistry.
		 */
		do_action( 'graphql_seo_after_register_interfaces', $type_registry );
	}

	/**
	 * Fires hooks responsible for registering Object types.
	 *
	 * @param GraphQLRegistry $type_registry .
	 */
	public static function register_objects( GraphQLRegistry $type_registry ) : void {
		WPObject\Config\ContentType\ContentType::register();
		WPObject\Config\OpenGraph\FrontPage::register();
		WPObject\Config\Social\Facebook::register();
		WPObject\Config\Social\Instagram::register();
		WPObject\Config\Social\LinkedIn::register();
		WPObject\Config\Social\MySpace::register();
		WPObject\Config\Social\Pinterest::register();
		WPObject\Config\Social\Twitter::register();
		WPObject\Config\Social\Wikipedia::register();
		WPObject\Config\Social\Youtube::register();
		WPObject\Config\Breadcrumbs::register();
		WPObject\Config\ContentTypes::register();
		WPObject\Config\OpenGraph::register();
		WPObject\Config\Redirect::register();
		WPObject\Config\Schema::register();
		WPObject\Config\Social::register();
		WPObject\Config\Webmaster::register();

		WPObject\ContentType\Archive::register();
		WPObject\PageInfo\Schema::register();
		WPObject\PostType\Schema::register();
		WPObject\Taxonomy\Schema::register();
		WPObject\User\Schema::register();
		WPObject\User\Social::register();

		WPObject\PostTypeSEO::register();
		WPObject\SEOConfig::register();
		WPObject\SEOPostTypeBreadcrumbs::register();
		WPObject\SEOPostTypePageInfo::register();
		WPObject\SEOUser::register();
		WPObject\TaxonomySEO::register();

		/**
		 * Fires after objects have been registered.
		 *
		 * @param GraphQLRegistry $type_registry Instance of the WPGraphQL TypeRegistry.
		 */
		do_action( 'graphql_seo_after_register_objects', $type_registry );
	}

	/**
	 * Fires hooks responsible for registering fields to GraphQL types.
	 *
	 * @param GraphQLRegistry $type_registry .
	 */
	public static function register_fields( GraphQLRegistry $type_registry ) : void {
		/**
		 * Fires after fields have been registered.
		 *
		 * @param GraphQLRegistry $type_registry Instance of the WPGraphQL TypeRegistry.
		 */
		do_action( 'graphql_seo_after_register_fields', $type_registry );
	}

	/**
	 * Fires hooks responsible for registering fields to GraphQL types.
	 *
	 * @param GraphQLRegistry $type_registry .
	 */
	public static function register_connections( GraphQLRegistry $type_registry ) : void {
		Connection\PostTypeToTermNodeConnectionEdge::register( $type_registry );
		/**
		 * Fires after connections have been registered.
		 *
		 * @param GraphQLRegistry $type_registry Instance of the WPGraphQL TypeRegistry.
		 */
		do_action( 'graphql_seo_after_register_connections', $type_registry );
	}

}
