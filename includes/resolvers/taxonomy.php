<?php
/**
 * Taxonomy resolvers
 *
 * @package WP_Graphql_YOAST_SEO
 */

if (!defined('ABSPATH')) {
    exit();
}

use WPGraphQL\AppContext;

/**
 * Register fields for taxonomy types
 */
add_action('graphql_register_types', function () {
    $taxonomies = \WPGraphQL::get_allowed_taxonomies();

    // Loop through allowed taxonomies and add SEO field to each
    if (!empty($taxonomies) && is_array($taxonomies)) {
        foreach ($taxonomies as $tax) {
            $taxonomy = get_taxonomy($tax);

            if (empty($taxonomy) || !isset($taxonomy->graphql_single_name)) {
                continue;
            }

            register_graphql_field($taxonomy->graphql_single_name, 'seo', [
                'type' => 'TaxonomySEO',
                'description' => sprintf(
                    // translators: %s is the taxonomy label.
                    __('The Yoast SEO data of the %s taxonomy.', 'wp-graphql-yoast-seo'),
                    $taxonomy->label
                ),
                'resolve' => function ($term, array $args, AppContext $context) {
                    $term_obj = get_term($term->term_id);

                    $meta = WPSEO_Taxonomy_Meta::get_term_meta((int) $term_obj->term_id, $term_obj->taxonomy);
                    $yoast_meta = YoastSEO()->meta->for_term($term->term_id);

                    if ($yoast_meta === false) {
                        return null;
                    }

                    $robots = $yoast_meta->robots;

                    $schemaArray = $yoast_meta->schema;
                    $indexable = $yoast_meta->indexable;

                    // Get data
                    $seo = [
                        'title' => wp_gql_seo_format_string(html_entity_decode(wp_strip_all_tags($yoast_meta->title))),
                        'metaDesc' => wp_gql_seo_format_string($yoast_meta->description),
                        'focuskw' => isset($meta['wpseo_focuskw'])
                            ? wp_gql_seo_format_string($meta['wpseo_focuskw'])
                            : null,
                        'metaKeywords' => isset($meta['wpseo_metakeywords'])
                            ? wp_gql_seo_format_string($meta['wpseo_metakeywords'])
                            : null,
                        'metaRobotsNoindex' => $robots['index'],
                        'metaRobotsNofollow' => $robots['follow'],
                        'robots' => wp_gql_seo_build_robots($robots, $indexable),
                        'opengraphTitle' => wp_gql_seo_format_string($yoast_meta->open_graph_title),
                        'opengraphUrl' => wp_gql_seo_format_string($yoast_meta->open_graph_url),
                        'opengraphSiteName' => wp_gql_seo_format_string($yoast_meta->open_graph_site_name),
                        'opengraphType' => wp_gql_seo_format_string($yoast_meta->open_graph_type),
                        'opengraphAuthor' => wp_gql_seo_format_string($yoast_meta->open_graph_article_author),
                        'opengraphPublisher' => wp_gql_seo_format_string($yoast_meta->open_graph_article_publisher),
                        'opengraphPublishedTime' => wp_gql_seo_format_string(
                            $yoast_meta->open_graph_article_published_time
                        ),
                        'opengraphModifiedTime' => wp_gql_seo_format_string(
                            $yoast_meta->open_graph_article_modified_time
                        ),
                        'opengraphDescription' => wp_gql_seo_format_string($yoast_meta->open_graph_description),
                        'opengraphImage' => $context
                            ->get_loader('post')
                            ->load_deferred(absint($meta['wpseo_opengraph-image-id'] ?? 0)),
                        'opengraphLocale' => wp_gql_seo_format_string($yoast_meta->open_graph_locale),
                        'opengraphFbAppId' => wp_gql_seo_format_string($yoast_meta->open_graph_fb_app_id),
                        'opengraphEnabled' => boolval($yoast_meta->open_graph_enabled),
                        'twitterCardType' => wp_gql_seo_format_string($yoast_meta->twitter_card),
                        'twitterTitle' => wp_gql_seo_format_string($yoast_meta->twitter_title),
                        'twitterDescription' => wp_gql_seo_format_string($yoast_meta->twitter_description),
                        'twitterImage' => $context
                            ->get_loader('post')
                            ->load_deferred(absint($meta['wpseo_twitter-image-id'] ?? 0)),
                        'twitterCreator' => wp_gql_seo_format_string($yoast_meta->twitter_creator),
                        'twitterSite' => wp_gql_seo_format_string($yoast_meta->twitter_site),
                        'canonical' => isset($yoast_meta->canonical)
                            ? wp_gql_seo_format_string($yoast_meta->canonical)
                            : null,
                        'breadcrumbs' => $yoast_meta->breadcrumbs,
                        'cornerstone' => boolval($yoast_meta->is_cornerstone),
                        'fullHead' => wp_gql_seo_get_full_head($yoast_meta),
                        'head' => wp_gql_seo_get_head_obj($yoast_meta),
                        'relNext' => wp_gql_seo_format_string($yoast_meta->rel_next),
                        'relPrev' => wp_gql_seo_format_string($yoast_meta->rel_prev),
                        'breadcrumbTitle' => wp_gql_seo_format_string(
                            wp_gql_seo_indexable_prop($indexable, 'breadcrumb_title')
                        ),
                        'objectPublishedAt' => wp_gql_seo_format_string(
                            wp_gql_seo_indexable_prop($indexable, 'object_published_at')
                        ),
                        'objectLastModified' => wp_gql_seo_format_string(
                            wp_gql_seo_indexable_prop($indexable, 'object_last_modified')
                        ),
                        'language' => wp_gql_seo_format_string(wp_gql_seo_indexable_prop($indexable, 'language')),
                        'region' => wp_gql_seo_format_string(wp_gql_seo_indexable_prop($indexable, 'region')),
                        'schema' => [
                            'raw' => wp_json_encode($schemaArray, JSON_UNESCAPED_SLASHES),
                        ],
                        'analysis' => wp_gql_seo_build_analysis($indexable),
                    ];

                    wp_reset_postdata();

                    return !empty($seo) ? $seo : null;
                },
            ]);
        }
    }

    // Post type connection edge fields for primary taxonomies
    $post_types = \WPGraphQL::get_allowed_post_types();
    if (!empty($post_types) && is_array($post_types)) {
        foreach ($post_types as $post_type) {
            $post_type_object = get_post_type_object($post_type);

            if (!isset($post_type_object->graphql_single_name)) {
                continue;
            }

            $taxonomiesPostObj = get_object_taxonomies($post_type, 'objects');

            $postNameKey = wp_gql_seo_get_field_key($post_type_object->graphql_single_name);

            foreach ($taxonomiesPostObj as $tax) {
                if (isset($tax->hierarchical) && isset($tax->graphql_single_name)) {
                    $name = ucfirst($postNameKey) . 'To' . ucfirst($tax->graphql_single_name) . 'ConnectionEdge';

                    register_graphql_field($name, 'isPrimary', [
                        'type' => 'Boolean',
                        'description' => sprintf(
                            // translators: %s is the taxonomy name.
                            __('The Yoast SEO Primary %s', 'wp-graphql-yoast-seo'),
                            $tax->name
                        ),
                        'resolve' => function ($item) use ($tax) {
                            $postId = $item['source']->ID;

                            $wpseo_primary_term = new WPSEO_Primary_Term($tax->name, $postId);
                            $primaryTaxId = $wpseo_primary_term->get_primary_term();
                            $termId = $item['node']->term_id;

                            return $primaryTaxId === $termId;
                        },
                    ]);
                }
            }
        }
    }
});
