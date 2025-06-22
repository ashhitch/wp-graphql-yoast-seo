<?php
/**
 * Test TaxonomySEO data
 *
 * @package WP_Graphql_YOAST_SEO
 */

class Test_Taxonomy_SEO extends WP_UnitTestCase {

	/**
	 * Test category ID
	 *
	 * @var int
	 */
	protected static $category_id;

	/**
	 * Test tag ID
	 *
	 * @var int
	 */
	protected static $tag_id;

	/**
	 * Set up test data before the tests run
	 */
	public static function wpSetUpBeforeClass( $factory ) {
		// Create a test category
		self::$category_id = $factory->term->create( array(
			'name'     => 'Test Category',
			'taxonomy' => 'category',
		) );

		// Create a test tag
		self::$tag_id = $factory->term->create( array(
			'name'     => 'Test Tag',
			'taxonomy' => 'post_tag',
		) );

		// Set Yoast SEO meta data for the test category
		update_term_meta( self::$category_id, 'wpseo_title', 'Category SEO Title' );
		update_term_meta( self::$category_id, 'wpseo_desc', 'Category SEO Description' );
		update_term_meta( self::$category_id, 'wpseo_focuskw', 'category keyword' );
		update_term_meta( self::$category_id, 'wpseo_metakeywords', 'category, keywords' );
		update_term_meta( self::$category_id, 'wpseo_noindex', 'index' );
		update_term_meta( self::$category_id, 'wpseo_nofollow', 'nofollow' );
		update_term_meta( self::$category_id, 'wpseo_opengraph-title', 'Category OG Title' );
		update_term_meta( self::$category_id, 'wpseo_opengraph-description', 'Category OG Description' );
		update_term_meta( self::$category_id, 'wpseo_twitter-title', 'Category Twitter Title' );
		update_term_meta( self::$category_id, 'wpseo_twitter-description', 'Category Twitter Description' );
		update_term_meta( self::$category_id, 'wpseo_canonical', 'https://example.com/category-canonical' );

		// Set Yoast SEO meta data for the test tag
		update_term_meta( self::$tag_id, 'wpseo_title', 'Tag SEO Title' );
		update_term_meta( self::$tag_id, 'wpseo_desc', 'Tag SEO Description' );
		update_term_meta( self::$tag_id, 'wpseo_focuskw', 'tag keyword' );
		update_term_meta( self::$tag_id, 'wpseo_metakeywords', 'tag, keywords' );
		update_term_meta( self::$tag_id, 'wpseo_noindex', 'noindex' );
		update_term_meta( self::$tag_id, 'wpseo_nofollow', 'follow' );
		update_term_meta( self::$tag_id, 'wpseo_opengraph-title', 'Tag OG Title' );
		update_term_meta( self::$tag_id, 'wpseo_opengraph-description', 'Tag OG Description' );
		update_term_meta( self::$tag_id, 'wpseo_twitter-title', 'Tag Twitter Title' );
		update_term_meta( self::$tag_id, 'wpseo_twitter-description', 'Tag Twitter Description' );
		update_term_meta( self::$tag_id, 'wpseo_canonical', 'https://example.com/tag-canonical' );
	}

	/**
	 * Test that the taxonomy SEO field resolver returns the expected data
	 */
	public function test_taxonomy_seo_resolver() {
		// Test category
		$category = get_term( self::$category_id, 'category' );
		$category_model = new WPGraphQL\Model\Term( $category );
		$context = new WPGraphQL\AppContext();

		// Create a mock resolver function similar to what's in the plugin
		$term_obj = get_term( $category->term_id );
		$meta = WPSEO_Taxonomy_Meta::get_term_meta( (int) $term_obj->term_id, $term_obj->taxonomy );
		
		// Test basic category SEO fields
		$this->assertEquals( 'Category SEO Title', $meta['wpseo_title'] );
		$this->assertEquals( 'Category SEO Description', $meta['wpseo_desc'] );
		$this->assertEquals( 'category keyword', $meta['wpseo_focuskw'] );
		$this->assertEquals( 'category, keywords', $meta['wpseo_metakeywords'] );
		
		// Test tag
		$tag = get_term( self::$tag_id, 'post_tag' );
		$tag_model = new WPGraphQL\Model\Term( $tag );
		
		// Create a mock resolver function similar to what's in the plugin
		$term_obj = get_term( $tag->term_id );
		$meta = WPSEO_Taxonomy_Meta::get_term_meta( (int) $term_obj->term_id, $term_obj->taxonomy );
		
		// Test basic tag SEO fields
		$this->assertEquals( 'Tag SEO Title', $meta['wpseo_title'] );
		$this->assertEquals( 'Tag SEO Description', $meta['wpseo_desc'] );
		$this->assertEquals( 'tag keyword', $meta['wpseo_focuskw'] );
		$this->assertEquals( 'tag, keywords', $meta['wpseo_metakeywords'] );
	}

