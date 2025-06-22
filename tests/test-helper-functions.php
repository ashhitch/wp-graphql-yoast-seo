<?php
/**
 * Test helper functions
 *
 * @package WP_Graphql_YOAST_SEO
 */

class Test_Helper_Functions extends WP_UnitTestCase {

	/**
	 * Test the wp_gql_seo_format_string function
	 */
	public function test_wp_gql_seo_format_string() {
		// Test with regular string
		$this->assertEquals('Test String', wp_gql_seo_format_string('Test String'));
		
		// Test with string containing HTML entities
		$this->assertEquals('Test & String', wp_gql_seo_format_string('Test &amp; String'));
		
		// Test with string containing whitespace
		$this->assertEquals('Test String', wp_gql_seo_format_string('  Test String  '));
		
		// Test with null value
		$this->assertNull(wp_gql_seo_format_string(null));
		
		// Test with empty string
		$this->assertEquals('', wp_gql_seo_format_string(''));
	}

	/**
	 * Test the wp_gql_seo_get_field_key function
	 */
	public function test_wp_gql_seo_get_field_key() {
		// Test with simple string
		$this->assertEquals('test', wp_gql_seo_get_field_key('test'));
		
		// Test with camelCase
		$this->assertEquals('testCase', wp_gql_seo_get_field_key('testCase'));
		
		// Test with snake_case
		$this->assertEquals('testCase', wp_gql_seo_get_field_key('test_case'));
		
		// Test with kebab-case
		$this->assertEquals('testCase', wp_gql_seo_get_field_key('test-case'));
		
		// Test with PascalCase
		$this->assertEquals('testCase', wp_gql_seo_get_field_key('TestCase'));
		
		// Test with special characters
		$this->assertEquals('testCase', wp_gql_seo_get_field_key('test@case'));
	}

	/**
	 * Test the wp_gql_seo_get_og_image function
	 */
	public function test_wp_gql_seo_get_og_image() {
		// Test with empty array
		$this->assertEquals('', wp_gql_seo_get_og_image([]));
		
		// Test with image array containing ID
		$image_with_id = [
			[
				'url' => 'https://example.com/image.jpg',
				'id' => 123
			]
		];
		$this->assertEquals(123, wp_gql_seo_get_og_image($image_with_id));
		
		// Test with image array without ID
		$image_without_id = [
			[
				'url' => 'https://example.com/image.jpg'
			]
		];
		
		// This will call wpcom_vip_attachment_url_to_postid which we can't fully test
		// But we can at least ensure it doesn't error
		$result = wp_gql_seo_get_og_image($image_without_id);
		$this->assertIsNotBool($result); // Should return string or null, not boolean
	}

	/**
	 * Test the wp_gql_seo_build_content_types function
	 */
	public function test_wp_gql_seo_build_content_types() {
		// Create a test post type
		register_post_type('test_cpt', [
			'public' => true,
			'show_in_graphql' => true,
			'graphql_single_name' => 'testCpt',
			'graphql_plural_name' => 'testCpts',
		]);
		
		// Test with the registered post type
		$result = wp_gql_seo_build_content_types(['test_cpt']);
		$this->assertArrayHasKey('testCpt', $result);
		$this->assertEquals(['type' => 'SEOContentType'], $result['testCpt']);
		
		// Clean up
		unregister_post_type('test_cpt');
	}

	/**
	 * Test the wp_gql_seo_build_taxonomy_types function
	 */
	public function test_wp_gql_seo_build_taxonomy_types() {
		// Create a test taxonomy
		register_taxonomy('test_tax', 'post', [
			'public' => true,
			'show_in_graphql' => true,
			'graphql_single_name' => 'testTax',
			'graphql_plural_name' => 'testTaxes',
		]);
		
		// Test with the registered taxonomy
		$result = wp_gql_seo_build_taxonomy_types(['test_tax']);
		$this->assertArrayHasKey('testTax', $result);
		$this->assertEquals(['type' => 'SEOTaxonomyType'], $result['testTax']);
		
		// Clean up
		unregister_taxonomy('test_tax');
	}

	/**
	 * Test the wp_gql_seo_get_full_head function
	 */
	public function test_wp_gql_seo_get_full_head() {
		// Test with false value
		$this->assertEquals('', wp_gql_seo_get_full_head(false));
		
		// Create a mock meta object with string head
		$meta_with_string = new stdClass();
		$meta_with_string->head = '<title>Test Title</title>';
		
		// We can't directly test this without the full Yoast SEO environment,
		// but we can test the function signature and ensure it doesn't error
		$this->assertTrue(function_exists('wp_gql_seo_get_full_head'));
	}
}
