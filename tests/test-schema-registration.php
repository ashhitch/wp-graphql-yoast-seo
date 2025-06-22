<?php
/**
 * Test Schema Registration
 *
 * @package WP_Graphql_YOAST_SEO
 */

class Test_Schema_Registration extends WP_UnitTestCase {

	/**
	 * Test that all SEO types are registered in the schema
	 */
	public function test_seo_types_registration() {
		$schema = \WPGraphQL::get_schema();
		$type_registry = \WPGraphQL::get_type_registry();
		
		// Check core SEO types
		$expected_types = [
			'SEO',
			'PostTypeSEO',
			'TaxonomySEO',
			'SEOUser',
			'SEOConfig',
			'SEOSchema',
			'SEOWebmaster',
			'SEOBreadcrumbs',
			'SEOSocialFacebook',
			'SEOSocialTwitter',
			'SEOSocialInstagram',
			'SEOSocialLinkedIn',
			'SEOSocialMySpace',
			'SEOSocialPinterest',
			'SEOSocialYouTube',
			'SEOSocialWikipedia',
			'SEOSocial',
			'SEOOpenGraph',
			'SEOTwitter',
			'SEOContentType',
			'SEOTaxonomyType',
			'SEOUserSocial',
		];
		
		foreach ( $expected_types as $type ) {
			$this->assertTrue( 
				$type_registry->has_type( $type ), 
				"GraphQL type '$type' is not registered" 
			);
		}
	}

	/**
	 * Test that SEO fields are registered on appropriate interfaces
	 */
	public function test_seo_interface_fields() {
		$schema = \WPGraphQL::get_schema();
		$type_registry = \WPGraphQL::get_type_registry();
		
		// Check ContentNode interface
		$content_node = $type_registry->get_type( 'ContentNode' );
		$this->assertNotNull( $content_node );
		$content_node_fields = $content_node->getFields();
		$this->assertArrayHasKey( 'seo', $content_node_fields );
		
		// Check NodeWithTitle interface if it exists
		if ( $type_registry->has_type( 'NodeWithTitle' ) ) {
			$node_with_title = $type_registry->get_type( 'NodeWithTitle' );
			$node_with_title_fields = $node_with_title->getFields();
			$this->assertArrayHasKey( 'seo', $node_with_title_fields );
		}
	}

	/**
	 * Test that SEO fields are registered on post types
	 */
	public function test_post_type_seo_fields() {
		$schema = \WPGraphQL::get_schema();
		$type_registry = \WPGraphQL::get_type_registry();
		
		// Get allowed post types
		$post_types = \WPGraphQL::get_allowed_post_types();
		
		foreach ( $post_types as $post_type ) {
			$post_type_object = get_post_type_object( $post_type );
			
			if ( ! $post_type_object || ! isset( $post_type_object->graphql_single_name ) ) {
				continue;
			}
			
			$graphql_name = ucfirst( $post_type_object->graphql_single_name );
			
			if ( $type_registry->has_type( $graphql_name ) ) {
				$type = $type_registry->get_type( $graphql_name );
				$fields = $type->getFields();
				$this->assertArrayHasKey( 
					'seo', 
					$fields, 
					"SEO field not registered for post type '$graphql_name'"
				);
			}
		}
	}

	/**
	 * Test that SEO fields are registered on taxonomy types
	 */
	public function test_taxonomy_seo_fields() {
		$schema = \WPGraphQL::get_schema();
		$type_registry = \WPGraphQL::get_type_registry();
		
		// Get allowed taxonomies
		$taxonomies = \WPGraphQL::get_allowed_taxonomies();
		
		foreach ( $taxonomies as $taxonomy ) {
			$taxonomy_object = get_taxonomy( $taxonomy );
			
			if ( ! $taxonomy_object || ! isset( $taxonomy_object->graphql_single_name ) ) {
				continue;
			}
			
			$graphql_name = ucfirst( $taxonomy_object->graphql_single_name );
			
			if ( $type_registry->has_type( $graphql_name ) ) {
				$type = $type_registry->get_type( $graphql_name );
				$fields = $type->getFields();
				$this->assertArrayHasKey( 
					'seo', 
					$fields, 
					"SEO field not registered for taxonomy '$graphql_name'"
				);
			}
		}
	}

	/**
	 * Test that SEO fields are registered on User type
	 */
	public function test_user_seo_fields() {
		$schema = \WPGraphQL::get_schema();
		$type_registry = \WPGraphQL::get_type_registry();
		
		// Check User type
		$user_type = $type_registry->get_type( 'User' );
		$this->assertNotNull( $user_type );
		$user_fields = $user_type->getFields();
		$this->assertArrayHasKey( 'seo', $user_fields );
	}

	/**
	 * Test that SEO field is registered on RootQuery
	 */
	public function test_root_query_seo_field() {
		$schema = \WPGraphQL::get_schema();
		$type_registry = \WPGraphQL::get_type_registry();
		
		// Check RootQuery type
		$root_query = $type_registry->get_type( 'RootQuery' );
		$this->assertNotNull( $root_query );
		$root_query_fields = $root_query->getFields();
		$this->assertArrayHasKey( 'seo', $root_query_fields );
	}

	/**
	 * Test that isPrimary field is registered on taxonomy connection edges
	 */
	public function test_primary_taxonomy_fields() {
		$schema = \WPGraphQL::get_schema();
		$type_registry = \WPGraphQL::get_type_registry();
		
		// Get allowed taxonomies
		$taxonomies = \WPGraphQL::get_allowed_taxonomies();
		
		foreach ( $taxonomies as $taxonomy ) {
			$taxonomy_object = get_taxonomy( $taxonomy );
			
			if ( ! $taxonomy_object || ! isset( $taxonomy_object->graphql_single_name ) ) {
				continue;
			}
			
			// Check for post to taxonomy connection edge
			$edge_type_name = 'PostTo' . ucfirst( $taxonomy_object->graphql_single_name ) . 'ConnectionEdge';
			
			if ( $type_registry->has_type( $edge_type_name ) ) {
				$edge_type = $type_registry->get_type( $edge_type_name );
				$edge_fields = $edge_type->getFields();
				$this->assertArrayHasKey( 
					'isPrimary', 
					$edge_fields, 
					"isPrimary field not registered for edge type '$edge_type_name'"
				);
			}
		}
	}
}
