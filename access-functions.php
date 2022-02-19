<?php
/**
 * This file contains access functions for various class methods.
 *
 * @since @todo
 * @package .
 */

if ( ! function_exists( 'wp_gql_seo_format_string' ) ) {
	function wp_gql_seo_format_string( $string ) : ?string {
		return isset( $string ) ? html_entity_decode( trim( $string ) ) : null;
	}
}

if ( ! function_exists( 'wp_gql_seo_get_og_image' ) ) {
	function wp_gql_seo_get_og_image( $images ) {
		if ( empty( $images ) ) {
			return __return_empty_string();
		}

		$image = reset( $images );

		if ( empty( $image ) ) {
			return __return_empty_string();
		}

		if ( ! isset( $image['url'] ) ) {
			return __return_empty_string();
		}
		// Remove image sizes from url
		$url = preg_replace(
			'/(.*)-\d+x\d+\.(jpg|png|gif)$/',
			'$1.$2',
			$image['url']
		);
		return wpcom_vip_attachment_url_to_postid( $url );
	}

	
	if ( ! function_exists( 'wp_gql_seo_get_field_key' ) ) {
		function wp_gql_seo_get_field_key( $field_key ) {
			$field_key = lcfirst( preg_replace( '[^a-zA-Z0-9 -]', ' ', $field_key ) );
			$field_key = lcfirst( str_replace( '_', ' ', ucwords( $field_key, '_' ) ) );
			$field_key = lcfirst( str_replace( '-', ' ', ucwords( $field_key, '_' ) ) );
			$field_key = lcfirst( str_replace( ' ', '', ucwords( $field_key, ' ' ) ) );

			return $field_key;
		}
	}

	if ( ! function_exists( 'wpcom_vip_attachment_url_to_postid' ) ) {
		function wpcom_vip_attachment_cache_key( $url ) {
			return 'wpcom_vip_attachment_url_post_id_' . md5( $url );
		}
	}

	if ( ! function_exists( 'wpcom_vip_attachment_url_to_postid' ) ) {
		function wpcom_vip_attachment_url_to_postid( $url ) {
			$cache_key = wpcom_vip_attachment_cache_key( $url );
			$id        = wp_cache_get( $cache_key );
			if ( false === $id ) {
                $id = attachment_url_to_postid($url); // phpcs:ignore
				if ( empty( $id ) ) {
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
			} elseif ( 'not_found' === $id ) {
				return false;
			}

			return $id;
		}
	}
}
