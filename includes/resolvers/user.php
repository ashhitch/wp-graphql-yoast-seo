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
            $indexable = $authorMeta->indexable;

            $schemaArray = $authorMeta->schema;

            $userSeo = [
                'title' => wp_gql_seo_format_string($authorMeta->title),
                'metaDesc' => wp_gql_seo_format_string($authorMeta->description),
                'metaRobotsNoindex' => $robots['index'],
                'metaRobotsNofollow' => $robots['follow'],
                'robots' => wp_gql_seo_build_robots($robots, $indexable),
                'canonical' => wp_gql_seo_format_string($authorMeta->canonical),
                'opengraphTitle' => wp_gql_seo_format_string($authorMeta->open_graph_title),
                'opengraphDescription' => wp_gql_seo_format_string($authorMeta->open_graph_description),
                'opengraphImage' => $context
                    ->get_loader('post')
                    ->load_deferred(absint($authorMeta->open_graph_image_id)),
                'opengraphLocale' => wp_gql_seo_format_string($authorMeta->open_graph_locale),
                'opengraphFbAppId' => wp_gql_seo_format_string($authorMeta->open_graph_fb_app_id),
                'opengraphEnabled' => boolval($authorMeta->open_graph_enabled),
                'twitterImage' => $context->get_loader('post')->load_deferred(absint($authorMeta->twitter_image_id)),
                'twitterTitle' => wp_gql_seo_format_string($authorMeta->twitter_title),
                'twitterDescription' => wp_gql_seo_format_string($authorMeta->twitter_description),
                'twitterCreator' => wp_gql_seo_format_string($authorMeta->twitter_creator),
                'twitterSite' => wp_gql_seo_format_string($authorMeta->twitter_site),
                'language' => wp_gql_seo_format_string($authorMeta->language),
                'region' => wp_gql_seo_format_string($authorMeta->region),
                'breadcrumbTitle' => wp_gql_seo_format_string($authorMeta->breadcrumb_title),
                'fullHead' => wp_gql_seo_get_full_head($authorMeta),
                'head' => wp_gql_seo_get_head_obj($authorMeta),
                'relNext' => wp_gql_seo_format_string($authorMeta->rel_next),
                'relPrev' => wp_gql_seo_format_string($authorMeta->rel_prev),
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
                    'mastodon' => wp_gql_seo_format_string(get_the_author_meta('mastodon', $user->userId)),
                ],

                'schema' => [
                    'raw' => wp_json_encode($schemaArray, JSON_UNESCAPED_SLASHES),
                    'pageType' => is_array($authorMeta->schema_page_type) ? $authorMeta->schema_page_type : [],
                    'articleType' => is_array($authorMeta->schema_article_type) ? $authorMeta->schema_article_type : [],
                ],
                'analysis' => wp_gql_seo_build_analysis($indexable),
            ];

            return !empty($userSeo) ? $userSeo : [];
        },
    ]);
});
