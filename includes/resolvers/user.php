<?php
/**
 * User resolvers
 *
 * @package WP_Graphql_YOAST_SEO
 */

if (!defined('ABSPATH')) {
    exit();
}

use WPGraphQL\AppContext;

/**
 * Register the User SEO field
 */
add_action('graphql_register_types', function () {
    register_graphql_field('User', 'seo', [
        'type' => 'SEOUser',
        'description' => __('The Yoast SEO data of a user', 'wp-graphql-yoast-seo'),
        'resolve' => function ($user, array $args, AppContext $context) {
            // Cache the author meta data
            $authorMeta = YoastSEO()->meta->for_author($user->userId);

            // Author has no posts
            if (!$authorMeta) {
                return [];
            }

            $robots = $authorMeta->robots;

            $schemaArray = $authorMeta->schema;

            $userSeo = [
                'title' => wp_gql_seo_format_string($authorMeta->title),
                'metaDesc' => wp_gql_seo_format_string($authorMeta->description),
                'metaRobotsNoindex' => $robots['index'],
                'metaRobotsNofollow' => $robots['follow'],
                'canonical' => $authorMeta->canonical,
                'opengraphTitle' => $authorMeta->open_graph_title,
                'opengraphDescription' => $authorMeta->open_graph_description,
                'opengraphImage' => $context
                    ->get_loader('post')
                    ->load_deferred(absint($authorMeta->open_graph_image_id)),
                'twitterImage' => $context
                    ->get_loader('post')
                    ->load_deferred(absint($authorMeta->twitter_image_id)),
                'twitterTitle' => $authorMeta->twitter_title,
                'twitterDescription' => $authorMeta->twitter_description,
                'language' => $authorMeta->language,
                'region' => $authorMeta->region,
                'breadcrumbTitle' => $authorMeta->breadcrumb_title,
                'fullHead' => wp_gql_seo_get_full_head($authorMeta),
                'social' => [
                    'facebook' => wp_gql_seo_format_string(get_the_author_meta('facebook', $user->userId)),
                    'twitter' => wp_gql_seo_format_string(get_the_author_meta('twitter', $user->userId)),
                    'instagram' => wp_gql_seo_format_string(get_the_author_meta('instagram', $user->userId)),
                    'linkedIn' => wp_gql_seo_format_string(get_the_author_meta('linkedin', $user->userId)),
                    'mySpace' => wp_gql_seo_format_string(get_the_author_meta('myspace', $user->userId)),
                    'pinterest' => wp_gql_seo_format_string(get_the_author_meta('pinterest', $user->userId)),
                    'youTube' => wp_gql_seo_format_string(get_the_author_meta('youtube', $user->userId)),
                    'soundCloud' => wp_gql_seo_format_string(get_the_author_meta('soundcloud', $user->userId)),
                    'wikipedia' => wp_gql_seo_format_string(get_the_author_meta('wikipedia', $user->userId)),
                ],

                'schema' => [
                    'raw' => wp_json_encode($schemaArray, JSON_UNESCAPED_SLASHES),
                    'pageType' => is_array($authorMeta->schema_page_type)
                        ? $authorMeta->schema_page_type
                        : [],
                    'articleType' => is_array($authorMeta->schema_article_type)
                        ? $authorMeta->schema_article_type
                        : [],
                ],
            ];

            return !empty($userSeo) ? $userSeo : [];
        },
    ]);
});
