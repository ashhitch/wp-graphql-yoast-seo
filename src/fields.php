<?php
use WPGraphQL\AppContext;

$post_types = \WPGraphQL::get_allowed_post_types();
$taxonomies = \WPGraphQL::get_allowed_taxonomies();

// If WooCommerce installed then add these post types and taxonomies
if (class_exists('\WooCommerce')) {
    array_push($post_types, 'product');
    array_push($taxonomies, 'productCategory');
}
register_graphql_field('RootQuery', 'seo', [
    'type' => 'SEOConfig',
    'description' => __('Returns seo site data', 'wp-graphql-yoast-seo'),
    'resolve' => function ($source, array $args, AppContext $context) use ($post_types) {
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

        $contentTypes = wp_gql_seo_build_content_type_data($post_types, $all);

        return [
            'contentTypes' => $contentTypes,
            'webmaster' => [
                'baiduVerify' => wp_gql_seo_format_string($all['baiduverify']),
                'googleVerify' => wp_gql_seo_format_string($all['googleverify']),
                'msVerify' => wp_gql_seo_format_string($all['msverify']),
                'yandexVerify' => wp_gql_seo_format_string($all['yandexverify']),
            ],
            'social' => [
                'facebook' => [
                    'url' => wp_gql_seo_format_string($all['facebook_site']),
                    'defaultImage' => $context->get_loader('post')->load_deferred($all['og_default_image_id']),
                ],
                'twitter' => [
                    'username' => wp_gql_seo_format_string($all['twitter_site']),
                    'cardType' => wp_gql_seo_format_string($all['twitter_card_type']),
                ],
                'instagram' => [
                    'url' => wp_gql_seo_format_string($all['instagram_url']),
                ],
                'linkedIn' => [
                    'url' => wp_gql_seo_format_string($all['linkedin_url']),
                ],
                'mySpace' => [
                    'url' => wp_gql_seo_format_string($all['myspace_url']),
                ],
                'pinterest' => [
                    'url' => wp_gql_seo_format_string($all['pinterest_url']),
                    'metaTag' => wp_gql_seo_format_string($all['pinterestverify']),
                ],
                'youTube' => [
                    'url' => wp_gql_seo_format_string($all['youtube_url']),
                ],
                'wikipedia' => [
                    'url' => wp_gql_seo_format_string($all['wikipedia_url']),
                ],
            ],
            'breadcrumbs' => [
                'enabled' => wp_gql_seo_format_string($all['breadcrumbs-enable']),
                'boldLast' => wp_gql_seo_format_string($all['breadcrumbs-boldlast']),
                'showBlogPage' => wp_gql_seo_format_string($all['breadcrumbs-display-blog-page']),
                'archivePrefix' => wp_gql_seo_format_string($all['breadcrumbs-archiveprefix']),
                'prefix' => wp_gql_seo_format_string($all['breadcrumbs-prefix']),
                'notFoundText' => wp_gql_seo_format_string($all['breadcrumbs-404crumb']),
                'homeText' => wp_gql_seo_format_string($all['breadcrumbs-home']),
                'searchPrefix' => wp_gql_seo_format_string($all['breadcrumbs-searchprefix']),
                'separator' => wp_gql_seo_format_string($all['breadcrumbs-sep']),
            ],
            'schema' => [
                'companyName' => wp_gql_seo_format_string($all['company_name']),
                'personName' => !empty($user) ? wp_gql_seo_format_string($user->user_nicename) : null,
                'companyLogo' => $context->get_loader('post')->load_deferred(absint($all['company_logo_id'])),
                'personLogo' => $context->get_loader('post')->load_deferred(absint($all['person_logo_id'])),
                'logo' => $context
                    ->get_loader('post')
                    ->load_deferred(
                        $all['company_or_person'] === 'company'
                            ? absint($all['company_logo_id'])
                            : absint($all['person_logo_id'])
                    ),
                'companyOrPerson' => wp_gql_seo_format_string($all['company_or_person']),
                'siteName' => wp_gql_seo_format_string(YoastSEO()->helpers->site->get_site_name()),
                'wordpressSiteName' => wp_gql_seo_format_string(get_bloginfo('name')),
                'siteUrl' => wp_gql_seo_format_string(apply_filters('wp_gql_seo_site_url', get_site_url())),
                'homeUrl' => wp_gql_seo_format_string(apply_filters('wp_gql_seo_home_url', get_home_url())),
                'inLanguage' => wp_gql_seo_format_string(get_bloginfo('language')),
            ],
            'redirects' => array_map($mappedRedirects, $redirects),
            'openGraph' => [
                'defaultImage' => $context->get_loader('post')->load_deferred(absint($all['og_default_image_id'])),
                'frontPage' => [
                    'title' => wp_gql_seo_format_string(wp_gql_seo_replace_vars($all['open_graph_frontpage_title'])),
                    'description' => wp_gql_seo_format_string(
                        wp_gql_seo_replace_vars($all['open_graph_frontpage_desc'])
                    ),
                    'image' => $context
                        ->get_loader('post')
                        ->load_deferred(absint($all['open_graph_frontpage_image_id'])),
                ],
            ],
        ];
    },
]);

