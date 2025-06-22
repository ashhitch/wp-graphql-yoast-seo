# WP GraphQL Yoast SEO Tests

This directory contains PHPUnit tests for the WP GraphQL Yoast SEO plugin. The tests verify that the plugin correctly exposes Yoast SEO data through the GraphQL API.

## Test Coverage

The test suite covers:

1. **Post Type SEO Data** - Tests that SEO data for posts and custom post types is correctly exposed in GraphQL.
2. **Taxonomy SEO Data** - Tests that SEO data for categories, tags, and custom taxonomies is correctly exposed in GraphQL.
3. **User SEO Data** - Tests that SEO data for users/authors is correctly exposed in GraphQL.
4. **Primary Taxonomy** - Tests that primary category/taxonomy information is correctly exposed in GraphQL.
5. **Root Query SEO Data** - Tests that site-wide SEO configuration is correctly exposed in GraphQL.
6. **Helper Functions** - Tests the utility functions used by the plugin.
7. **Schema Registration** - Tests that all schema types are properly registered.

## Docker-Based Testing

This project uses Docker for development, and tests are designed to run within the Docker environment. This ensures consistent test results across different development environments.

### Running Tests in Docker

```bash
# Run all tests
./bin/docker-test.sh

# Run a specific test file
./bin/docker-test.sh tests/test-post-type-seo.php
```

The script will automatically:
1. Start Docker containers if they're not running
2. Install test dependencies in the container
3. Set up the WordPress test environment inside the container
4. Run the specified tests

### What Happens Behind the Scenes

The Docker test script:

1. Uses the same Docker environment defined in `docker-compose.yml`
2. Installs PHPUnit in the WordPress container
3. Sets up the WordPress test suite using the existing MySQL container
4. Runs the tests within the WordPress container

This approach ensures that tests run in the same environment as the plugin, providing more reliable test results.

## Writing New Tests

When adding new features to the plugin, please add corresponding tests. Follow these guidelines:

1. Create a new test file in the `tests` directory with the naming convention `test-{feature}.php`
2. Extend the `WP_UnitTestCase` class
3. Use the `wpSetUpBeforeClass` method for setup that should run once before all tests
4. Use the `setUp` and `tearDown` methods for setup and cleanup that should run before and after each test
5. Test method names should start with `test_` and be descriptive

