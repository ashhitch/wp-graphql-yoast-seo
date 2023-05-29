<?php // phpcs:ignore

/**
 * Plugin Name:     Add WPGraphQL SEO
 * Plugin URI:      https://github.com/ashhitch/wp-graphql-yoast-seo
 * Description:     A WPGraphQL Extension that adds support for Yoast SEO
 * Author:          Ash Hitchcock
 * Author URI:      https://www.ashleyhitchcock.com
 * Text Domain:     wp-graphql-yoast-seo
 * Domain Path:     /languages
 * Version:         4.22.4
 *
 * @package         WP_Graphql_YOAST_SEO
 */

if (!defined('ABSPATH')) {
    exit();
}

use WPGraphQL\AppContext;
use WPGraphQL\Model\Term;

add_action('admin_init', function () {
    $core_dependencies = [
        'WPGraphQL plugin' => class_exists('WPGraphQL'),
        'Yoast SEO' => function_exists('YoastSEO'),
    ];

    $missing_dependencies = array_keys(array_diff($core_dependencies, array_filter($core_dependencies)));
    $display_admin_notice = static function () use ($missing_dependencies) {
        ?>
            <div class="notice notice-error">
              <p><?php esc_html_e(
                  'The WPGraphQL Yoast SEO plugin can\'t be loaded because these dependencies are missing:',
                  'wp-graphql-yoast-seo'
              ); ?>
              </p>
              <ul>
                <?php foreach ($missing_dependencies as $missing_dependency): ?>
                  <li><?php echo esc_html($missing_dependency); ?></li>
                <?php endforeach; ?>
              </ul>
            </div>
            <?php
    };

    if (!empty($missing_dependencies)) {
        add_action('network_admin_notices', $display_admin_notice);
        add_action('admin_notices', $display_admin_notice);

        return;
    }
});