function get_post_type_graphql_fields($post, array $args, AppContext $context)
{
    // Base array
    $seo = [];

    $map = [
        '@id' => 'id',
        '@type' => 'type',
        '@graph' => 'graph',
        '@context' => 'context',
    ];
    $meta = YoastSEO()->meta->for_post($post->ID);

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
        'opengraphImage' => function () use ($post, $context, $meta) {
            $id = wp_gql_seo_get_og_image($meta !== false ? $meta->open_graph_images : []);

            return $context->get_loader('post')->load_deferred(absint($id));
        },
        'twitterCardType' => wp_gql_seo_format_string($meta !== false ? $meta->twitter_card : ''),
        'twitterTitle' => wp_gql_seo_format_string($meta !== false ? $meta->twitter_title : ''),
        'twitterDescription' => wp_gql_seo_format_string($meta !== false ? $meta->twitter_description : ''),
        'twitterImage' => function () use ($post, $context, $meta) {
            $twitter_image = $meta->twitter_image;

            if (empty($twitter_image)) {
                return __return_empty_string();
            }

            $id = wpcom_vip_attachment_url_to_postid($twitter_image);

            return $context->get_loader('post')->load_deferred(absint($id));
        },
        'canonical' => wp_gql_seo_format_string($meta !== false ? $meta->canonical : ''),
        'readingTime' => floatval($meta !== false ? $meta->estimated_reading_time_minutes : ''),
        'breadcrumbs' => $meta !== false ? $meta->breadcrumbs : [],
        // TODO: Default should be true or false?
        'cornerstone' => boolval($meta !== false ? $meta->indexable->is_cornerstone : false),
        'fullHead' => wp_gql_seo_get_full_head($meta),
        'schema' => [
            'pageType' => $meta !== false && is_array($meta->schema_page_type) ? $meta->schema_page_type : [],
            'articleType' => $meta !== false && is_array($meta->schema_article_type) ? $meta->schema_article_type : [],
            'raw' => json_encode($schemaArray, JSON_UNESCAPED_SLASHES),
        ],
    ];

    return !empty($seo) ? $seo : null;
}

register_graphql_field('ContentNode', 'seo', [
    'type' => 'PostTypeSEO',
    'description' => __('The Yoast SEO data of the ContentNode', 'wp-graphql-yoast-seo'),
    'resolve' => function ($post, array $args, AppContext $context) {
        return get_post_type_graphql_fields($post, $args, $context);
    },
]);
register_graphql_field('NodeWithTitle', 'seo', [
    'type' => 'PostTypeSEO',
    'description' => __('The Yoast SEO data of the ContentNode', 'wp-graphql-yoast-seo'),
    'resolve' => function ($post, array $args, AppContext $context) {
        return get_post_type_graphql_fields($post, $args, $context);
    },
]);

// TODO connect to content node
// Post Type SEO Data
if (!empty($post_types) && is_array($post_types)) {
    foreach ($post_types as $post_type) {
        $post_type_object = get_post_type_object($post_type);

        if (isset($post_type_object->graphql_single_name)):
            // register field on edge for arch

            $name = 'WP' . ucfirst($post_type_object->graphql_single_name) . 'Info';

            // Loop each taxonomy to register on the edge if a category is the primary one.
            $taxonomiesPostObj = get_object_taxonomies($post_type, 'objects');

            $postNameKey = wp_gql_seo_get_field_key($post_type_object->graphql_single_name);

            foreach ($taxonomiesPostObj as $tax) {
                if (isset($tax->hierarchical) && isset($tax->graphql_single_name)) {
                    $name = ucfirst($postNameKey) . 'To' . ucfirst($tax->graphql_single_name) . 'ConnectionEdge';

                    register_graphql_field($name, 'isPrimary', [
                        'type' => 'Boolean',
                        'description' => __('The Yoast SEO Primary ' . $tax->name, 'wp-graphql-yoast-seo'),
                        'resolve' => function ($item, array $args, AppContext $context) use ($tax) {
                            $postId = $item['source']->ID;

                            $wpseo_primary_term = new WPSEO_Primary_Term($tax->name, $postId);
                            $primaryTaxId = $wpseo_primary_term->get_primary_term();
                            $termId = $item['node']->term_id;

                            return $primaryTaxId === $termId;
                        },
                    ]);
                }
            }
        endif;
    }
}

