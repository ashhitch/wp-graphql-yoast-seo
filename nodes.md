$term = $context->get_loader('term')->load(\$wpseo_primary_term);

    // register_graphql_field('PostToCategoryConnectionEdge', 'primary',  [
    //   'type'        => 'TermNode',
    //   'description' => __('The Yoast SEO Primary category', 'wp-graphql-yoast-seo'),
    //   'resolve'     => function ($post, array $args, AppContext $context) {
    //     $id = $post['source']->ID;

    //     $wpseo_primary_term = new WPSEO_Primary_Term('category', $id);
    //     $wpseo_primary_term = $wpseo_primary_term->get_primary_term();

    //     $term = $context->get_loader('term')->load($wpseo_primary_term);
    //     // wp_send_json($term);
    //     return  $term;
    //   }
    // ]);
