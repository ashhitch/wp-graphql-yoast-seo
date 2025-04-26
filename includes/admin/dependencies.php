<?php
/**
 * Check plugin dependencies
 *
 * @package WP_Graphql_YOAST_SEO
 */

if (!defined('ABSPATH')) {
    exit();
}

/**
 * Check for required dependencies and display admin notice if they're not met
 */
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
