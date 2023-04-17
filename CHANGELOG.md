# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [4.22.2] - 2023-04-17

### Fixed

- Cannot return null for non-nullable field "MediaItem.id" (#132) (thanks @izzygld)

## [4.22.1] - 2023-03-08

### Fixed

- Taxonomies metatag behaviour fixed (#152)

## [4.22.0] - 2023-02-06

### Added

-   `Date archive` title and description

## [4.21.0] - 2023-01-06

### Added

-   `metaRobotsIndex` and `metaRobotsFollow` string fields (thanks @rodrigo-arias)

## Fix

-   Show encoded title instead of Yoast SEO variables. (thanks @rodrigo-arias)

### Changed

-   Change supported version of WordPress (thanks @BronsonQuick)

## [4.20.0] - 2022-11-16

### Added

-   Added missing base meta fields:
    -   Homepage
    -   Author
    -   Separator
    -   404

## [4.19.0] - 2022-10-21

### Added

-   Added "Other" Social Profiles after Yoast change

## [4.18.0] - 2022-06-10

### Added

-   Enable SEO field for contentNode and nodeByUri

## [4.17.0] - 2022-03-29

### Changed

-   Ensure placeholders are replaced (thanks @BronsonQuick)
-   Update option names that are deprecated (thanks @BronsonQuick)

## [4.16.2] - 2022-03-23

### Changed

-   Image optimize perf (thanks @kent1D )
-   Add guard against undefined user object (thanks @rodrigo-arias)
-   PostTypeSEO improvements: More defensive programming (thanks @matthewgrzegorczyk)

## [4.16.1] - 2022-01-23

### Changed

-   Guarding against undefined values

## [4.16.0] - 2021-10-19

### Added

-   Added extra user fields

## [4.15.1] - 2021-09-11

### Changed

-   Fix canonical url for taxonomies

## [4.15.0] - 2021-07-15

### Changed

-   Update to support Yoast 16.7

## [4.14.2] - 2021-06-07

### Changed

-   Guard against undefined meta properties

## [4.14.1] - 2021-06-01

### Added

-   Added filter to change site_url (thanks @matepaiva)

## [4.14.0] - 2021-04-26

### Changed

-   Decode strings via html_entity_decode

## [4.13.1] - 2021-04-06

### Changed

-   Version bump again, release failed

## [4.13.0] - 2021-04-06

### Added

-   Yoast head meta fields as single string (same as get_head in rest API)

## [4.12.1] - 2021-03-31

### Changed

-   guard against undefined errors

## [4.12.0] - 2021-03-19

### Added

-   Archive settings for post types
-   Reading Time

## [4.11.0] - 2021-01-08

### Changed

-   Updated deprecated deprecated function changed in WPGraphQL v1.1.0 (thanks @jasonbahl)

## [4.10.2] - 2020-12-22

### Added

-   Raw Schema data for post types archives also added into global seo settings

## [4.10.1] - 2020-12-16

### Added

-   Raw Schema data for post types archives

## [4.10.0] - 2020-12-07

### Added

-   Raw Schema data for post types, taxonomies, and users

## [4.9.0] - 2020-11-25

### Added

-   Coding standards (thanks @henrikwirth and @oriooctopus)

### Changes

-   Fixes null images from open graph

## [4.8.0] - 2020-10-07

### Added

-   Added support for content type config

## [4.7.1] - 2020-10-04

### Changes

-   Fix categories

## [4.7.0] - 2020-10-03

### Added

-   Primary Category to edge.

## [4.6.0] - 2020-09-08

### Added

-   New Schema fields:
    -   pageType
    -   articleType
-   Added cornerstone flag

### Changed

-   Changed Type used on Post Types and Taxonomies from `SEO` to `PostTypeSEO` and `TaxonomySEO`

## [4.5.5] - 2020-08-20

### Changed

-   Updated readme.txt and readme.md for consistency and display issues.

## [4.5.4] - 2020-08-19

### Changed

-   Readme.md update

## [4.5.3] - 2020-08-19

### Changed

-   Build

## [4.5.2] - 2020-08-19

### Changed

-   Build

### Added

-   Added logos for WordPress

### Changed

-   Changed Plugin Name: property to comply with WordPress.org

## [4.5.1] - 2020-07-28

### Added

-   Added inLanguage to Schema
-   Added function to tidy strings before returning

### Changed

-   Changed Composer to note require dependency plugins but suggest them, to fix issues with MU Plugins

## [4.5.0] - 2020-07-13

### Added

-   Added Frontpage and default openGraph Image

## [4.4.1] - 2020-07-05

### Changed

-   Fixes MU plugin issue and HTML entities in taxonomy titles

## [4.4.0] - 2020-07-02

### Added

-   Adds support for Author / User Metadata

### Changed

-   Missing returns added, to avoid PHP notices - Thanks @jonshipman

## [4.3.0] - 2020-06-24

### Added

-   Adds support for redirects (Yoast Premium)

## [4.2.0] - 2020-06-15

### Added

-   Adds check for dependencies

## [4.1.0] - 2020-05-06

### Added

-   Page / Tax SEO - opengraphUrl, opengraphSiteName, opengraphPublishedTime, opengraphModifiedTime - Thanks @izzygld
-   Global SEO > Schema - siteName, wordpressSiteName, siteUrl - Thanks @izzygld
-   Contributor Images added to readme

## [4.0.1] - 2020-05-03

### Added

-   Support for Yoast 14
-   Breadcrumbs for post type and taxonomies
-   canonical url for post type and taxonomies

## [3.3.0] - 2020-04-01

### Added

Support for Yoast configuration data including:

-   Webmaster verification
-   Social profiles
-   Schemas
-   Breadcrumbs (global config)

## [3.2.0] - 2020-03-09

### Added

Adding canonical url (thanks @dafky2000 )

## [3.1.0] - 2020-03-09

### Added

WooCommerce product support

## [3.0.0] - 2020-02-04

### Changed

-   BREAKING CHANGE - image urls are now returned as `mediaItem` type.
    This applies to `twitterImage` and `opengraphImage`

## [2.1.1] - 2019-12-01

### Changed

-   Update composer

## [2.1.0] - 2019-12-01

### Changed

-   Fixed term data

## [2.1.0] - 2019-11-11

### Added

-   Ability to query taxonomies for SEO data.
-   LICENSE file added

## [2.1.0] - 2019-11-01

### Added

-   Composer.json file added.

## [2.0.0] - 2019-07-08

### Changed

-   Generated Meta title and descriptions
