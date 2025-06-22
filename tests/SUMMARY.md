# WP GraphQL Yoast SEO Test Suite Summary

This document provides an overview of the test suite created for the WP GraphQL Yoast SEO plugin.

## Test Coverage

The test suite provides comprehensive coverage of the plugin's functionality, ensuring that all aspects of the Yoast SEO data are properly exposed through the GraphQL API.

### 1. Post Type SEO Tests (`test-post-type-seo.php`)

Tests the SEO data for posts and custom post types:

- Schema registration for PostTypeSEO type
- Direct resolver function testing
- GraphQL queries for post SEO data
- Testing all SEO fields (title, meta description, robots, OpenGraph, etc.)

### 2. Taxonomy SEO Tests (`test-taxonomy-seo.php`)

Tests the SEO data for taxonomies (categories, tags, and custom taxonomies):

- Schema registration for TaxonomySEO type
- GraphQL queries for category SEO data
- GraphQL queries for tag SEO data
- Testing all taxonomy SEO fields

### 3. User SEO Tests (`test-user-seo.php`)

Tests the SEO data for users/authors:

- Schema registration for SEOUser type
- GraphQL queries for user SEO data
- Testing user SEO fields (title, meta description, social profiles)
- Testing user social media profiles

### 4. Primary Taxonomy Tests (`test-primary-taxonomy.php`)

Tests the primary taxonomy functionality:

- Schema registration for isPrimary field on taxonomy connection edges
- GraphQL queries for primary category data
- Testing changing the primary category

### 5. Root Query SEO Tests (`test-root-query-seo.php`)

Tests the site-wide SEO configuration:

- Schema registration for SEOConfig type
- GraphQL queries for global SEO settings
- Testing webmaster verification, schema, breadcrumbs, and social settings
- Testing with modified settings

### 6. Helper Function Tests (`test-helper-functions.php`)

Tests the utility functions used by the plugin:

- Testing string formatting functions
- Testing field key normalization
- Testing OpenGraph image retrieval
- Testing content type and taxonomy type builders

### 7. Schema Registration Tests (`test-schema-registration.php`)

Tests the comprehensive schema registration:

- Verifying all SEO types are registered
- Testing SEO fields on interfaces
- Testing SEO fields on post types
- Testing SEO fields on taxonomy types
- Testing SEO fields on User type
- Testing SEO field on RootQuery
- Testing isPrimary field on taxonomy connection edges

## Running the Tests

Since this project uses Docker for development, tests are designed to run within the Docker environment:

```bash
# Run all tests
./bin/docker-test.sh

# Run a specific test file
./bin/docker-test.sh tests/test-post-type-seo.php
```

## Test Environment

The Docker-based testing approach automatically sets up everything needed for testing:

1. Uses the existing Docker containers from your development environment
2. Installs PHPUnit in the WordPress container
3. Sets up the WordPress test suite inside the container
4. Uses the existing MySQL database container
5. Ensures WPGraphQL and Yoast SEO plugins are available

This approach ensures consistent test results across different development environments.
