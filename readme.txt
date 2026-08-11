=== WPGraphQL Yoast SEO Addon ===
Contributors: ash_hitch
Tags: SEO, Yoast, WPGraphQL, GraphQL, Headless WordPress
Requires at least: 5.0
Tested up to: 7.0
Requires PHP: 7.1
Stable tag: 5.1.0
License: GPLv3
License URI: https://www.gnu.org/licenses/gpl-3.0.html

This plugin enables Yoast SEO Support for WPGraphQL.

== Description ==

This plugin enables Yoast SEO Support for WPGraphQL

This is an extension to the WPGraphQL plugin (https://github.com/wp-graphql/wp-graphql) that returns Yoast SEO data.

**Currently returning SEO data for:**

- Pages
- Posts
- Custom post types
- Products (WooCommerce)
- Categories
- Custom taxonomies
- WooCommerce Products
- Yoast Configuration
  - Webmaster verification
  - Social profiles (including Mastodon)
  - Schemas
  - Breadcrumbs
  - Advanced robots directives (noarchive, noimageindex, nosnippet)
  - SEO analysis scores and link counts
  - Structured head JSON (html + json)
  - Open Graph locale, FB App ID, enabled toggle
  - Twitter creator/site handles, enabled toggle
  - Pagination rel_next/rel_prev
  - Search results, author/date archive noindex settings
  - Schema article type per content type
  - Sitemap, Index Now, and analysis toggles
  - Premium: per-archive social settings (title/description/image)

  > Please Note: Yoast and WPGraphQL and their logos are copyright to their respective owners.

== Installation ==

1. Install & activate [WPGraphQL](https://www.wpgraphql.com/)
2. Install & activate [Yoast SEO](https://wordpress.org/plugins/wordpress-seo/)
2. Upload plugin to the `/wp-content/plugins/` directory


 [See GitHub Repo for example queries](https://github.com/ashhitch/wp-graphql-yoast-seo)

== Upgrade Notice ==
Please note version 14 of the Yoast Plugin is a major update so is now required to run this plugin
