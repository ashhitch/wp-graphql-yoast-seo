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

    // Removed unused $map variable
    if ($post instanceof Term) {
        $meta = YoastSEO()->meta->for_term($post->term_id);
    } else {
        $meta = YoastSEO()->meta->for_post($post->ID);
    }

    $schemaArray = $meta !== false ? $meta->schema : [];

    // https://developer.yoast.com/blog/yoast-seo-14-0-using-yoast-seo-surfaces/
    $robots = $meta !== false ? $meta->robots : [];

    // Get data
    $seo = [
        'title' => wp_gql_seo_format_string($meta !== false ? $meta->title : ''),
        'metaDesc' => wp_gql_seo_format_string($meta !== false ? $meta->description : ''),
        'focuskw' => wp_gql_seo_format_string(get_post_meta($post->ID, '_yoast_wpseo_focuskw', true)),
        'metaKeywords' => wp_gql_seo_format_string(get_post_meta($post->ID, '_yoast_wpseo_metakeywords', true)),
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
            $twitter_image = $meta->twitter_image;

            if (empty($twitter_image)) {
                return null;
            }

            $id = wp_gql_seo_attachment_url_to_postid($twitter_image);

            return $context->get_loader('post')->load_deferred(absint($id));
        },
        'canonical' => wp_gql_seo_format_string($meta !== false ? $meta->canonical : ''),
        'readingTime' => floatval($meta !== false ? $meta->estimated_reading_time_minutes : ''),
        'breadcrumbs' => $meta !== false ? $meta->breadcrumbs : [],
        'cornerstone' => boolval($meta !== false ? $meta->indexable->is_cornerstone : false),
        'fullHead' => wp_gql_seo_get_full_head($meta),
        'schema' => [
            'pageType' => $meta !== false && is_array($meta->schema_page_type) ? $meta->schema_page_type : [],
            'articleType' => $meta !== false && is_array($meta->schema_article_type) ? $meta->schema_article_type : [],
            'raw' => wp_json_encode($schemaArray, JSON_UNESCAPED_SLASHES),
        ],
    ];

    return !empty($seo) ? $seo : null;
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