// User SEO Data
register_graphql_field('User', 'seo', [
    'type' => 'SEOUser',
    'description' => __('The Yoast SEO data of a user', 'wp-graphql-yoast-seo'),
    'resolve' => function ($user, array $args, AppContext $context) {
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
            'fullHead' => is_string(
                YoastSEO()
                    ->meta->for_author($user->userId)
                    ->get_head()
            )
                ? YoastSEO()
                    ->meta->for_author($user->userId)
                    ->get_head()
                : YoastSEO()
                    ->meta->for_author($user->userId)
                    ->get_head()->html,
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

// Taxonomy SEO Data
if (!empty($taxonomies) && is_array($taxonomies)) {
    foreach ($taxonomies as $tax) {
        $taxonomy = get_taxonomy($tax);

        if (empty($taxonomy) || !isset($taxonomy->graphql_single_name)) {
            return;
        }

        register_graphql_field($taxonomy->graphql_single_name, 'seo', [
            'type' => 'TaxonomySEO',
            'description' => __('The Yoast SEO data of the ' . $taxonomy->label . ' taxonomy.', 'wp-graphql-yoast-seo'),
            'resolve' => function ($term, array $args, AppContext $context) {
                $term_obj = get_term($term->term_id);

                $meta = WPSEO_Taxonomy_Meta::get_term_meta((int) $term_obj->term_id, $term_obj->taxonomy);
                $robots = YoastSEO()->meta->for_term($term->term_id)->robots;

                $schemaArray = YoastSEO()->meta->for_term($term->term_id)->schema;

                // Get data
                $seo = [
                    'title' => wp_gql_seo_format_string(
                        html_entity_decode(wp_strip_all_tags(YoastSEO()->meta->for_term($term->term_id)->title))
                    ),
                    'metaDesc' => wp_gql_seo_format_string(YoastSEO()->meta->for_term($term->term_id)->description),
                    'focuskw' => isset($meta['wpseo_focuskw'])
                        ? wp_gql_seo_format_string($meta['wpseo_focuskw'])
                        : $meta['wpseo_focuskw'],
                    'metaKeywords' => isset($meta['wpseo_metakeywords'])
                        ? wp_gql_seo_format_string($meta['wpseo_metakeywords'])
                        : null,
                    'metaRobotsNoindex' => $robots['index'],
                    'metaRobotsNofollow' => $robots['follow'],
                    'opengraphTitle' => wp_gql_seo_format_string(
                        YoastSEO()->meta->for_term($term->term_id)->open_graph_title
                    ),
                    'opengraphUrl' => wp_gql_seo_format_string(
                        YoastSEO()->meta->for_term($term->term_id)->open_graph_url
                    ),
                    'opengraphSiteName' => wp_gql_seo_format_string(
                        YoastSEO()->meta->for_term($term->term_id)->open_graph_site_name
                    ),
                    'opengraphType' => wp_gql_seo_format_string(
                        YoastSEO()->meta->for_term($term->term_id)->open_graph_type
                    ),
                    'opengraphAuthor' => wp_gql_seo_format_string(
                        YoastSEO()->meta->for_term($term->term_id)->open_graph_article_author
                    ),
                    'opengraphPublisher' => wp_gql_seo_format_string(
                        YoastSEO()->meta->for_term($term->term_id)->open_graph_article_publisher
                    ),
                    'opengraphPublishedTime' => wp_gql_seo_format_string(
                        YoastSEO()->meta->for_term($term->term_id)->open_graph_article_published_time
                    ),
                    'opengraphModifiedTime' => wp_gql_seo_format_string(
                        YoastSEO()->meta->for_term($term->term_id)->open_graph_article_modified_time
                    ),
                    'opengraphDescription' => wp_gql_seo_format_string(
                        YoastSEO()->meta->for_term($term->term_id)->open_graph_description
                    ),
                    'opengraphImage' => $context
                        ->get_loader('post')
                        ->load_deferred(absint($meta['wpseo_opengraph-image-id'])),
                    'twitterCardType' => wp_gql_seo_format_string(
                        YoastSEO()->meta->for_term($term->term_id)->twitter_card
                    ),
                    'twitterTitle' => wp_gql_seo_format_string(
                        YoastSEO()->meta->for_term($term->term_id)->twitter_title
                    ),
                    'twitterDescription' => wp_gql_seo_format_string(
                        YoastSEO()->meta->for_term($term->term_id)->twitter_description
                    ),
                    'twitterImage' => $context
                        ->get_loader('post')
                        ->load_deferred(absint($meta['wpseo_twitter-image-id'])),
                    'canonical' => isset(YoastSEO()->meta->for_term($term->term_id)->canonical)
                        ? wp_gql_seo_format_string(YoastSEO()->meta->for_term($term->term_id)->canonical)
                        : null,
                    'breadcrumbs' => YoastSEO()->meta->for_term($term->term_id)->breadcrumbs,
                    'cornerstone' => boolval(YoastSEO()->meta->for_term($term->term_id)->is_cornerstone),
                    'fullHead' => is_string(
                        YoastSEO()
                            ->meta->for_term($term->term_id)
                            ->get_head()
                    )
                        ? YoastSEO()
                            ->meta->for_term($term->term_id)
                            ->get_head()
                        : YoastSEO()
                            ->meta->for_term($term->term_id)
                            ->get_head()->html,
                    'schema' => [
                        'raw' => json_encode($schemaArray, JSON_UNESCAPED_SLASHES),
                    ],
                ];
                wp_reset_query();

                return !empty($seo) ? $seo : null;
            },
        ]);
    }
}
