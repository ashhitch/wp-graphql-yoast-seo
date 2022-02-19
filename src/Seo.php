<?php
/**
 * Initializes a singleton instance of WPGraphQL\YoastSEO
 *
 * @package WPGraphQL\YoastSEO
 * @since @todo
 */

namespace WPGraphQL\YoastSEO;

if ( ! class_exists( 'WPGraphQL\YoastSEO\Seo' ) ) :

	/**
	 * Class - Seo
	 */
	final class Seo {
		/**
		 * Stores the instance of the WPGraphQL\YoastSEO class
		 *
		 * @var Seo The one true WPGraphQL\YoastSEO
		 * @access private
		 */
		private static $instance;

		/**
		 * YoastSEO constructor.
		 */
		public static function instance() : self {
			if ( ! ( is_a( self::$instance, __CLASS__ ) ) ) {
				if ( ! function_exists( 'is_plugin_active' ) ) {
					require_once ABSPATH . 'wp-admin/includes/plugin.php';
				}
				self::$instance = new self();
				self::$instance->includes();
				self::$instance->setup();
			}

			/**
			 * Fire off init action
			 *
			 * @param Seo $instance The instance of the Seo class
			 */
			do_action( 'graphql_seo_init', self::$instance );

			/**
			 * Return the Seo Instance
			 */
			return self::$instance;
		}
		/**
		 * Throw error on object clone.
		 * The whole idea of the singleton design pattern is that there is a single object
		 * therefore, we don't want the object to be cloned.
		 *
		 * @since  @todo
		 * @access public
		 * @return void
		 */
		public function __clone() {
			// Cloning instances of the class is forbidden.
			_doing_it_wrong( __FUNCTION__, esc_html__( 'WPGraphQL/YoastSEO/Seo class should not be cloned.', 'wp-graphql-yoast-seo' ), '@todo' );
		}
		
		/**
		 * Disable unserializing of the class.
		 *
		 * @since  @todo
		 * @access protected
		 * @return void
		 */
		public function __wakeup() : void {
			// De-serializing instances of the class is forbidden.
			_doing_it_wrong( __FUNCTION__, esc_html__( 'De-serializing instances of the WPGraphQL/YoastSEO/Seo class is not allowed', 'wp-graphql-yoast-seo' ), '@todo' );
		}

		/**
		 * Include required files.
		 * Uses composer's autoload
		 *
		 * @access private
		 * @since  @todo
		 */
		private function includes() : void {
			/**
			 * Autoload Required Classes
			 */
			if ( defined( 'WPGRAPHQL_SEO_AUTOLOAD' ) && false !== WPGRAPHQL_SEO_AUTOLOAD && defined( 'WPGRAPHQL_SEO_PLUGIN_DIR' ) ) {
				require_once WPGRAPHQL_SEO_PLUGIN_DIR . 'vendor/autoload.php';
			}
		}

		/**
		 * Sets up the schema.
		 */
		private function setup() : void {
			CoreSchemaFilters::register_hooks();
			add_action( get_graphql_register_action(), [ TypeRegistry::class, 'init' ] );
		}
	}
endif;
