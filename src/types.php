
<?php
use WPGraphQL\AppContext;

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
    'description' => __('he Yoast SEO search appearance content types fields', 'wp-graphql-yoast-seo'),
    'fields' => [
        'hasArchive' => ['type' => 'Boolean'],
        'title' => ['type' => 'String'],
        'archiveLink' => ['type' => 'String'],
        'metaDesc' => ['type' => 'String'],
        'metaRobotsNoindex' => ['type' => 'Boolean'],
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

