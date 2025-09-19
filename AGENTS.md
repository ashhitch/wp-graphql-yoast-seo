# WPGraphQL Yoast SEO Plugin

## Overview

A WordPress plugin that extends WPGraphQL to provide comprehensive Yoast SEO data via GraphQL queries. This plugin bridges the gap between Yoast SEO's powerful SEO features and modern headless WordPress architectures using GraphQL.

## Purpose

This plugin enables developers to query Yoast SEO metadata through GraphQL, making it essential for:
- Headless WordPress implementations
- JAMstack websites using WordPress as a CMS
- Modern frontend frameworks (React, Vue, Gatsby, Next.js, Astro) that need SEO data
- API-first WordPress architectures

## Key Features

### SEO Data Coverage
- **Post Types**: Pages, Posts, Custom post types, WooCommerce Products
- **Taxonomies**: Categories, Tags, Custom taxonomies
- **Users**: Author SEO profiles and social media links
- **Global Configuration**: Yoast settings, webmaster verification, social profiles

### Available SEO Fields
- Meta titles and descriptions
- Open Graph data (title, description, images)
- Twitter Card data
- Canonical URLs
- Meta robots directives (noindex, nofollow)
- Focus keywords
- Breadcrumbs
- Schema.org structured data
- Reading time estimates
- Complete `<head>` output

### Advanced Features
- **Schema Support**: JSON-LD structured data for rich snippets
- **Social Media Integration**: Facebook, Twitter, Instagram, LinkedIn profiles
- **Breadcrumb Navigation**: Configurable breadcrumb data
- **Image Handling**: Proper media item types for SEO images
- **Primary Category**: Support for Yoast's primary category feature
- **Redirects**: Access to Yoast redirect configurations

## Technical Architecture

### Plugin Structure
```
wp-graphql-yoast-seo/
├── wp-graphql-yoast-seo.php     # Main plugin bootstrap
├── includes/
│   ├── admin/
│   │   └── dependencies.php      # Dependency checking
│   ├── helpers/
│   │   └── functions.php         # Utility functions
│   ├── resolvers/               # GraphQL resolvers
│   │   ├── post-type.php
│   │   ├── taxonomy.php
│   │   ├── user.php
│   │   └── root-query.php
│   └── schema/
│       └── types.php            # GraphQL type definitions
```

### Dependencies
- **WordPress**: 5.0+
- **WPGraphQL**: Latest version
- **Yoast SEO**: 14.0.0+
- **PHP**: 7.4+

### Integration Points
- Hooks into `graphql_init` action
- Extends WPGraphQL schema with SEO types
- Integrates with Yoast SEO's data layer
- Provides caching for attachment URL lookups

## Usage Examples

### Basic Post SEO Query
```graphql
query GetPostSEO {
  posts(first: 10) {
    edges {
      node {
        title
        seo {
          title
          metaDesc
          canonical
          opengraphTitle
          opengraphDescription
          opengraphImage {
            sourceUrl
            altText
          }
          twitterTitle
          twitterDescription
          breadcrumbs {
            text
            url
          }
          schema {
            raw
          }
        }
      }
    }
  }
}
```

### Category SEO Data
```graphql
query GetCategorySEO {
  categories {
    nodes {
      name
      seo {
        title
        metaDesc
        canonical
        metaRobotsNoindex
        opengraphImage {
          sourceUrl
        }
      }
    }
  }
}
```

### Global SEO Configuration
```graphql
query GetSEOConfig {
  seo {
    webmaster {
      googleVerify
      bingVerify
    }
    social {
      facebook {
        url
      }
      twitter {
        username
      }
    }
    breadcrumbs {
      enabled
      separator
      homeText
    }
  }
}
```

## Development & Contribution

### Local Development Setup
The plugin includes a complete Docker development environment:

```bash
# Clone repository
git clone https://github.com/ashhitch/wp-graphql-yoast-seo.git
cd wp-graphql-yoast-seo

# Start Docker environment
docker-compose up -d

# Access WordPress at http://localhost:8000
# Admin credentials: admin/password
```

### Code Quality Standards
- Follows WordPress Coding Standards
- PHP CodeSniffer integration
- Prettier formatting for PHP
- Husky pre-commit hooks
- Comprehensive testing setup

### Testing
- PHPUnit test suite
- GraphQL query testing via GraphiQL
- Docker-based testing environment
- Automated CI/CD pipeline

## Performance Considerations

### Optimizations
- Caching for attachment URL lookups
- Lazy loading of schema components
- Efficient data retrieval from Yoast's APIs
- Minimal database queries

### Recommendations
- Use object caching for Yoast meta queries
- Implement proper error handling
- Consider pagination for large datasets
- Monitor query complexity

## Version History

### v5.0.0 (Latest)
- Complete plugin refactoring
- Improved code organization
- Enhanced performance
- Better error handling

### v4.x Series
- Yoast SEO 14.0+ compatibility
- MediaItem type for images
- Primary category support
- Redirect configuration access

## Community & Support

### Resources
- **GitHub**: [ashhitch/wp-graphql-yoast-seo](https://github.com/ashhitch/wp-graphql-yoast-seo)
- **WordPress Plugin Directory**: [add-wpgraphql-seo](https://wordpress.org/plugins/add-wpgraphql-seo/)
- **Packagist**: [ashhitch/wp-graphql-yoast-seo](https://packagist.org/packages/ashhitch/wp-graphql-yoast-seo)

### Companion Tools
- **Gatsby Plugin**: [gatsby-plugin-wpgraphql-seo](https://github.com/ashhitch/gatsby-plugin-wpgraphql-seo)
- **GraphiQL IDE**: Built into WPGraphQL for testing queries

### Getting Help
- Open issues on GitHub for bugs and feature requests
- Follow [@ash_hitchcock](https://twitter.com/ash_hitchcock) for updates
- Contribute to discussions and improvements

## License

GPL-3.0-or-later - Same as WordPress core

---

*This plugin makes headless WordPress SEO implementation seamless by providing comprehensive Yoast SEO data through GraphQL, enabling modern web architectures while maintaining excellent SEO practices.*
