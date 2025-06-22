<?php
/**
 * Test Root Query SEO data
 *
 * @package WP_Graphql_YOAST_SEO
 */

class Test_Root_Query_SEO extends WP_UnitTestCase {

	/**
	 * Test that the SEOConfig type is registered in the schema
	 */
	public function test_seo_config_schema_registration() {
		$schema = \WPGraphQL::get_schema();
		$type_registry = \WPGraphQL::get_type_registry();
		
		// Check if SEOConfig type exists
		$this->assertTrue( $type_registry->has_type( 'SEOConfig' ) );
		
		// Check if the RootQuery type has the seo field
		$root_query_type = $type_registry->get_type( 'RootQuery' );
		$this->assertNotNull( $root_query_type );
		
		$root_query_fields = $root_query_type->getFields();
		$this->assertArrayHasKey( 'seo', $root_query_fields );
	}

	/**
	 * Test GraphQL query for SEO config data
	 */
	public function test_seo_config_graphql_query() {
		$query = '
		query GetSEOConfig {
			seo {
				webmaster {
					baiduVerify
					googleVerify
					msVerify
					yandexVerify
				}
				schema {
					companyName
					companyLogo {
						mediaItemUrl
					}
					personLogo {
						mediaItemUrl
					}
					logo {
						mediaItemUrl
					}
					siteName
					wordpressSiteName
					siteUrl
				}
				breadcrumbs {
					enabled
					boldLast
					showBlogPage
					archivePrefix
					prefix
					notFoundText
					homeText
					searchPrefix
					separator
				}
				social {
					facebook {
						url
						defaultImage {
							mediaItemUrl
						}
					}
					twitter {
						username
						cardType
					}
					instagram {
						url
					}
					linkedIn {
						url
					}
					mySpace {
						url
					}
					pinterest {
						url
						metaTag
					}
					youTube {
						url
					}
					wikipedia {
						url
					}
				}
			}
		}
		';
		
		$actual = graphql( array(
			'query' => $query,
		) );
		
		// Check if there are no errors
		$this->assertArrayNotHasKey( 'errors', $actual );
		
		// Check if the data is returned as expected
		$this->assertArrayHasKey( 'data', $actual );
		$this->assertArrayHasKey( 'seo', $actual['data'] );
		
		$seo = $actual['data']['seo'];
		
		// Test that all required sections are present
		$this->assertArrayHasKey( 'webmaster', $seo );
		$this->assertArrayHasKey( 'schema', $seo );
		$this->assertArrayHasKey( 'breadcrumbs', $seo );
		$this->assertArrayHasKey( 'social', $seo );
		
		// Test webmaster section
		$this->assertArrayHasKey( 'baiduVerify', $seo['webmaster'] );
		$this->assertArrayHasKey( 'googleVerify', $seo['webmaster'] );
		$this->assertArrayHasKey( 'msVerify', $seo['webmaster'] );
		$this->assertArrayHasKey( 'yandexVerify', $seo['webmaster'] );
		
		// Test schema section
		$this->assertArrayHasKey( 'companyName', $seo['schema'] );
		$this->assertArrayHasKey( 'siteName', $seo['schema'] );
		$this->assertArrayHasKey( 'wordpressSiteName', $seo['schema'] );
		$this->assertArrayHasKey( 'siteUrl', $seo['schema'] );
		
		// Test breadcrumbs section
		$this->assertArrayHasKey( 'enabled', $seo['breadcrumbs'] );
		$this->assertArrayHasKey( 'homeText', $seo['breadcrumbs'] );
		$this->assertArrayHasKey( 'separator', $seo['breadcrumbs'] );
		
		// Test social section
		$this->assertArrayHasKey( 'facebook', $seo['social'] );
		$this->assertArrayHasKey( 'twitter', $seo['social'] );
		$this->assertArrayHasKey( 'instagram', $seo['social'] );
	}

	/**
	 * Test the SEO config data with modified settings
	 */
	public function test_seo_config_with_modified_settings() {
		// Set some Yoast SEO options
		$wpseo_options = get_option( 'wpseo' );
		if (!is_array($wpseo_options)) {
			$wpseo_options = array();
		}
		
		$wpseo_options['company_name'] = 'Test Company';
		$wpseo_options['website_name'] = 'Test Website';
		$wpseo_options['alternate_website_name'] = 'Alternate Test Website';
		
		// Set breadcrumb options
		$wpseo_options['breadcrumbs-enable'] = true;
		$wpseo_options['breadcrumbs-home'] = 'Home Page';
		$wpseo_options['breadcrumbs-sep'] = '»';
		
		// Set social options
		$wpseo_options['facebook_site'] = 'https://facebook.com/testcompany';
		$wpseo_options['twitter_site'] = '@testcompany';
		
		// Save options
		update_option( 'wpseo', $wpseo_options );
		
		// Run the query
		$query = '
		query GetSEOConfig {
			seo {
				schema {
					companyName
					siteName
					wordpressSiteName
				}
				breadcrumbs {
					enabled
					homeText
					separator
				}
				social {
					facebook {
						url
					}
					twitter {
						username
					}
				}
			}
		}
		';
		
		$actual = graphql( array(
			'query' => $query,
		) );
		
		// Check if there are no errors
		$this->assertArrayNotHasKey( 'errors', $actual );
		
		// Check if the data is returned as expected
		$seo = $actual['data']['seo'];
		
		// Test schema section with our modified values
		$this->assertEquals( 'Test Company', $seo['schema']['companyName'] );
		$this->assertEquals( 'Test Website', $seo['schema']['siteName'] );
		
		// Test breadcrumbs section with our modified values
		$this->assertTrue( $seo['breadcrumbs']['enabled'] );
		$this->assertEquals( 'Home Page', $seo['breadcrumbs']['homeText'] );
		$this->assertEquals( '»', $seo['breadcrumbs']['separator'] );
		
		// Test social section with our modified values
		$this->assertEquals( 'https://facebook.com/testcompany', $seo['social']['facebook']['url'] );
		$this->assertEquals( '@testcompany', $seo['social']['twitter']['username'] );
	}
}
