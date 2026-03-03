<?php
/**
 * Post type resolvers
 *
 * @package WP_Graphql_YOAST_SEO
 */

if (!defined('ABSPATH')) {
    exit();
}

use WPGraphQL\AppContext;
use WPGraphQL\Model\Term;

/**
 * Get SEO data for a post or term
 *
 * @param object $post The post or term object.
 * @param array  $args The resolver arguments.
 * @param AppContext $context The AppContext object.
 * @return array
 */
function wp_gql_seo_get_post_type_graphql_fields($post, array $args, AppContext $context)
{
    // Base array
    $seo = [];

    if (empty($post) || (!isset($post->ID) && !isset($post->term_id))) {
        return null;
    }

    if ($post instanceof Term) {
        $metaPromise = $context->get_loader('yoast_term_indexable')->load_deferred($post->term_id);
    } else {
        $metaPromise = $context->get_loader('yoast_post_indexable')->load_deferred($post->ID);
    }

    $is_term = $post instanceof Term;
    $post_id = !$is_term ? $post->ID : null;

    return wp_gql_seo_handle_promise_error(
        $metaPromise->then(function ($meta) use ($is_term, $post_id, $context) {
            $schemaArray = $meta !== false ? $meta->schema : [];

            // https://developer.yoast.com/blog/yoast-seo-14-0-using-yoast-seo-surfaces/
            $robots = $meta !== false ? $meta->robots : [];

            // Get data
            $seo = [
                'title' => wp_gql_seo_format_string($meta !== false ? $meta->title : ''),
                'metaDesc' => wp_gql_seo_format_string($meta !== false ? $meta->description : ''),
                'focuskw' => wp_gql_seo_format_string($post_id ? get_post_meta($post_id, '_yoast_wpseo_focuskw', true) : ''),
                'metaKeywords' => wp_gql_seo_format_string($post_id ? get_post_meta($post_id, '_yoast_wpseo_metakeywords', true) : ''),
                'metaRobotsNoindex' => $robots['index'] ?? '',
                'metaRobotsNofollow' => $robots['follow'] ?? '',
                'opengraphTitle' => wp_gql_seo_format_string($meta !== false ? $meta->open_graph_title : ''),
                'opengraphUrl' => wp_gql_seo_format_string($meta !== false ? $meta->open_graph_url : ''),
                'opengraphSiteName' => wp_gql_seo_format_string($meta !== false ? $meta->open_graph_site_name : ''),
                'opengraphType' => wp_gql_seo_format_string($meta !== false ? $meta->open_graph_type : ''),
                'opengraphAuthor' => wp_gql_seo_format_string($meta !== false ? $meta->open_graph_article_author : ''),
                'opengraphPublisher' => wp_gql_seo_format_string($meta !== false ? $meta->open_graph_article_publisher : ''),
                'opengraphPublishedTime' => wp_gql_seo_format_string(
                    $meta !== false ? $meta->open_graph_article_published_time : ''
                ),
                'opengraphModifiedTime' => wp_gql_seo_format_string(
                    $meta !== false ? $meta->open_graph_article_modified_time : ''
                ),
                'opengraphDescription' => wp_gql_seo_format_string($meta !== false ? $meta->open_graph_description : ''),
                'opengraphImage' => function () use ($context, $meta) {
                    $id = wp_gql_seo_get_og_image($meta !== false ? $meta->open_graph_images : []);

                    return $context->get_loader('post')->load_deferred(absint($id));
                },
                'twitterCardType' => wp_gql_seo_format_string($meta !== false ? $meta->twitter_card : ''),
                'twitterTitle' => wp_gql_seo_format_string($meta !== false ? $meta->twitter_title : ''),
                'twitterDescription' => wp_gql_seo_format_string($meta !== false ? $meta->twitter_description : ''),
                'twitterImage' => function () use ($context, $meta) {
                    $twitter_image = $meta !== false ? $meta->twitter_image : null;

                    if (empty($twitter_image)) {
                        return null;
                    }

                    $id = wp_gql_seo_attachment_url_to_postid($twitter_image);

                    return $context->get_loader('post')->load_deferred(absint($id));
                },
                'canonical' => wp_gql_seo_format_string($meta !== false ? $meta->canonical : ''),
                'readingTime' => floatval($meta !== false ? $meta->estimated_reading_time_minutes : ''),
                'breadcrumbs' => $meta !== false ? $meta->breadcrumbs : [],
                'cornerstone' => boolval($meta !== false && isset($meta->indexable) && isset($meta->indexable->is_cornerstone) ? $meta->indexable->is_cornerstone : false),
                'fullHead' => wp_gql_seo_get_full_head($meta),
                'primaryCategory' => function () use ($context, $is_term, $post_id) {
                    if ($is_term || !$post_id) {
                        return null;
                    }

                    $primary_term_id = function_exists('yoast_get_primary_term_id') ? yoast_get_primary_term_id('category', $post_id) : false;
                    $primary_term_id = absint($primary_term_id);

                    if (empty($primary_term_id)) {
                        return null;
                    }
                    return $context->get_loader('term')->load_deferred($primary_term_id);
                },
                'schema' => [
                    'pageType' => $meta !== false && isset($meta->schema_page_type) && is_array($meta->schema_page_type) ? $meta->schema_page_type : (
                        $meta !== false && isset($schemaArray['@graph'][0]['@type']) ? [$schemaArray['@graph'][0]['@type']] : []
                    ),
                    'articleType' => $meta !== false && isset($meta->schema_article_type) && is_array($meta->schema_article_type) ? $meta->schema_article_type : [],
                    'raw' => wp_json_encode($schemaArray, JSON_UNESCAPED_SLASHES),
                ],
            ];

            return !empty($seo) ? $seo : null;
        }),
        function (\Throwable $e) {
            // Return null if there was an error resolving the promise
            return null;
        }
    );
}

// Register GraphQL fields for NodeWithTitle and ContentNode types
add_action('graphql_register_types', function () {
    register_graphql_field('ContentNode', 'seo', [
        'type' => 'PostTypeSEO',
        'description' => __('The Yoast SEO data of the ContentNode', 'wp-graphql-yoast-seo'),
        'resolve' => function ($post, array $args, AppContext $context) {
            return wp_gql_seo_get_post_type_graphql_fields($post, $args, $context);
        },
    ]);

    register_graphql_field('NodeWithTitle', 'seo', [
        'type' => 'PostTypeSEO',
        'description' => __('The Yoast SEO data of the ContentNode', 'wp-graphql-yoast-seo'),
        'resolve' => function ($post, array $args, AppContext $context) {
            return wp_gql_seo_get_post_type_graphql_fields($post, $args, $context);
        },
    ]);

    // If WooCommerce is active, add seo to product
    if (class_exists('WooCommerce')) {
        register_graphql_field('Product', 'seo', [
            'type' => 'PostTypeSEO',
            'description' => __('The Yoast SEO data of the ContentNode', 'wp-graphql-yoast-seo'),
            'resolve' => function ($post, array $args, AppContext $context) {
                return wp_gql_seo_get_post_type_graphql_fields($post, $args, $context);
            },
        ]);
    }
});
