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
            // Author has no posts
            if (!YoastSEO()->meta->for_author($user->userId)) {
                return [];
            }

            $robots = YoastSEO()->meta->for_author($user->userId)->robots;

            $schemaArray = YoastSEO()->meta->for_author($user->userId)->schema;

            $userSeo = [
                'title' => wp_gql_seo_format_string(YoastSEO()->meta->for_author($user->userId)->title),
                'metaDesc' => wp_gql_seo_format_string(YoastSEO()->meta->for_author($user->userId)->description),
                'metaRobotsNoindex' => $robots['index'],
                'metaRobotsNofollow' => $robots['follow'],
                'canonical' => YoastSEO()->meta->for_author($user->userId)->canonical,
                'opengraphTitle' => YoastSEO()->meta->for_author($user->userId)->open_graph_title,
                'opengraphDescription' => YoastSEO()->meta->for_author($user->userId)->open_graph_description,
                'opengraphImage' => $context
                    ->get_loader('post')
                    ->load_deferred(absint(YoastSEO()->meta->for_author($user->userId)->open_graph_image_id)),
                'twitterImage' => $context
                    ->get_loader('post')
                    ->load_deferred(absint(YoastSEO()->meta->for_author($user->userId)->twitter_image_id)),
                'twitterTitle' => YoastSEO()->meta->for_author($user->userId)->twitter_title,
                'twitterDescription' => YoastSEO()->meta->for_author($user->userId)->twitter_description,
                'language' => YoastSEO()->meta->for_author($user->userId)->language,
                'region' => YoastSEO()->meta->for_author($user->userId)->region,
                'breadcrumbTitle' => YoastSEO()->meta->for_author($user->userId)->breadcrumb_title,
                'fullHead' => wp_gql_seo_get_full_head(YoastSEO()->meta->for_author($user->userId)),
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
                    'raw' => json_encode($schemaArray, JSON_UNESCAPED_SLASHES),
                    'pageType' => is_array(YoastSEO()->meta->for_author($user->userId)->schema_page_type)
                        ? YoastSEO()->meta->for_author($user->userId)->schema_page_type
                        : [],
                    'articleType' => is_array(YoastSEO()->meta->for_author($user->userId)->schema_article_type)
                        ? YoastSEO()->meta->for_author($user->userId)->schema_article_type
                        : [],
                ],
            ];

            return !empty($userSeo) ? $userSeo : [];
        },
    ]);
});