	/**
	 * Test the GraphQL schema registration for taxonomies
	 */
	public function test_taxonomy_seo_schema_registration() {
		$schema = \WPGraphQL::get_schema();
		$type_registry = \WPGraphQL::get_type_registry();
		
		// Check if TaxonomySEO type exists
		$this->assertTrue( $type_registry->has_type( 'TaxonomySEO' ) );
		
		// Check if the Category type has the seo field
		$category_type = $type_registry->get_type( 'Category' );
		$this->assertNotNull( $category_type );
		
		$category_fields = $category_type->getFields();
		$this->assertArrayHasKey( 'seo', $category_fields );
		
		// Check if the Tag type has the seo field
		$tag_type = $type_registry->get_type( 'Tag' );
		$this->assertNotNull( $tag_type );
		
		$tag_fields = $tag_type->getFields();
		$this->assertArrayHasKey( 'seo', $tag_fields );
	}

	/**
	 * Test GraphQL query for category SEO data
	 */
	public function test_category_seo_graphql_query() {
		$category_id = self::$category_id;
		$query = '
		query GetCategorySEO($id: ID!) {
			category(id: $id, idType: DATABASE_ID) {
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
					schema {
						raw
					}
				}
			}
		}
		';
		
		$variables = array(
			'id' => $category_id,
		);
		
		$actual = graphql( array(
			'query'     => $query,
			'variables' => $variables,
		) );
		
		// Check if there are no errors
		$this->assertArrayNotHasKey( 'errors', $actual );
		
		// Check if the data is returned as expected
		$this->assertArrayHasKey( 'data', $actual );
		$this->assertArrayHasKey( 'category', $actual['data'] );
		$this->assertArrayHasKey( 'seo', $actual['data']['category'] );
		
		$seo = $actual['data']['category']['seo'];
		
		// Test basic SEO fields
		$this->assertEquals( 'Category SEO Title', $seo['title'] );
		$this->assertEquals( 'Category SEO Description', $seo['metaDesc'] );
		$this->assertEquals( 'category keyword', $seo['focuskw'] );
		$this->assertEquals( 'category, keywords', $seo['metaKeywords'] );
		
		// Test OpenGraph fields
		$this->assertEquals( 'Category OG Title', $seo['opengraphTitle'] );
		$this->assertEquals( 'Category OG Description', $seo['opengraphDescription'] );
		
		// Test Twitter fields
		$this->assertEquals( 'Category Twitter Title', $seo['twitterTitle'] );
		$this->assertEquals( 'Category Twitter Description', $seo['twitterDescription'] );
		
		// Test canonical field
		$this->assertEquals( 'https://example.com/category-canonical', $seo['canonical'] );
	}

	/**
	 * Test GraphQL query for tag SEO data
	 */
	public function test_tag_seo_graphql_query() {
		$tag_id = self::$tag_id;
		$query = '
		query GetTagSEO($id: ID!) {
			tag(id: $id, idType: DATABASE_ID) {
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
					schema {
						raw
					}
				}
			}
		}
		';
		
		$variables = array(
			'id' => $tag_id,
		);
		
		$actual = graphql( array(
			'query'     => $query,
			'variables' => $variables,
		) );
		
		// Check if there are no errors
		$this->assertArrayNotHasKey( 'errors', $actual );
		
		// Check if the data is returned as expected
		$this->assertArrayHasKey( 'data', $actual );
		$this->assertArrayHasKey( 'tag', $actual['data'] );
		$this->assertArrayHasKey( 'seo', $actual['data']['tag'] );
		
		$seo = $actual['data']['tag']['seo'];
		
		// Test basic SEO fields
		$this->assertEquals( 'Tag SEO Title', $seo['title'] );
		$this->assertEquals( 'Tag SEO Description', $seo['metaDesc'] );
		$this->assertEquals( 'tag keyword', $seo['focuskw'] );
		$this->assertEquals( 'tag, keywords', $seo['metaKeywords'] );
		
		// Test OpenGraph fields
		$this->assertEquals( 'Tag OG Title', $seo['opengraphTitle'] );
		$this->assertEquals( 'Tag OG Description', $seo['opengraphDescription'] );
		
		// Test Twitter fields
		$this->assertEquals( 'Tag Twitter Title', $seo['twitterTitle'] );
		$this->assertEquals( 'Tag Twitter Description', $seo['twitterDescription'] );
		
		// Test canonical field
		$this->assertEquals( 'https://example.com/tag-canonical', $seo['canonical'] );
	}
}
