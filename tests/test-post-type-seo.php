<?php
/**
 * Test PostTypeSEO data
 *
 * @package WP_Graphql_YOAST_SEO
 */

class Test_Post_Type_SEO extends WP_UnitTestCase {

	/**
	 * Test post ID
	 *
	 * @var int
	 */
	protected static $post_id;

	/**
	 * Set up test data before the tests run
	 */
	public static function wpSetUpBeforeClass( $factory ) {
		// Create a test post
		self::$post_id = $factory->post->create( array(
			'post_title'   => 'Test Post',
			'post_content' => 'This is test content',
			'post_status'  => 'publish',
		) );

		// Set Yoast SEO meta data for the test post
		update_post_meta( self::$post_id, '_yoast_wpseo_title', 'SEO Test Title' );
		update_post_meta( self::$post_id, '_yoast_wpseo_metadesc', 'SEO Test Description' );
		update_post_meta( self::$post_id, '_yoast_wpseo_focuskw', 'test keyword' );
		update_post_meta( self::$post_id, '_yoast_wpseo_metakeywords', 'test, keywords' );
		update_post_meta( self::$post_id, '_yoast_wpseo_meta-robots-noindex', '1' );
		update_post_meta( self::$post_id, '_yoast_wpseo_meta-robots-nofollow', '1' );
		update_post_meta( self::$post_id, '_yoast_wpseo_opengraph-title', 'OG Test Title' );
		update_post_meta( self::$post_id, '_yoast_wpseo_opengraph-description', 'OG Test Description' );
		update_post_meta( self::$post_id, '_yoast_wpseo_twitter-title', 'Twitter Test Title' );
		update_post_meta( self::$post_id, '_yoast_wpseo_twitter-description', 'Twitter Test Description' );
		update_post_meta( self::$post_id, '_yoast_wpseo_canonical', 'https://example.com/test-canonical' );
		update_post_meta( self::$post_id, '_yoast_wpseo_is_cornerstone', '1' );
	}

	/**
	 * Test that the post type SEO field resolver returns the expected data
	 */
	public function test_post_type_seo_resolver() {
		$post = get_post( self::$post_id );
		$context = new WPGraphQL\AppContext();
		$seo_data = wp_gql_seo_get_post_type_graphql_fields( $post, array(), $context );

		// Test basic SEO fields
		$this->assertEquals( 'SEO Test Title', $seo_data['title'] );
		$this->assertEquals( 'SEO Test Description', $seo_data['metaDesc'] );
		$this->assertEquals( 'test keyword', $seo_data['focuskw'] );
		$this->assertEquals( 'test, keywords', $seo_data['metaKeywords'] );
		
		// Test OpenGraph fields
		$this->assertEquals( 'OG Test Title', $seo_data['opengraphTitle'] );
		$this->assertEquals( 'OG Test Description', $seo_data['opengraphDescription'] );
		
		// Test Twitter fields
		$this->assertEquals( 'Twitter Test Title', $seo_data['twitterTitle'] );
		$this->assertEquals( 'Twitter Test Description', $seo_data['twitterDescription'] );
		
		// Test other fields
		$this->assertEquals( 'https://example.com/test-canonical', $seo_data['canonical'] );
		$this->assertTrue( $seo_data['cornerstone'] );
	}

	/**
	 * Test the GraphQL schema registration for post types
	 */
	public function test_post_type_seo_schema_registration() {
		$schema = \WPGraphQL::get_schema();
		$type_registry = \WPGraphQL::get_type_registry();
		
		// Check if PostTypeSEO type exists
		$this->assertTrue( $type_registry->has_type( 'PostTypeSEO' ) );
		
		// Check if the ContentNode type has the seo field
		$content_node_type = $type_registry->get_type( 'ContentNode' );
		$this->assertNotNull( $content_node_type );
		
		$content_node_fields = $content_node_type->getFields();
		$this->assertArrayHasKey( 'seo', $content_node_fields );
		
		// Check if the NodeWithTitle type has the seo field
		$node_with_title_type = $type_registry->get_type( 'NodeWithTitle' );
		$this->assertNotNull( $node_with_title_type );
		
		$node_with_title_fields = $node_with_title_type->getFields();
		$this->assertArrayHasKey( 'seo', $node_with_title_fields );
	}

	/**
	 * Test GraphQL query for post SEO data
	 */
	public function test_post_seo_graphql_query() {
		$post_id = self::$post_id;
		$query = '
		query GetPostSEO($id: ID!) {
			post(id: $id, idType: DATABASE_ID) {
				seo {
					title
					metaDesc
					focuskw
					metaKeywords
					metaRobotsNoindex
					metaRobotsNofollow
					opengraphTitle
					opengraphDescription
					twitterTitle
					twitterDescription
					canonical
					cornerstone
				}
			}
		}
		';
		
		$variables = array(
			'id' => $post_id,
		);
		
		$actual = graphql( array(
			'query'     => $query,
			'variables' => $variables,
		) );
		
		// Check if there are no errors
		$this->assertArrayNotHasKey( 'errors', $actual );
		
		// Check if the data is returned as expected
		$this->assertArrayHasKey( 'data', $actual );
		$this->assertArrayHasKey( 'post', $actual['data'] );
		$this->assertArrayHasKey( 'seo', $actual['data']['post'] );
		
		$seo = $actual['data']['post']['seo'];
		
		// Test basic SEO fields
		$this->assertEquals( 'SEO Test Title', $seo['title'] );
		$this->assertEquals( 'SEO Test Description', $seo['metaDesc'] );
		$this->assertEquals( 'test keyword', $seo['focuskw'] );
		$this->assertEquals( 'test, keywords', $seo['metaKeywords'] );
		
		// Test OpenGraph fields
		$this->assertEquals( 'OG Test Title', $seo['opengraphTitle'] );
		$this->assertEquals( 'OG Test Description', $seo['opengraphDescription'] );
		
		// Test Twitter fields
		$this->assertEquals( 'Twitter Test Title', $seo['twitterTitle'] );
		$this->assertEquals( 'Twitter Test Description', $seo['twitterDescription'] );
		
		// Test other fields
		$this->assertEquals( 'https://example.com/test-canonical', $seo['canonical'] );
		$this->assertTrue( $seo['cornerstone'] );
	}
}
