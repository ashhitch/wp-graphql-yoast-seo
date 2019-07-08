<?php
/**
 * Plugin Name:     WPGraphql Yoast Seo
 * Plugin URI:      https://github.com/ashhitch/wp-graphql-yoast-seo
 * Description:     A WPGraphQL Extension that adds support for Yoast SEO
 * Author:          Ash Hitchcock
 * Author URI:      https://www.ashleyhitchcock.com
 * Text Domain:     wp-graphql-yoast-seo
 * Domain Path:     /languages
 * Version:         2.0.0
 *
 * @package         WP_Graphql_YOAST_SEO
 */
if (!defined('ABSPATH')) {
  exit();
}

add_action('graphql_register_types', function () {
  $post_types = \WPGraphQL::$allowed_post_types;

  register_graphql_object_type('SEO', [
    'fields' => [
      'title' => ['type' => 'String'],
      'metaDesc' => ['type' => 'String'],
      'focuskw' => ['type' => 'String'],
      'metaKeywords' => ['type' => 'String'],
      'metaRobotsNoindex' => ['type' => 'String'],
      'metaRobotsNofollow' => ['type' => 'String'],
      'opengraphTitle' => ['type' => 'String'],
      'opengraphDescription' => ['type' => 'String'],
      'opengraphImage' => ['type' => 'String'],
      'twitterTitle' => ['type' => 'String'],
      'twitterDescription' => ['type' => 'String'],
      'twitterImage' => ['type' => 'String']
    ]
  ]);

  if (!empty($post_types) && is_array($post_types)) {
    foreach ($post_types as $post_type) {
      $post_type_object = get_post_type_object($post_type);

      if (isset($post_type_object->graphql_single_name)):
        register_graphql_field($post_type_object->graphql_single_name, 'seo', [
          'type' => 'SEO',
          'description' => __('The Yoast SEO data of the ' . $post_type_object->graphql_single_name, 'wp-graphql'),
          'resolve' => function ($post) {
            // Connect to Yoast
            $wpseo_frontend = WPSEO_Frontend::get_instance();
            $wpseo_frontend->reset();

            // Base array
            $seo = array();

            query_posts(array(
              'p' => $post->ID,
              'post_type' => 'any'
            ));
            the_post();

            // Get data
            $seo = array(
              'title' => trim($wpseo_frontend->get_content_title($post)),
              'metaDesc' => trim($wpseo_frontend->metadesc(false)),
              'focuskw' => trim(get_post_meta($post->ID, '_yoast_wpseo_focuskw', true)),
              'metaKeywords' => trim(get_post_meta($post->ID, '_yoast_wpseo_metakeywords', true)),
              'metaRobotsNoindex' => trim(get_post_meta($post->ID, '_yoast_wpseo_meta-robots-noindex', true)),
              'metaRobotsNofollow' => trim(get_post_meta($post->ID, '_yoast_wpseo_meta-robots-nofollow', true)),
              'opengraphTitle' => trim(get_post_meta($post->ID, '_yoast_wpseo_opengraph-title', true)),
              'opengraphDescription' => trim(get_post_meta($post->ID, '_yoast_wpseo_opengraph-description', true)),
              'opengraphImage' => trim(get_post_meta($post->ID, '_yoast_wpseo_opengraph-image', true)),
              'twitterTitle' => trim(get_post_meta($post->ID, '_yoast_wpseo_twitter-title', true)),
              'twitterDescription' => trim(get_post_meta($post->ID, '_yoast_wpseo_twitter-description', true)),
              'twitterImage' => trim(get_post_meta($post->ID, '_yoast_wpseo_twitter-image', true))
            );
            wp_reset_query();

            return !empty($seo) ? $seo : null;
          }
        ]);
      endif;
    }
  }
});
