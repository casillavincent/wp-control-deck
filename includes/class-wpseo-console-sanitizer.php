<?php
/**
 * Sanitization helpers for WP SEO Console fields.
 *
 * @package WP_Control_Deck
 */

defined( 'ABSPATH' ) || exit;

/**
 * Keeps SEO field sanitization in one predictable place.
 */
if ( ! class_exists( 'WPSEO_Console_Sanitizer', false ) ) :
class WPSEO_Console_Sanitizer {

	/**
	 * Sanitizes a plain text value.
	 *
	 * @param string $value Raw value.
	 * @return string
	 */
	public static function text( $value ) {
		if ( ! is_scalar( $value ) ) {
			return '';
		}

		return sanitize_text_field( wp_unslash( (string) $value ) );
	}

	/**
	 * Sanitizes a textarea value.
	 *
	 * @param string $value Raw value.
	 * @return string
	 */
	public static function textarea( $value ) {
		if ( ! is_scalar( $value ) ) {
			return '';
		}

		return sanitize_textarea_field( wp_unslash( (string) $value ) );
	}

	/**
	 * Sanitizes a URL value.
	 *
	 * @param string $value Raw value.
	 * @return string
	 */
	public static function url( $value ) {
		if ( ! is_scalar( $value ) ) {
			return '';
		}

		return esc_url_raw( wp_unslash( (string) $value ) );
	}

	/**
	 * Sanitizes a select value against allowed options.
	 *
	 * @param string $value   Raw value.
	 * @param array  $allowed Allowed values.
	 * @param string $default Fallback value.
	 * @return string
	 */
	public static function choice( $value, $allowed, $default ) {
		if ( ! is_scalar( $value ) ) {
			return $default;
		}

		$value = sanitize_text_field( wp_unslash( (string) $value ) );

		if ( in_array( $value, $allowed, true ) ) {
			return $value;
		}

		return $default;
	}
}
endif;
