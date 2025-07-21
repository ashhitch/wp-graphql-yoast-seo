<?php
/**
 * Custom test loader for WP GraphQL Yoast SEO tests
 */

// Define a custom test suite loader
class WP_GraphQL_Yoast_SEO_TestSuite {
    /**
     * Load the test suite
     */
    public static function load_tests() {
        $suite = new PHPUnit\Framework\TestSuite('WP GraphQL Yoast SEO');
        
        // Map of file names to class names
        $test_files = [
            'test-post-type-seo.php' => 'Test_Post_Type_SEO',
            'test-taxonomy-seo.php' => 'Test_Taxonomy_SEO',
            'test-user-seo.php' => 'Test_User_SEO',
            'test-primary-taxonomy.php' => 'Test_Primary_Taxonomy',
            'test-root-query-seo.php' => 'Test_Root_Query_SEO',
            'test-helper-functions.php' => 'Test_Helper_Functions',
            'test-schema-registration.php' => 'Test_Schema_Registration',
        ];
        
        foreach ($test_files as $file => $class) {
            // Include the test file if it exists
            $file_path = dirname(__FILE__) . '/' . $file;
            if (file_exists($file_path)) {
                require_once $file_path;
                
                // Add the test class to the suite if it exists
                if (class_exists($class)) {
                    $suite->addTestSuite($class);
                } else {
                    echo "Warning: Class {$class} not found in {$file}\n";
                }
            } else {
                echo "Warning: File {$file} not found\n";
            }
        }
        
        return $suite;
    }
}

// Function to be used in phpunit.xml bootstrap
function wp_graphql_yoast_seo_suite() {
    return WP_GraphQL_Yoast_SEO_TestSuite::load_tests();
}
