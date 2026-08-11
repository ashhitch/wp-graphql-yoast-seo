<?php
/**
 * Root query resolvers
 *
 * @package WP_Graphql_YOAST_SEO
 */

if (!defined('ABSPATH')) {
    exit();
}

use WPGraphQL\AppContext;

/**
 * Register the SEO field on the RootQuery
 */
add_action('graphql_register_types', function () {
    register_graphql_field('RootQuery', 'seo', [
        'type' => 'SEOConfig',
        'description' => __('Returns seo site data', 'wp-graphql-yoast-seo'),
        'resolve' => function ($source, array $args, AppContext $context) {
            $post_types = \WPGraphQL::get_allowed_post_types();
            $taxonomies = \WPGraphQL::get_allowed_taxonomies();

            $wpseo_options = WPSEO_Options::get_instance();
            $all = $wpseo_options->get_all();
            $redirectsObj = class_exists('WPSEO_Redirect_Option') ? new WPSEO_Redirect_Option() : false;
            $redirects = $redirectsObj ? $redirectsObj->get_from_option() : [];

            $userID = !empty($all['company_or_person_user_id']) ? $all['company_or_person_user_id'] : null;
            $user = !empty($userID) ? get_userdata($userID) : null;

            $mappedRedirects = function ($value) {
                return [
                    'origin' => $value['origin'],
                    'target' => $value['url'],
                    'type' => $value['type'],
                    'format' => $value['format'],
                ];
            };

            $contentTypes = wp_gql_seo_build_content_type_data($post_types, $all, $context);
            $taxonomyTypes = wp_gql_seo_build_taxonomy_data($taxonomies, $all, $context);

            $homepage = [
                'title' => wp_gql_seo_format_string(wp_gql_seo_replace_vars($all['title-home-wpseo'] ?? null)),
                'description' => wp_gql_seo_format_string(wp_gql_seo_replace_vars($all['metadesc-home-wpseo'] ?? null)),
            ];
            $author = [
                'title' => wp_gql_seo_format_string(wp_gql_seo_replace_vars($all['title-author-wpseo'] ?? null)),
                'description' => wp_gql_seo_format_string(
                    wp_gql_seo_replace_vars($all['metadesc-author-wpseo'] ?? null)
                ),
                'noindex' => boolval($all['noindex-author-wpseo'] ?? false),
                'noindexNoPosts' => boolval($all['noindex-author-noposts-wpseo'] ?? false),
                'social' => wp_gql_seo_get_premium_social('author-wpseo', $all, $context),
            ];
            $date = [
                'title' => wp_gql_seo_format_string(wp_gql_seo_replace_vars($all['title-archive-wpseo'] ?? null)),
                'description' => wp_gql_seo_format_string(
                    wp_gql_seo_replace_vars($all['metadesc-archive-wpseo'] ?? null)
                ),
                'noindex' => boolval($all['noindex-archive-wpseo'] ?? false),
                'social' => wp_gql_seo_get_premium_social('archive-wpseo', $all, $context),
            ];
            $search = [
                'title' => wp_gql_seo_format_string(wp_gql_seo_replace_vars($all['title-search-wpseo'] ?? null)),
            ];
            $config = [
                'separator' => wp_gql_seo_format_string($all['separator'] ?? null),
            ];
            $notFound = [
                'title' => wp_gql_seo_format_string(wp_gql_seo_replace_vars($all['title-404-wpseo'] ?? null)),
                'breadcrumb' => wp_gql_seo_format_string(wp_gql_seo_replace_vars($all['breadcrumbs-404crumb'] ?? null)),
            ];

            return [
                'contentTypes' => $contentTypes,
                'taxonomyArchives' => $taxonomyTypes,
                'meta' => [
                    'homepage' => $homepage,
                    'author' => $author,
                    'date' => $date,
                    'search' => $search,
                    'config' => $config,
                    'notFound' => $notFound,
                ],
                'webmaster' => [
                    'baiduVerify' => wp_gql_seo_format_string($all['baiduverify'] ?? null),
                    'googleVerify' => wp_gql_seo_format_string($all['googleverify'] ?? null),
                    'msVerify' => wp_gql_seo_format_string($all['msverify'] ?? null),
                    'yandexVerify' => wp_gql_seo_format_string($all['yandexverify'] ?? null),
                ],
                'social' => [
                    'facebook' => [
                        'url' => wp_gql_seo_format_string($all['facebook_site'] ?? null),
                        'defaultImage' => $context
                            ->get_loader('post')
                            ->load_deferred(absint($all['og_default_image_id'] ?? 0)),
                    ],
                    'twitter' => [
                        'username' => wp_gql_seo_format_string($all['twitter_site'] ?? null),
                        'cardType' => wp_gql_seo_format_string($all['twitter_card_type'] ?? null),
                        'enabled' => boolval($all['twitter'] ?? false),
                    ],
                    'instagram' => [
                        'url' => wp_gql_seo_format_string($all['instagram_url'] ?? null),
                    ],
                    'linkedIn' => [
                        'url' => wp_gql_seo_format_string($all['linkedin_url'] ?? null),
                    ],
                    'mastodon' => [
                        'url' => wp_gql_seo_format_string($all['mastodon_url'] ?? null),
                    ],
                    'mySpace' => [
                        'url' => wp_gql_seo_format_string($all['myspace_url'] ?? null),
                    ],
                    'pinterest' => [
                        'url' => wp_gql_seo_format_string($all['pinterest_url'] ?? null),
                        'metaTag' => wp_gql_seo_format_string($all['pinterestverify'] ?? null),
                    ],
                    'youTube' => [
                        'url' => wp_gql_seo_format_string($all['youtube_url'] ?? null),
                    ],
                    'wikipedia' => [
                        'url' => wp_gql_seo_format_string($all['wikipedia_url'] ?? null),
                    ],
                    'otherSocials' => !empty($all['other_social_urls']) ? $all['other_social_urls'] : [],
                ],
                'breadcrumbs' => [
                    'enabled' => boolval($all['breadcrumbs-enable'] ?? false),
                    'boldLast' => boolval($all['breadcrumbs-boldlast'] ?? false),
                    'showBlogPage' => boolval($all['breadcrumbs-display-blog-page'] ?? false),
                    'archivePrefix' => wp_gql_seo_format_string($all['breadcrumbs-archiveprefix'] ?? null),
                    'prefix' => wp_gql_seo_format_string($all['breadcrumbs-prefix'] ?? null),
                    'notFoundText' => wp_gql_seo_format_string($all['breadcrumbs-404crumb'] ?? null),
                    'homeText' => wp_gql_seo_format_string($all['breadcrumbs-home'] ?? null),
                    'searchPrefix' => wp_gql_seo_format_string($all['breadcrumbs-searchprefix'] ?? null),
                    'separator' => wp_gql_seo_format_string($all['breadcrumbs-sep'] ?? null),
                ],
                'schema' => [
                    'companyName' => wp_gql_seo_format_string($all['company_name'] ?? null),
                    'personName' => !empty($user) ? wp_gql_seo_format_string($user->user_nicename) : null,
                    'companyLogo' => $context->get_loader('post')->load_deferred(absint($all['company_logo_id'] ?? 0)),
                    'personLogo' => $context->get_loader('post')->load_deferred(absint($all['person_logo_id'] ?? 0)),
                    'logo' => $context
                        ->get_loader('post')
                        ->load_deferred(
                            $all['company_or_person'] === 'company'
                                ? absint($all['company_logo_id'] ?? 0)
                                : absint($all['person_logo_id'] ?? 0)
                        ),
                    'companyOrPerson' => wp_gql_seo_format_string($all['company_or_person'] ?? null),
                    'siteName' => wp_gql_seo_format_string(YoastSEO()->helpers->site->get_site_name()),
                    'wordpressSiteName' => wp_gql_seo_format_string(get_bloginfo('name')),
                    'siteUrl' => wp_gql_seo_format_string(apply_filters('wp_gql_seo_site_url', get_site_url())),
                    'homeUrl' => wp_gql_seo_format_string(apply_filters('wp_gql_seo_home_url', get_home_url())),
                    'inLanguage' => wp_gql_seo_format_string(get_bloginfo('language')),
                ],
                'redirects' => array_map($mappedRedirects, $redirects),
                'openGraph' => [
                    'defaultImage' => $context
                        ->get_loader('post')
                        ->load_deferred(absint($all['og_default_image_id'] ?? 0)),
                    'frontPage' => [
                        'title' => wp_gql_seo_format_string(
                            wp_gql_seo_replace_vars($all['open_graph_frontpage_title'] ?? null)
                        ),
                        'description' => wp_gql_seo_format_string(
                            wp_gql_seo_replace_vars($all['open_graph_frontpage_desc'] ?? null)
                        ),
                        'image' => $context
                            ->get_loader('post')
                            ->load_deferred(absint($all['open_graph_frontpage_image_id'] ?? 0)),
                    ],
                    'enabled' => boolval($all['opengraph'] ?? false),
                ],
                'advanced' => [
                    'siteType' => wp_gql_seo_format_string($all['site_type'] ?? null),
                    'environmentType' => wp_gql_seo_format_string($all['environment_type'] ?? null),
                    'hasMultipleAuthors' => isset($all['has_multiple_authors'])
                        ? boolval($all['has_multiple_authors'])
                        : null,
                    'xmlSitemapEnabled' => boolval($all['enable_xml_sitemap'] ?? false),
                    'indexNowEnabled' => boolval($all['enable_index_now'] ?? false),
                    'indexNowKey' => wp_gql_seo_format_string($all['index_now_key'] ?? null),
                    'contentAnalysisActive' => boolval($all['content_analysis_active'] ?? false),
                    'keywordAnalysisActive' => boolval($all['keyword_analysis_active'] ?? false),
                    'inclusiveLanguageAnalysisActive' => boolval($all['inclusive_language_analysis_active'] ?? false),
                ],
            ];
        },
    ]);

    // Register post type page info schema fields
    $post_types = \WPGraphQL::get_allowed_post_types();
    if (!empty($post_types) && is_array($post_types)) {
        foreach ($post_types as $post_type) {
            $post_type_object = get_post_type_object($post_type);

            if (isset($post_type_object->graphql_single_name)):
                // register field on edge for archive
                $name = 'WP' . ucfirst($post_type_object->graphql_single_name) . 'Info';

                register_graphql_field($name, 'seo', [
                    'type' => 'SEOPostTypePageInfo',
                    'description' => sprintf(
                        // translators: %s is the post type singular name.
                        __('Raw schema for %s', 'wp-graphql-yoast-seo'),
                        $post_type_object->graphql_single_name
                    ),
                    'resolve' => function () use ($post_type) {
                        $meta = YoastSEO()->meta->for_post_type_archive($post_type);
                        $schemaArray = $meta !== false ? $meta->schema : [];

                        return [
                            'schema' => [
                                'raw' => wp_json_encode($schemaArray, JSON_UNESCAPED_SLASHES),
                            ],
                        ];
                    },
                ]);
            endif;
        }
    }
});
