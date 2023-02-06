# WPGraphQl Yoast SEO Plugin

[![Latest Stable Version](https://poser.pugx.org/ashhitch/wp-graphql-yoast-seo/v/stable)](https://packagist.org/packages/ashhitch/wp-graphql-yoast-seo)
[![Total Downloads](https://poser.pugx.org/ashhitch/wp-graphql-yoast-seo/downloads)](https://packagist.org/packages/ashhitch/wp-graphql-yoast-seo)
[![Monthly Downloads](https://poser.pugx.org/ashhitch/wp-graphql-yoast-seo/d/monthly)](https://packagist.org/packages/ashhitch/wp-graphql-yoast-seo)

![WPGraphQl Yoast SEO Plugin](./banner.png)

## Please note version 14 of the Yoast Plugin is a major update.

If you are stuck on version of Yoast before V14 then use v3 of this plugin.

This is an extension to the WPGraphQL plugin (https://github.com/wp-graphql/wp-graphql) that returns Yoast SEO data.

> Using this plugin? I would love to see what you make with it. 😃 [@ash_hitchcock](https://twitter.com/ash_hitchcock)

**Currently returning SEO data for:**

-   Pages
-   Posts
-   Custom post types
-   Products (WooCommerce)
-   Categories
-   Custom taxonomies
-   WooCommerce Products
-   Yoast Configuration
    -   Webmaster verification
    -   Social profiles
    -   Schemas
    -   Breadcrumbs

> If there is any Yoast data that is not currently returned, please raise an issue so we can add it to the roadmap.

## Quick Install

-   Install from the [WordPress Plugin Directory](https://wordpress.org/plugins/add-wpgraphql-seo/)
-   Clone or download the zip of this repository into your WordPress plugin directory & activate the **WP GraphQL Yoast SEO** plugin
-   Install & activate [WPGraphQL](https://www.wpgraphql.com/)

## Composer

```
composer require ashhitch/wp-graphql-yoast-seo
```

## Find this useful?

<a href="https://www.buymeacoffee.com/hafowuvo" target="_blank"><img src="https://www.buymeacoffee.com/assets/img/custom_images/orange_img.png" alt="Buy Me A Coffee" style="height: 40px !important;width: auto !important;" ></a>

## Canonicals

> Please note canonicals will not be returned if you have the discourage search engines option turned on in your WordPress settings.

## V4 breaking change

Plugin now requires at least Yoast 14.0.0

## V3 breaking change

Image urls are now returned as `mediaItem` type.

This applies to `twitterImage` and `opengraphImage`

## Usage with Gatsby

Checkout the companion [Gatsby plugin](https://github.com/ashhitch/gatsby-plugin-wpgraphql-seo) to add in Metadata and JSON LD schema with ease.

## Usage

To query for the Yoast Data simply add the seo object to your query:

### Post Type Data

```graphql
query GetPages {
    pages(first: 10) {
        edges {
            node {
                id
                title
                seo {
                    canonical
                    title
                    metaDesc
                    focuskw
                    metaRobotsNoindex
                    metaRobotsNofollow
                    opengraphAuthor
                    opengraphDescription
                    opengraphTitle
                    opengraphDescription
                    opengraphImage {
                        altText
                        sourceUrl
                        srcSet
                    }
                    opengraphUrl
                    opengraphSiteName
                    opengraphPublishedTime
                    opengraphModifiedTime
                    twitterTitle
                    twitterDescription
                    twitterImage {
                        altText
                        sourceUrl
                        srcSet
                    }
                    breadcrumbs {
                        url
                        text
                    }
                    cornerstone
                    schema {
                        pageType
                        articleType
                        raw
                    }
                    readingTime
                    fullHead
                }
                author {
                    node {
                        seo {
                            metaDesc
                            metaRobotsNofollow
                            metaRobotsNoindex
                            title
                            social {
                                youTube
                                wikipedia
                                twitter
                                soundCloud
                                pinterest
                                mySpace
                                linkedIn
                                instagram
                                facebook
                            }
                        }
                    }
                }
            }
        }
    }
}
```

### Post Taxonomy Data

```graphql
query GetCategories {
    categories(first: 10) {
        edges {
            node {
                id
                seo {
                    fullHead
                    canonical
                    title
                    metaDesc
                    focuskw
                    metaRobotsNoindex
                    metaRobotsNofollow
                    opengraphAuthor
                    opengraphDescription
                    opengraphTitle
                    opengraphDescription
                    opengraphImage {
                        altText
                        sourceUrl
                        srcSet
                    }
                    twitterTitle
                    twitterDescription
                    twitterImage {
                        altText
                        sourceUrl
                        srcSet
                    }
                    breadcrumbs {
                        url
                        text
                    }
                }
                name
            }
        }
    }
}
```

### User Data

```graphql
query GetUsers {
    users {
        nodes {
            seo {
                metaDesc
                metaRobotsNofollow
                metaRobotsNoindex
                title
                fullHead
                social {
                    youTube
                    wikipedia
                    twitter
                    soundCloud
                    pinterest
                    mySpace
                    linkedIn
                    instagram
                    facebook
                }
            }
        }
    }
}
```

### Edge and Page Info Data

```graphql
query GetPostsWithIsPrimary {
    posts {
        pageInfo {
            startCursor
            seo {
                schema {
                    raw
                }
            }
        }
        nodes {
            title
            slug
            categories {
                edges {
                    isPrimary
                    node {
                        name
                        count
                    }
                }
            }
        }
    }
}
```

### Yoast Config Data

```graphql
query GetSeoConfig {
    seo {
        meta {
            author {
                description
                title
            }
            date {
                description
                title
            }
            config {
                separator
            }
            homepage {
                description
                title
            }
            notFound {
                breadcrumb
                title
            }
        }
        webmaster {
            googleVerify
            yandexVerify
            msVerify
            baiduVerify
        }
        schema {
            siteName
            wordpressSiteName
            siteUrl
            inLanguage
            companyName
            companyOrPerson
            companyLogo {
                mediaItemUrl
            }
            logo {
                mediaItemUrl
            }
            personLogo {
                mediaItemUrl
            }
        }
        breadcrumbs {
            showBlogPage
            separator
            searchPrefix
            prefix
            homeText
            enabled
            boldLast
            archivePrefix
            notFoundText
        }
        social {
            facebook {
                url
                defaultImage {
                    mediaItemUrl
                }
            }
            instagram {
                url
            }
            linkedIn {
                url
            }
            mySpace {
                url
            }
            pinterest {
                url
                metaTag
            }
            twitter {
                cardType
                username
            }
            wikipedia {
                url
            }
            youTube {
                url
            }
            otherSocials
        }
        openGraph {
            frontPage {
                title
                description
                image {
                    altText
                    sourceUrl
                    mediaItemUrl
                }
            }
            defaultImage {
                altText
                sourceUrl
                mediaItemUrl
            }
        }
        contentTypes {
            post {
                title
                schemaType
                metaRobotsNoindex
                metaDesc
                schema {
                    raw
                }

                archive {
                    fullHead
                    archiveLink
                    breadcrumbTitle
                    hasArchive
                    metaDesc
                    metaRobotsNoindex
                    title
                }
            }
            page {
                metaDesc
                metaRobotsNoindex
                schemaType
                title
                schema {
                    raw
                }
            }
        }
        redirects {
            origin
            target
            format
            type
        }
    }
}
```

### contentNode and nodeByUri

```graphql
contentNode(id: "1", idType: DATABASE_ID) {
    id
    contentTypeName
    seo {
      title
      metaDesc
    }
  }
  node(id: "cG9zdDox",) {
    ... on NodeWithTitle {
      seo {
        title
        metaDesc
      }
    }

  }
  nodeByUri(uri: "/") {
    ... on NodeWithTitle {
      seo {
        title
        metaDesc
      }
    }
  }
```

## Support

[Open an issue](https://github.com/ashhitch/wp-graphql-yoast-seo/issues)

[Twitter: @ash_hitchcock](https://twitter.com/ash_hitchcock)

> Please Note: Yoast and WPGraphQL and their logos are copyright to their respective owners.
