<?php
/**
 * GraphQL Enum - SEOCardType
 *
 * @package WPGraphQL\YoastSEO\Type\Enum
 * @since @todo
 */

namespace WPGraphQL\YoastSEO\Type\Enum;

use WPGraphQL\Registry\TypeRegistry;
use WPGraphQL\YoastSEO\Interfaces\Registrable;
use WPGraphQL\YoastSEO\Interfaces\Type;

/**
 * Class - SEOCardTypeEnum
 */
class SEOCardTypeEnum implements Registrable, Type {
	/**
	 * Type registered in WPGraphQL.
	 *
	 * @var string
	 */
	public static string $type = 'SEOCardType';

	/**
	 * {@inheritDoc}
	 */
	public static function register( TypeRegistry $type_registry = null ) : void { // phpcs:ignore VariableAnalysis.CodeAnalysis.VariableAnalysis.UnusedVariable
		register_graphql_enum_type(
			static::$type,
			[
				'description' => static::get_description(),
				'values'      => static::get_values(),
			]
		);
	}

	/**
	 * {@inheritDoc}
	 */
	public static function get_description() : string {
		return __( 'Types of cards', 'wp-graphql-yoast-seo' );
	}

	/**
	 * Gets the Enum type values.
	 */
	public static function get_values() : array {
		return [
			'summary_large_image' => [
				'value' => 'summary_large_image',
			],
			'summary'             => [
				'value' => 'summary',
			],
		];
	}
}
