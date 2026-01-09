<?php
/**
 * Helper functions for the plugin
 *
 * @package WP_Graphql_YOAST_SEO
 */

if (!defined('ABSPATH')) {
    exit();
}

/**
 * Format string for GraphQL.
 *
 * @param string $string String to format.
 * @return string|null
 */
function wp_gql_seo_format_string($string)
{
    return isset($string) ? html_entity_decode(trim($string)) : null;
}

/**
 * Replace variables in string.
 *
 * @param string $string String with variables.
 * @return string|null
 */
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

/**
 * Get OG image ID from Yoast SEO.
 *
 * @param array $images Array of images from Yoast SEO.
 * @return string|null
 */
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
    return wp_gql_seo_attachment_url_to_postid($url);
}

/**
 * Normalize field key names for GraphQL.
 *
 * @param string $field_key Field key to normalize.
 * @return string
 */
function wp_gql_seo_get_field_key($field_key)
{
    $field_key = lcfirst(preg_replace('/[^a-zA-Z0-9 \-]/', ' ', $field_key));
    $field_key = lcfirst(str_replace('_', ' ', ucwords($field_key, '_')));
    $field_key = lcfirst(str_replace('-', ' ', ucwords($field_key, '_')));
    $field_key = lcfirst(str_replace(' ', '', ucwords($field_key, ' ')));

    return $field_key;
}

/**
 * Generate cache key for attachment URL.
 * Only declare if not already defined (e.g., by VIP platform).
 *
 * @param string $url URL to generate cache key for.
 * @return string
 */
if (!function_exists('wpcom_vip_attachment_cache_key')) {
    function wpcom_vip_attachment_cache_key($url)
    {
        return 'wpcom_vip_attachment_url_post_id_' . md5($url);
    }
}

/**
 * Get post ID from attachment URL with caching.
 * Only declare if not already defined (e.g., by VIP platform).
 *
 * @param string $url URL to get post ID for.
 * @return int|null
 */
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
            return false;
        }

        return $id;
    }
}

/**
 * Wrapper for wpcom_vip_attachment_url_to_postid that returns null instead of false.
 * This is needed for GraphQL compatibility - fixes
 * "Cannot return null for non-nullable field 'MediaItem.id'" errors.
 *
 * @see https://github.com/ashhitch/wp-graphql-yoast-seo/issues/132
 * @param string $url URL to get post ID for.
 * @return int|null
 */
function wp_gql_seo_attachment_url_to_postid($url)
{
    $id = wpcom_vip_attachment_url_to_postid($url);
    return (false === $id || empty($id)) ? null : $id;
}

/**
 * Build content types data array for GraphQL schema.
 *
 * @param array $types Post types.
 * @return array
 */
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
 * Build taxonomy types data array for GraphQL schema.
 *
 * @param array $taxonomies Taxonomies.
 * @return array
 */
function wp_gql_seo_build_taxonomy_types($taxonomies)
{
    $carry = [];
    foreach ($taxonomies as $taxonomy) {
        $taxonomy_object = get_taxonomy($taxonomy);
        if ($taxonomy_object->graphql_single_name) {
            $carry[wp_gql_seo_get_field_key($taxonomy_object->graphql_single_name)] = ['type' => 'SEOTaxonomyType'];
        }
    }
    return $carry;
}

/**
 * Get full head content from Yoast SEO.
 *
 * @param \Yoast\WP\SEO\Surfaces\Values\Meta|bool $metaForPost Meta object.
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

/**
 * Build content type data for GraphQL schema.
 *
 * @param array $types Post types.
 * @param array $all All Yoast SEO options.
 * @return array
 */
function wp_gql_seo_build_content_type_data($types, $all)
{
    $carry = [];

    // Validate input parameters
    if (!is_array($types) || empty($types) || !is_array($all) || empty($all)) {
        return $carry;
    }

    foreach ($types as $type) {
        $post_type_object = get_post_type_object($type);

        // Validate post type object
        if (!$post_type_object || !$post_type_object->graphql_single_name) {
            continue;
        }

        $tag = wp_gql_seo_get_field_key($post_type_object->graphql_single_name);

        $meta = YoastSEO()->meta->for_post_type_archive($type);

        $carry[$tag] = [
            'title' => wp_gql_seo_format_string(wp_gql_seo_replace_vars($all['title-' . $type] ?? null)),
            'metaDesc' => wp_gql_seo_format_string(wp_gql_seo_replace_vars($all['metadesc-' . $type] ?? null)),
            'metaRobotsNoindex' => boolval($all['noindex-' . $type] ?? false),
            'schemaType' => $all['schema-page-type-' . $type] ?? null,
            'schema' => [
                'raw' =>
                    !empty($meta) && !empty($meta->schema) ? wp_json_encode($meta->schema, JSON_UNESCAPED_SLASHES) : null,
            ],
            'archive' => [
                'hasArchive' => boolval($post_type_object->has_archive),
                'archiveLink' => apply_filters('wp_gql_seo_archive_link', get_post_type_archive_link($type), $type),
                'title' => wp_gql_seo_format_string($meta->title ?? null),
                'metaDesc' => wp_gql_seo_format_string($all['metadesc-ptarchive-' . $type] ?? null),
                'metaRobotsNoindex' =>
                    !empty($meta) && !empty($meta->robots['index']) && $meta->robots['index'] === 'index'
                        ? false
                        : true,
                'metaRobotsNofollow' =>
                    !empty($meta) && !empty($meta->robots['follow']) && $meta->robots['follow'] === 'follow'
                        ? false
                        : true,
                'metaRobotsIndex' => $meta->robots['index'] ?? 'noindex',
                'metaRobotsFollow' => $meta->robots['follow'] ?? 'nofollow',
                'breadcrumbTitle' => wp_gql_seo_format_string($all['bctitle-ptarchive-' . $type] ?? null),
                'fullHead' => wp_gql_seo_get_full_head($meta),
            ],
        ];
    }

    return $carry;
}

/**
 * Build taxonomy data for GraphQL schema.
 *
 * @param array $taxonomies Taxonomies.
 * @param array $all All Yoast SEO options.
 * @return array
 */
function wp_gql_seo_build_taxonomy_data($taxonomies, $all)
{
    $carry = [];

    // Validate input parameters
    if (!is_array($taxonomies) || empty($taxonomies) || !is_array($all) || empty($all)) {
        return $carry;
    }

    foreach ($taxonomies as $taxonomy) {
        $taxonomy_object = get_taxonomy($taxonomy);

        // Validate taxonomy object
        if (!$taxonomy_object || !$taxonomy_object->graphql_single_name) {
            continue;
        }

        $tag = wp_gql_seo_get_field_key($taxonomy_object->graphql_single_name);
        $carry[$tag] = [
            'archive' => [
                'title' => wp_gql_seo_format_string(wp_gql_seo_replace_vars($all['title-tax-' . $taxonomy] ?? null)),
                'metaDesc' => wp_gql_seo_format_string(
                    wp_gql_seo_replace_vars($all['metadesc-tax-' . $taxonomy] ?? null)
                ),
                'metaRobotsNoindex' => boolval($all['noindex-tax-' . $taxonomy] ?? false),
            ],
        ];
    }

    return $carry;
}
