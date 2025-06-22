<?php
/**
 * Test UserSEO data
 *
 * @package WP_Graphql_YOAST_SEO
 */

class Test_User_SEO extends WP_UnitTestCase {

	/**
	 * Test user ID
	 *
	 * @var int
	 */
	protected static $user_id;

	/**
	 * Set up test data before the tests run
	 */
	public static function wpSetUpBeforeClass( $factory ) {
		// Create a test user
		self::$user_id = $factory->user->create( array(
			'role'         => 'editor',
			'user_login'   => 'testuser',
			'user_email'   => 'test@example.com',
			'display_name' => 'Test User',
		) );

		// Set Yoast SEO meta data for the test user
		update_user_meta( self::$user_id, 'wpseo_title', 'User SEO Title' );
		update_user_meta( self::$user_id, 'wpseo_desc', 'User SEO Description' );
		update_user_meta( self::$user_id, 'wpseo_metakey', 'user, keywords' );
		update_user_meta( self::$user_id, 'wpseo_noindex_author', '1' );
		update_user_meta( self::$user_id, 'wpseo_opengraph-title', 'User OG Title' );
		update_user_meta( self::$user_id, 'wpseo_opengraph-description', 'User OG Description' );
		update_user_meta( self::$user_id, 'wpseo_twitter-title', 'User Twitter Title' );
		update_user_meta( self::$user_id, 'wpseo_twitter-description', 'User Twitter Description' );

		// Set social profiles
		update_user_meta( self::$user_id, 'wpseo_facebook', 'https://facebook.com/testuser' );
		update_user_meta( self::$user_id, 'wpseo_twitter', 'https://twitter.com/testuser' );
		update_user_meta( self::$user_id, 'wpseo_instagram', 'https://instagram.com/testuser' );
		update_user_meta( self::$user_id, 'wpseo_linkedin', 'https://linkedin.com/in/testuser' );
	}

	/**
	 * Test the GraphQL schema registration for users
	 */
	public function test_user_seo_schema_registration() {
		$schema = \WPGraphQL::get_schema();
		$type_registry = \WPGraphQL::get_type_registry();
		
		// Check if SEOUser type exists
		$this->assertTrue( $type_registry->has_type( 'SEOUser' ) );
		
		// Check if the User type has the seo field
		$user_type = $type_registry->get_type( 'User' );
		$this->assertNotNull( $user_type );
		
		$user_fields = $user_type->getFields();
		$this->assertArrayHasKey( 'seo', $user_fields );
		
		// Check if SEOUserSocial type exists
		$this->assertTrue( $type_registry->has_type( 'SEOUserSocial' ) );
	}

	/**
	 * Test GraphQL query for user SEO data
	 */
	public function test_user_seo_graphql_query() {
		$user_id = self::$user_id;
		$query = '
		query GetUserSEO($id: ID!) {
			user(id: $id, idType: DATABASE_ID) {
				seo {
					title
					metaDesc
					metaRobotsNoindex
					metaRobotsNofollow
					opengraphTitle
					opengraphDescription
					twitterTitle
					twitterDescription
					social {
						facebook
						twitter
						instagram
						linkedIn
					}
				}
			}
		}
		';
		
		$variables = array(
			'id' => $user_id,
		);
		
		$actual = graphql( array(
			'query'     => $query,
			'variables' => $variables,
		) );
		
		// Check if there are no errors
		$this->assertArrayNotHasKey( 'errors', $actual );
		
		// Check if the data is returned as expected
		$this->assertArrayHasKey( 'data', $actual );
		$this->assertArrayHasKey( 'user', $actual['data'] );
		$this->assertArrayHasKey( 'seo', $actual['data']['user'] );
		
		$seo = $actual['data']['user']['seo'];
		
		// Test basic SEO fields
		$this->assertEquals( 'User SEO Title', $seo['title'] );
		$this->assertEquals( 'User SEO Description', $seo['metaDesc'] );
		
		// Test OpenGraph fields
		$this->assertEquals( 'User OG Title', $seo['opengraphTitle'] );
		$this->assertEquals( 'User OG Description', $seo['opengraphDescription'] );
		
		// Test Twitter fields
		$this->assertEquals( 'User Twitter Title', $seo['twitterTitle'] );
		$this->assertEquals( 'User Twitter Description', $seo['twitterDescription'] );
		
		// Test social fields
		$this->assertArrayHasKey( 'social', $seo );
		$this->assertEquals( 'https://facebook.com/testuser', $seo['social']['facebook'] );
		$this->assertEquals( 'https://twitter.com/testuser', $seo['social']['twitter'] );
		$this->assertEquals( 'https://instagram.com/testuser', $seo['social']['instagram'] );
		$this->assertEquals( 'https://linkedin.com/in/testuser', $seo['social']['linkedIn'] );
	}
}
