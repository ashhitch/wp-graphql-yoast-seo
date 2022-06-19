<?php

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
            } else {
                wp_cache_set(
                    $cache_key,
                    $id,
                    'default',
                    24 * HOUR_IN_SECONDS + mt_rand(0, 12 * HOUR_IN_SECONDS) // phpcs:ignore
                );
            }
        } elseif ('not_found' === $id) {
            return false;
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

            $schemaArray = YoastSEO()->meta->for_post_type_archive($type)->schema;

            $carry[$tag] = [
                'title' => !empty($all['title-' . $type])
                    ? wp_gql_seo_format_string(wp_gql_seo_replace_vars($all['title-' . $type]))
                    : null,
                'metaDesc' => !empty($all['metadesc-' . $type])
                    ? wp_gql_seo_format_string(wp_gql_seo_replace_vars($all['metadesc-' . $type]))
                    : null,
                'metaRobotsNoindex' => !empty($all['noindex-' . $type]) ? boolval($all['noindex-' . $type]) : false,
                'schemaType' => !empty($all['schema-page-type-' . $type]) ? $all['schema-page-type-' . $type] : null,

                'schema' => [
                    'raw' => !empty($schemaArray) ? json_encode($schemaArray, JSON_UNESCAPED_SLASHES) : null,
                ],
                'archive' =>
                    $tag == 'post' //Posts are stored like this
                        ? [
                            'hasArchive' => true,
                            'archiveLink' => get_post_type_archive_link($type),
                            'title' => !empty($all['title-archive-wpseo'])
                                ? wp_gql_seo_format_string(wp_gql_seo_replace_vars($all['title-archive-wpseo']))
                                : null,
                            'metaDesc' => !empty($all['metadesc-archive-wpseo'])
                                ? wp_gql_seo_format_string(wp_gql_seo_replace_vars($all['metadesc-archive-wpseo']))
                                : null,
                            'metaRobotsNoindex' => !empty($all['noindex-archive-wpseo'])
                                ? $all['noindex-archive-wpseo']
                                : null,
                            'breadcrumbTitle' => !empty($all['bctitle-archive-wpseo'])
                                ? $all['bctitle-archive-wpseo']
                                : null,
                            'metaRobotsNoindex' => boolval($all['noindex-archive-wpseo']),
                            'fullHead' => is_string(
                                YoastSEO()
                                    ->meta->for_post_type_archive($type)
                                    ->get_head()
                            )
                                ? YoastSEO()
                                    ->meta->for_post_type_archive($type)
                                    ->get_head()
                                : YoastSEO()
                                    ->meta->for_post_type_archive($type)
                                    ->get_head()->html,
                        ]
                        : [
                            'hasArchive' => boolval($post_type_object->has_archive),
                            'archiveLink' => get_post_type_archive_link($type),
                            'title' => !empty($all['title-ptarchive-' . $type])
                                ? $all['title-ptarchive-' . $type]
                                : null,
                            'metaDesc' => !empty($all['metadesc-ptarchive-' . $type])
                                ? $all['metadesc-ptarchive-' . $type]
                                : null,
                            'metaRobotsNoindex' => !empty($all['noindex-ptarchive-' . $type])
                                ? boolval($all['noindex-ptarchive-' . $type])
                                : false,
                            'breadcrumbTitle' => !empty($all['bctitle-ptarchive-' . $type])
                                ? $all['bctitle-ptarchive-' . $type]
                                : null,
                            'fullHead' => is_string(
                                YoastSEO()
                                    ->meta->for_post_type_archive($type)
                                    ->get_head()
                            )
                                ? YoastSEO()
                                    ->meta->for_post_type_archive($type)
                                    ->get_head()
                                : YoastSEO()
                                    ->meta->for_post_type_archive($type)
                                    ->get_head()->html,
                        ],
            ];
        }
    }
    return $carry;
}
