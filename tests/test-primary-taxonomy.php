<?php
/**
 * Test Primary Taxonomy functionality
 *
 * @package WP_Graphql_YOAST_SEO
 */

class Test_Primary_Taxonomy extends WP_UnitTestCase {

	/**
	 * Test post ID
	 *
	 * @var int
	 */
	protected static $post_id;

	/**
	 * Test category IDs
	 *
	 * @var array
	 */
	protected static $category_ids;

	/**
	 * Set up test data before the tests run
	 */
	public static function wpSetUpBeforeClass( $factory ) {
		// Create test categories
		self::$category_ids = [
			$factory->term->create( array(
				'name'     => 'Primary Category',
				'taxonomy' => 'category',
			) ),
			$factory->term->create( array(
				'name'     => 'Secondary Category',
				'taxonomy' => 'category',
			) ),
		];

		// Create a test post
		self::$post_id = $factory->post->create( array(
			'post_title'   => 'Test Post with Primary Category',
			'post_content' => 'This is test content',
			'post_status'  => 'publish',
		) );

		// Assign categories to the post
		wp_set_post_categories( self::$post_id, self::$category_ids );

		// Set the primary category
		update_post_meta( 
			self::$post_id, 
			'_yoast_wpseo_primary_category', 
			self::$category_ids[0] 
		);
	}

	/**
	 * Test that the primary taxonomy field is registered in the schema
	 */
	public function test_primary_taxonomy_schema_registration() {
		$schema = \WPGraphQL::get_schema();
		$type_registry = \WPGraphQL::get_type_registry();
		
		// Check if the PostToCategoryConnectionEdge type has the isPrimary field
		$edge_type_name = 'PostToCategoryConnectionEdge';
		$edge_type = $type_registry->get_type( $edge_type_name );
		
		$this->assertNotNull( $edge_type );
		
		$edge_fields = $edge_type->getFields();
		$this->assertArrayHasKey( 'isPrimary', $edge_fields );
	}

	/**
	 * Test GraphQL query for primary category
	 */
	public function test_primary_category_graphql_query() {
		$post_id = self::$post_id;
		$query = '
		query GetPostWithPrimaryCategory($id: ID!) {
			post(id: $id, idType: DATABASE_ID) {
				title
				categories {
					edges {
						isPrimary
						node {
							name
							databaseId
						}
					}
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
		$this->assertArrayHasKey( 'categories', $actual['data']['post'] );
		$this->assertArrayHasKey( 'edges', $actual['data']['post']['categories'] );
		
		// Find the primary category
		$primary_found = false;
		$non_primary_found = false;
		
		foreach ( $actual['data']['post']['categories']['edges'] as $edge ) {
			if ( $edge['node']['databaseId'] == self::$category_ids[0] ) {
				$this->assertTrue( $edge['isPrimary'] );
				$this->assertEquals( 'Primary Category', $edge['node']['name'] );
				$primary_found = true;
			} else {
				$this->assertFalse( $edge['isPrimary'] );
				$this->assertEquals( 'Secondary Category', $edge['node']['name'] );
				$non_primary_found = true;
			}
		}
		
		// Ensure we found both categories
		$this->assertTrue( $primary_found, 'Primary category not found in results' );
		$this->assertTrue( $non_primary_found, 'Secondary category not found in results' );
	}

	/**
	 * Test changing the primary category
	 */
	public function test_changing_primary_category() {
		$post_id = self::$post_id;
		
		// Change the primary category to the second category
		update_post_meta( 
			$post_id, 
			'_yoast_wpseo_primary_category', 
			self::$category_ids[1] 
		);
		
		// Query again
		$query = '
		query GetPostWithPrimaryCategory($id: ID!) {
			post(id: $id, idType: DATABASE_ID) {
				categories {
					edges {
						isPrimary
						node {
							name
							databaseId
						}
					}
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
		
		// Find the new primary category
		$primary_found = false;
		$non_primary_found = false;
		
		foreach ( $actual['data']['post']['categories']['edges'] as $edge ) {
			if ( $edge['node']['databaseId'] == self::$category_ids[1] ) {
				$this->assertTrue( $edge['isPrimary'] );
				$this->assertEquals( 'Secondary Category', $edge['node']['name'] );
				$primary_found = true;
			} else {
				$this->assertFalse( $edge['isPrimary'] );
				$this->assertEquals( 'Primary Category', $edge['node']['name'] );
				$non_primary_found = true;
			}
		}
		
		// Ensure we found both categories with updated primary status
		$this->assertTrue( $primary_found, 'New primary category not found in results' );
		$this->assertTrue( $non_primary_found, 'Former primary category not found in results' );
	}
}