add_action('graphql_init', function () {
    if (!function_exists('wp_gql_seo_format_string')) {
        function wp_gql_seo_format_string($string)
        {
            return isset($string) ? html_entity_decode(trim($string)) : null;
        }
    }

    if (!function_exists('wp_gql_seo_replace_vars')) {
        function wp_gql_seo_replace_vars($string)
        {
            // Get all the post types that have been registered.
            $post_types = get_post_types();
            // Get all the taxonomies that have been registered.
            $taxomonies = get_taxonomies();
            // Merge them together and pass them through.
            $objects = array_merge($post_types, $taxomonies);
            return isset($string) ? wpseo_replace_vars($string, $objects) : null;
        }
    }
    if (!function_exists('wp_gql_seo_get_og_image')) {
        function wp_gql_seo_get_og_image($images)
        {
            if (empty($images)) {
                return __return_empty_string();
            }

            $image = reset($images);

            if (empty($image)) {
                return __return_empty_string();
            }

            if (!isset($image['url'])) {
                return __return_empty_string();
            }
            // Remove image sizes from url
            $url = preg_replace('/(.*)-\d+x\d+\.(jpg|png|gif)$/', '$1.$2', $image['url']);
            // If the image is equal as the original and the original has an id, return this ID
            if (isset($image['id']) && $url === $image['url']) {
                return $image['id'];
            }
            return wpcom_vip_attachment_url_to_postid($url);
        }
    }
    if (!function_exists('wp_gql_seo_get_field_key')) {
        function wp_gql_seo_get_field_key($field_key)
        {
            $field_key = lcfirst(preg_replace('[^a-zA-Z0-9 -]', ' ', $field_key));
            $field_key = lcfirst(str_replace('_', ' ', ucwords($field_key, '_')));
            $field_key = lcfirst(str_replace('-', ' ', ucwords($field_key, '_')));
            $field_key = lcfirst(str_replace(' ', '', ucwords($field_key, ' ')));

            return $field_key;
        }
    }

    if (!function_exists('wpcom_vip_attachment_url_to_postid')) {
        function wpcom_vip_attachment_cache_key($url)
        {
            return 'wpcom_vip_attachment_url_post_id_' . md5($url);
        }
    }

    if (!function_exists('wpcom_vip_attachment_url_to_postid')) {
        function wpcom_vip_attachment_url_to_postid($url)
        {
            $cache_key = wpcom_vip_attachment_cache_key($url);
            $id = wp_cache_get($cache_key);
            if (false === $id) {
                $id = attachment_url_to_postid($url); // phpcs:ignore
                if (empty($id)) {
                    wp_cache_set(
                        $cache_key,
                        'not_found',
                        'default',
                        12 * HOUR_IN_SECONDS + mt_rand(0, 4 * HOUR_IN_SECONDS) // phpcs:ignore
                    );
                    $id = null; // Set $id to null instead of false
                } else {
                    wp_cache_set(
                        $cache_key,
                        $id,
                        'default',
                        24 * HOUR_IN_SECONDS + mt_rand(0, 12 * HOUR_IN_SECONDS) // phpcs:ignore
                    );
                }
            } elseif ('not_found' === $id) {
                return null; // Return null instead of false
            }

            return $id;
        }
    }

    function wp_gql_seo_build_content_types($types)
    {
        $carry = [];
        foreach ($types as $type) {
            $post_type_object = get_post_type_object($type);
            if ($post_type_object->graphql_single_name) {
                $carry[wp_gql_seo_get_field_key($post_type_object->graphql_single_name)] = ['type' => 'SEOContentType'];
            }
        }
        return $carry;
    }

    /**
     * @param \Yoast\WP\SEO\Surfaces\Values\Meta|bool $metaForPost
     * @return string
     */
    function wp_gql_seo_get_full_head($metaForPost)
    {
        if ($metaForPost !== false) {
            $head = $metaForPost->get_head();

            return is_string($head) ? $head : $head->html;
        }

        return '';
    }

    function wp_gql_seo_build_content_type_data($types, $all)
    {
        $carry = [];
        foreach ($types as $type) {
            $post_type_object = get_post_type_object($type);

            if ($post_type_object->graphql_single_name) {
                $tag = wp_gql_seo_get_field_key($post_type_object->graphql_single_name);

                $meta = YoastSEO()->meta->for_post_type_archive($type);

                if (empty($meta)) {
                    continue;
                }

                $carry[$tag] = [
                    'title' => !empty($all['title-' . $type])
                        ? wp_gql_seo_format_string(wp_gql_seo_replace_vars($all['title-' . $type]))
                        : null,
                    'metaDesc' => !empty($all['metadesc-' . $type])
                        ? wp_gql_seo_format_string(wp_gql_seo_replace_vars($all['metadesc-' . $type]))
                        : null,
                    'metaRobotsNoindex' => !empty($all['noindex-' . $type]) ? boolval($all['noindex-' . $type]) : false,
                    'schemaType' => !empty($all['schema-page-type-' . $type])
                        ? $all['schema-page-type-' . $type]
                        : null,

                    'schema' => [
                        'raw' => !empty($meta->schema) ? json_encode($meta->schema, JSON_UNESCAPED_SLASHES) : null,
                    ],
                    'archive' => [
                        'hasArchive' => boolval($post_type_object->has_archive),
                        'archiveLink' => apply_filters(
                            'wp_gql_seo_archive_link',
                            get_post_type_archive_link($type),
                            $type
                        ),
                        'title' => !empty($meta->title) ? wp_gql_seo_format_string($meta->title) : null,
                        'metaDesc' => !empty($all['metadesc-ptarchive-' . $type])
                            ? wp_gql_seo_format_string($all['metadesc-ptarchive-' . $type])
                            : null,
                        'metaRobotsNoindex' =>
                            !empty($meta->robots['index']) && $meta->robots['index'] === 'index' ? false : true,
                        'metaRobotsNofollow' =>
                            !empty($meta->robots['follow']) && $meta->robots['follow'] === 'follow' ? false : true,
                        'metaRobotsIndex' => !empty($meta->robots['index']) ? $meta->robots['index'] : 'noindex',
                        'metaRobotsFollow' => !empty($meta->robots['follow']) ? $meta->robots['follow'] : 'nofollow',
                        'breadcrumbTitle' => !empty($all['bctitle-ptarchive-' . $type])
                            ? wp_gql_seo_format_string($all['bctitle-ptarchive-' . $type])
                            : null,
                        'fullHead' => is_string($meta->get_head()) ? $meta->get_head() : $meta->get_head()->html,
                    ],
                ];
            }
        }
        return $carry;
    }

    add_action('graphql_register_types', function () {
        $post_types = \WPGraphQL::get_allowed_post_types();
        $taxonomies = \WPGraphQL::get_allowed_taxonomies();

        // If WooCommerce installed then add these post types and taxonomies
        if (class_exists('\WooCommerce')) {
            array_push($post_types, 'product');
            array_push($taxonomies, 'productCategory');
        }

        register_graphql_enum_type('SEOCardType', [
            'description' => __('Types of cards', 'wp-graphql-yoast-seo'),
            'values' => [
                'summary_large_image' => [
                    'value' => 'summary_large_image',
                ],
                'summary' => [
                    'value' => 'summary',
                ],
            ],
        ]);

        register_graphql_object_type('SEOPostTypeSchema', [
            'description' => __('The Schema types', 'wp-graphql-yoast-seo'),
            'fields' => [
                'pageType' => ['type' => ['list_of' => 'String']],
                'articleType' => ['type' => ['list_of' => 'String']],
                'raw' => ['type' => 'String'],
            ],
        ]);
        register_graphql_object_type('SEOTaxonomySchema', [
            'description' => __('The Schema types for Taxonomy', 'wp-graphql-yoast-seo'),
            'fields' => [
                'raw' => ['type' => 'String'],
            ],
        ]);

        $baseSEOFields = [
            'title' => ['type' => 'String'],
            'metaDesc' => ['type' => 'String'],
            'focuskw' => ['type' => 'String'],
            'metaKeywords' => ['type' => 'String'],
            'metaRobotsNoindex' => ['type' => 'String'],
            'metaRobotsNofollow' => ['type' => 'String'],
            'opengraphTitle' => ['type' => 'String'],
            'opengraphUrl' => ['type' => 'String'],
            'opengraphSiteName' => ['type' => 'String'],
            'opengraphType' => ['type' => 'String'],
            'opengraphAuthor' => ['type' => 'String'],
            'opengraphPublisher' => ['type' => 'String'],
            'opengraphPublishedTime' => ['type' => 'String'],
            'opengraphModifiedTime' => ['type' => 'String'],
            'opengraphDescription' => ['type' => 'String'],
            'opengraphImage' => ['type' => 'MediaItem'],
            'twitterTitle' => ['type' => 'String'],
            'twitterDescription' => ['type' => 'String'],
            'twitterImage' => ['type' => 'MediaItem'],
            'canonical' => ['type' => 'String'],
            'breadcrumbs' => ['type' => ['list_of' => 'SEOPostTypeBreadcrumbs']],
            'cornerstone' => ['type' => 'Boolean'],
            'fullHead' => ['type' => 'String'],
        ];

        register_graphql_object_type('TaxonomySEO', [
            'fields' => array_merge($baseSEOFields, [
                'schema' => ['type' => 'SEOTaxonomySchema'],
            ]),
        ]);

        register_graphql_object_type('PostTypeSEO', [
            'fields' => array_merge($baseSEOFields, [
                'readingTime' => ['type' => 'Float'],
                'schema' => ['type' => 'SEOPostTypeSchema'],
            ]),
        ]);

        register_graphql_object_type('SEOPostTypeBreadcrumbs', [
            'fields' => [
                'url' => ['type' => 'String'],
                'text' => ['type' => 'String'],
            ],
        ]);

        register_graphql_object_type('SEOGlobalMetaHome', [
            'description' => __('The Yoast SEO homepage data', 'wp-graphql-yoast-seo'),
            'fields' => [
                'title' => ['type' => 'String'],
                'description' => ['type' => 'String'],
            ],
        ]);
        register_graphql_object_type('SEOGlobalMetaAuthor', [
            'description' => __('The Yoast SEO Author data', 'wp-graphql-yoast-seo'),
            'fields' => [
                'title' => ['type' => 'String'],
                'description' => ['type' => 'String'],
            ],
        ]);
        register_graphql_object_type('SEOGlobalMetaDate', [
            'description' => __('The Yoast SEO Date data', 'wp-graphql-yoast-seo'),
            'fields' => [
                'title' => ['type' => 'String'],
                'description' => ['type' => 'String'],
            ],
        ]);
        register_graphql_object_type('SEOGlobalMetaConfig', [
            'description' => __('The Yoast SEO meta config data', 'wp-graphql-yoast-seo'),
            'fields' => [
                'separator' => ['type' => 'String'],
            ],
        ]);
        register_graphql_object_type('SEOGlobalMeta404', [
            'description' => __('The Yoast SEO meta 404 data', 'wp-graphql-yoast-seo'),
            'fields' => [
                'title' => ['type' => 'String'],
                'breadcrumb' => ['type' => 'String'],
            ],
        ]);
        register_graphql_object_type('SEOGlobalMeta', [
            'description' => __('The Yoast SEO meta data', 'wp-graphql-yoast-seo'),
            'fields' => [
                'homepage' => ['type' => 'SEOGlobalMetaHome'],
                'author' => ['type' => 'SEOGlobalMetaAuthor'],
                'date' => ['type' => 'SEOGlobalMetaDate'],
                'config' => ['type' => 'SEOGlobalMetaConfig'],
                'notFound' => ['type' => 'SEOGlobalMeta404'],
            ],
        ]);

        register_graphql_object_type('SEOSchema', [
            'description' => __('The Yoast SEO schema data', 'wp-graphql-yoast-seo'),
            'fields' => [
                'companyName' => ['type' => 'String'],
                'personName' => ['type' => 'String'],
                'companyOrPerson' => ['type' => 'String'],
                'companyLogo' => ['type' => 'MediaItem'],
                'personLogo' => ['type' => 'MediaItem'],
                'logo' => ['type' => 'MediaItem'],
                'siteName' => ['type' => 'String'],
                'wordpressSiteName' => ['type' => 'String'],
                'siteUrl' => ['type' => 'String'],
                'homeUrl' => ['type' => 'String'],
                'inLanguage' => ['type' => 'String'],
            ],
        ]);

        register_graphql_object_type('SEOWebmaster', [
            'description' => __('The Yoast SEO  webmaster fields', 'wp-graphql-yoast-seo'),
            'fields' => [
                'baiduVerify' => ['type' => 'String'],
                'googleVerify' => ['type' => 'String'],
                'msVerify' => ['type' => 'String'],
                'yandexVerify' => ['type' => 'String'],
            ],
        ]);

        register_graphql_object_type('SEOBreadcrumbs', [
            'description' => __('The Yoast SEO breadcrumb config', 'wp-graphql-yoast-seo'),
            'fields' => [
                'enabled' => ['type' => 'Boolean'],
                'boldLast' => ['type' => 'Boolean'],
                'showBlogPage' => ['type' => 'Boolean'],
                'notFoundText' => ['type' => 'String'],
                'archivePrefix' => ['type' => 'String'],
                'homeText' => ['type' => 'String'],
                'prefix' => ['type' => 'String'],
                'searchPrefix' => ['type' => 'String'],
                'separator' => ['type' => 'String'],
            ],
        ]);

        register_graphql_object_type('SEOSocialFacebook', [
            'fields' => [
                'url' => ['type' => 'String'],
                'defaultImage' => ['type' => 'MediaItem'],
            ],
        ]);

        register_graphql_object_type('SEOSocialTwitter', [
            'fields' => [
                'username' => ['type' => 'String'],
                'cardType' => ['type' => 'SEOCardType'],
            ],
        ]);

        register_graphql_object_type('SEOSocialInstagram', [
            'fields' => [
                'url' => ['type' => 'String'],
            ],
        ]);
        register_graphql_object_type('SEOSocialLinkedIn', [
            'fields' => [
                'url' => ['type' => 'String'],
            ],
        ]);
        register_graphql_object_type('SEOSocialMySpace', [
            'fields' => [
                'url' => ['type' => 'String'],
            ],
        ]);

        register_graphql_object_type('SEOSocialPinterest', [
            'fields' => [
                'url' => ['type' => 'String'],
                'metaTag' => ['type' => 'String'],
            ],
        ]);

        register_graphql_object_type('SEOSocialYoutube', [
            'fields' => [
                'url' => ['type' => 'String'],
            ],
        ]);
        register_graphql_object_type('SEOSocialWikipedia', [
            'fields' => [
                'url' => ['type' => 'String'],
            ],
        ]);

        register_graphql_object_type('SEOSocial', [
            'description' => __('The Yoast SEO Social media links', 'wp-graphql-yoast-seo'),
            'fields' => [
                'facebook' => ['type' => 'SEOSocialFacebook'],
                'twitter' => ['type' => 'SEOSocialTwitter'],
                'instagram' => ['type' => 'SEOSocialInstagram'],
                'linkedIn' => ['type' => 'SEOSocialLinkedIn'],
                'mySpace' => ['type' => 'SEOSocialMySpace'],
                'pinterest' => ['type' => 'SEOSocialPinterest'],
                'youTube' => ['type' => 'SEOSocialYoutube'],
                'wikipedia' => ['type' => 'SEOSocialWikipedia'],
                'otherSocials' => [
                    'type' => [
                        'list_of' => 'String',
                    ],
                ],
            ],
        ]);

        register_graphql_object_type('SEORedirect', [
            'description' => __('The Yoast redirect data  (Yoast Premium only)', 'wp-graphql-yoast-seo'),
            'fields' => [
                'origin' => ['type' => 'String'],
                'target' => ['type' => 'String'],
                'type' => ['type' => 'Int'],
                'format' => ['type' => 'String'],
            ],
        ]);

        register_graphql_object_type('SEOOpenGraphFrontPage', [
            'description' => __('The Open Graph Front page data', 'wp-graphql-yoast-seo'),
            'fields' => [
                'title' => ['type' => 'String'],
                'description' => ['type' => 'String'],
                'image' => ['type' => 'MediaItem'],
            ],
        ]);

        register_graphql_object_type('SEOOpenGraph', [
            'description' => __('The Open Graph data', 'wp-graphql-yoast-seo'),
            'fields' => [
                'defaultImage' => ['type' => 'MediaItem'],
                'frontPage' => ['type' => 'SEOOpenGraphFrontPage'],
            ],
        ]);

        register_graphql_object_type('SEOContentTypeArchive', [
            'description' => __('The Yoast SEO search appearance content types fields', 'wp-graphql-yoast-seo'),
            'fields' => [
                'hasArchive' => ['type' => 'Boolean'],
                'title' => ['type' => 'String'],
                'archiveLink' => ['type' => 'String'],
                'metaDesc' => ['type' => 'String'],
                'metaRobotsNoindex' => ['type' => 'Boolean'],
                'metaRobotsNofollow' => ['type' => 'Boolean'],
                'metaRobotsIndex' => ['type' => 'String'],
                'metaRobotsFollow' => ['type' => 'String'],
                'breadcrumbTitle' => ['type' => 'String'],
                'fullHead' => ['type' => 'String'],
            ],
        ]);
        register_graphql_object_type('SEOContentType', [
            'description' => __('The Yoast SEO search appearance content types fields', 'wp-graphql-yoast-seo'),
            'fields' => [
                'title' => ['type' => 'String'],
                'metaDesc' => ['type' => 'String'],
                'metaRobotsNoindex' => ['type' => 'Boolean'],
                'schemaType' => ['type' => 'String'],
                'schema' => ['type' => 'SEOPageInfoSchema'],
                'archive' => ['type' => 'SEOContentTypeArchive'],
            ],
        ]);

        $allTypes = wp_gql_seo_build_content_types($post_types);

        register_graphql_object_type('SEOContentTypes', [
            'description' => __('The Yoast SEO search appearance content types', 'wp-graphql-yoast-seo'),
            'fields' => $allTypes,
        ]);

        register_graphql_object_type('SEOConfig', [
            'description' => __('The Yoast SEO site level configuration data', 'wp-graphql-yoast-seo'),
            'fields' => [
                'meta' => ['type' => 'SEOGlobalMeta'],
                'schema' => ['type' => 'SEOSchema'],
                'webmaster' => ['type' => 'SEOWebmaster'],
                'social' => ['type' => 'SEOSocial'],
                'breadcrumbs' => ['type' => 'SEOBreadcrumbs'],
                'redirects' => [
                    'type' => [
                        'list_of' => 'SEORedirect',
                    ],
                ],
                'openGraph' => ['type' => 'SEOOpenGraph'],
                'contentTypes' => ['type' => 'SEOContentTypes'],
            ],
        ]);

        register_graphql_object_type('SEOUserSocial', [
            'fields' => [
                'facebook' => ['type' => 'String'],
                'twitter' => ['type' => 'String'],
                'instagram' => ['type' => 'String'],
                'linkedIn' => ['type' => 'String'],
                'mySpace' => ['type' => 'String'],
                'pinterest' => ['type' => 'String'],
                'youTube' => ['type' => 'String'],
                'soundCloud' => ['type' => 'String'],
                'wikipedia' => ['type' => 'String'],
            ],
        ]);

        register_graphql_object_type('SEOUserSchema', [
            'description' => __('The Schema types for User', 'wp-graphql-yoast-seo'),
            'fields' => [
                'raw' => ['type' => 'String'],
                'pageType' => ['type' => ['list_of' => 'String']],
                'articleType' => ['type' => ['list_of' => 'String']],
            ],
        ]);

        register_graphql_object_type('SEOUser', [
            'fields' => [
                'title' => ['type' => 'String'],
                'metaDesc' => ['type' => 'String'],
                'metaRobotsNoindex' => ['type' => 'String'],
                'metaRobotsNofollow' => ['type' => 'String'],
                'canonical' => ['type' => 'String'],
                'opengraphTitle' => ['type' => 'String'],
                'opengraphDescription' => ['type' => 'String'],
                'opengraphImage' => ['type' => 'MediaItem'],
                'twitterImage' => ['type' => 'MediaItem'],
                'twitterTitle' => ['type' => 'String'],
                'twitterDescription' => ['type' => 'String'],
                'language' => ['type' => 'String'],
                'region' => ['type' => 'String'],
                'breadcrumbTitle' => ['type' => 'String'],
                'fullHead' => ['type' => 'String'],
                'social' => ['type' => 'SEOUserSocial'],
                'schema' => ['type' => 'SEOUserSchema'],
            ],
        ]);

        register_graphql_object_type('SEOPageInfoSchema', [
            'description' => __('The Schema for post type', 'wp-graphql-yoast-seo'),
            'fields' => [
                'raw' => ['type' => 'String'],
            ],
        ]);
        register_graphql_object_type('SEOPostTypePageInfo', [
            'description' => __('The page info SEO details', 'wp-graphql-yoast-seo'),
            'fields' => [
                'schema' => ['type' => 'SEOPageInfoSchema'],
            ],
        ]);

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

                $homepage = [
                    'title' => wp_gql_seo_format_string(wp_gql_seo_replace_vars($all['title-home-wpseo'])),
                    'description' => wp_gql_seo_format_string(wp_gql_seo_replace_vars($all['metadesc-home-wpseo'])),
                ];
                $author = [
                    'title' => wp_gql_seo_format_string(wp_gql_seo_replace_vars($all['title-author-wpseo'])),
                    'description' => wp_gql_seo_format_string(wp_gql_seo_replace_vars($all['metadesc-author-wpseo'])),
                ];
                $date = [
                    'title' => wp_gql_seo_format_string(wp_gql_seo_replace_vars($all['title-archive-wpseo'])),
                    'description' => wp_gql_seo_format_string(wp_gql_seo_replace_vars($all['metadesc-archive-wpseo'])),
                ];
                $config = [
                    'separator' => wp_gql_seo_format_string($all['separator']),
                ];
                $notFound = [
                    'title' => wp_gql_seo_format_string(wp_gql_seo_replace_vars($all['title-404-wpseo'])),
                    'breadcrumb' => wp_gql_seo_format_string(wp_gql_seo_replace_vars($all['breadcrumbs-404crumb'])),
                ];

                return [
                    'contentTypes' => $contentTypes,
                    'meta' => [
                        'homepage' => $homepage,
                        'author' => $author,
                        'date' => $date,
                        'config' => $config,
                        'notFound' => $notFound,
                    ],
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
                        'otherSocials' => !empty($all['other_social_urls']) ? $all['other_social_urls'] : [],
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
                        'defaultImage' => $context
                            ->get_loader('post')
                            ->load_deferred(absint($all['og_default_image_id'])),
                        'frontPage' => [
                            'title' => wp_gql_seo_format_string(
                                wp_gql_seo_replace_vars($all['open_graph_frontpage_title'])
                            ),
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
                'opengraphPublisher' => wp_gql_seo_format_string(
                    $meta !== false ? $meta->open_graph_article_publisher : ''
                ),
                'opengraphPublishedTime' => wp_gql_seo_format_string(
                    $meta !== false ? $meta->open_graph_article_published_time : ''
                ),
                'opengraphModifiedTime' => wp_gql_seo_format_string(
                    $meta !== false ? $meta->open_graph_article_modified_time : ''
                ),
                'opengraphDescription' => wp_gql_seo_format_string(
                    $meta !== false ? $meta->open_graph_description : ''
                ),
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
                        return null;
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
                    'articleType' =>
                        $meta !== false && is_array($meta->schema_article_type) ? $meta->schema_article_type : [],
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

        // if woocommerce is active, add seo to product
        if (class_exists('WooCommerce')) {
            register_graphql_field('Product', 'seo', [
                'type' => 'PostTypeSEO',
                'description' => __('The Yoast SEO data of the ContentNode', 'wp-graphql-yoast-seo'),
                'resolve' => function ($post, array $args, AppContext $context) {
                    return get_post_type_graphql_fields($post, $args, $context);
                },
            ]);
        }

        // TODO connect to content node
        // Post Type SEO Data
        if (!empty($post_types) && is_array($post_types)) {
            foreach ($post_types as $post_type) {
                $post_type_object = get_post_type_object($post_type);

                if (isset($post_type_object->graphql_single_name)):
                    // register field on edge for arch

                    $name = 'WP' . ucfirst($post_type_object->graphql_single_name) . 'Info';

                    register_graphql_field($name, 'seo', [
                        'type' => 'SEOPostTypePageInfo',
                        'description' => __(
                            'Raw schema for ' . $post_type_object->graphql_single_name,
                            'wp-graphql-yoast-seo'
                        ),
                        'resolve' => function ($item, array $args, AppContext $context) use ($post_type) {
                            $schemaArray = YoastSEO()->meta->for_post_type_archive($post_type)->schema;

                            return [
                                'schema' => [
                                    'raw' => json_encode($schemaArray, JSON_UNESCAPED_SLASHES),
                                ],
                            ];
                        },
                    ]);

                    // Loop each taxonomy to register on the edge if a category is the primary one.
                    $taxonomiesPostObj = get_object_taxonomies($post_type, 'objects');

                    $postNameKey = wp_gql_seo_get_field_key($post_type_object->graphql_single_name);

                    foreach ($taxonomiesPostObj as $tax) {
                        if (isset($tax->hierarchical) && isset($tax->graphql_single_name)) {
                            $name =
                                ucfirst($postNameKey) . 'To' . ucfirst($tax->graphql_single_name) . 'ConnectionEdge';

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
                    'description' => __(
                        'The Yoast SEO data of the ' . $taxonomy->label . ' taxonomy.',
                        'wp-graphql-yoast-seo'
                    ),
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
                            'metaDesc' => wp_gql_seo_format_string(
                                YoastSEO()->meta->for_term($term->term_id)->description
                            ),
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
    });
});
