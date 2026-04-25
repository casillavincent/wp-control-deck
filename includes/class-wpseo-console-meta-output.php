<?php
/**
 * Frontend metadata output for the WP SEO Console module.
 *
 * @package WP_Control_Deck
 */

defined( 'ABSPATH' ) || exit;

/**
 * Outputs document titles and SEO meta tags from saved post meta.
 */
if ( ! class_exists( 'WPSEO_Console_Meta_Output', false ) ) :
class WPSEO_Console_Meta_Output {

	/**
	 * Registers frontend hooks.
	 */
	public function register() {
		add_filter( 'pre_get_document_title', array( $this, 'filter_document_title' ) );
		add_action( 'wp_head', array( $this, 'output_meta_tags' ), 1 );
	}

	/**
	 * Overrides the document title when a custom SEO title exists.
	 *
	 * @param string $title Existing document title.
	 * @return string
	 */
	public function filter_document_title( $title ) {
		$post = $this->get_supported_singular_post();

		if ( ! $post ) {
			return $title;
		}

		$values    = wpseo_console_get_effective_meta_values( $post->ID );
		$seo_title = $values['_wpseo_console_title'];

		if ( '' === $seo_title ) {
			return $title;
		}

		return sanitize_text_field( $seo_title );
	}

	/**
	 * Outputs description, robots, canonical, Open Graph, and Twitter meta tags.
	 */
	public function output_meta_tags() {
		$post = $this->get_supported_singular_post();

		if ( ! $post ) {
			return;
		}

		$values          = wpseo_console_get_effective_meta_values( $post->ID );
		$fallback_title  = $values['_wpseo_console_title'] ? $values['_wpseo_console_title'] : wp_get_document_title();
		$fallback_desc   = $values['_wpseo_console_description'];
		$twitter_title   = $values['_wpseo_console_og_title'] ? $values['_wpseo_console_og_title'] : $fallback_title;
		$twitter_desc    = $values['_wpseo_console_og_description'] ? $values['_wpseo_console_og_description'] : $fallback_desc;
		$twitter_image   = $values['_wpseo_console_og_image'];
		$has_twitter_tag = $twitter_title || $twitter_desc || $twitter_image;
		$og_type         = 'page' === get_post_type( $post ) ? 'website' : 'article';

		if ( $values['_wpseo_console_description'] ) {
			printf( '<meta name="description" content="%s" />' . "\n", esc_attr( $values['_wpseo_console_description'] ) );
		}

		if ( 'default' !== $values['_wpseo_console_robots'] ) {
			printf( '<meta name="robots" content="%s" />' . "\n", esc_attr( $values['_wpseo_console_robots'] ) );
		}

		if ( $values['_wpseo_console_canonical'] ) {
			printf( '<link rel="canonical" href="%s" />' . "\n", esc_url( $values['_wpseo_console_canonical'] ) );
		}

		printf( '<meta property="og:type" content="%s" />' . "\n", esc_attr( $og_type ) );

		if ( $values['_wpseo_console_og_title'] ) {
			printf( '<meta property="og:title" content="%s" />' . "\n", esc_attr( $values['_wpseo_console_og_title'] ) );
		}

		if ( $values['_wpseo_console_og_description'] ) {
			printf( '<meta property="og:description" content="%s" />' . "\n", esc_attr( $values['_wpseo_console_og_description'] ) );
		}

		if ( $values['_wpseo_console_og_image'] ) {
			printf( '<meta property="og:image" content="%s" />' . "\n", esc_url( $values['_wpseo_console_og_image'] ) );
		}

		if ( $has_twitter_tag ) {
			printf( '<meta name="twitter:card" content="%s" />' . "\n", esc_attr( $values['_wpseo_console_twitter_card'] ) );

			if ( $twitter_title ) {
				printf( '<meta name="twitter:title" content="%s" />' . "\n", esc_attr( $twitter_title ) );
			}

			if ( $twitter_desc ) {
				printf( '<meta name="twitter:description" content="%s" />' . "\n", esc_attr( $twitter_desc ) );
			}

			if ( $twitter_image ) {
				printf( '<meta name="twitter:image" content="%s" />' . "\n", esc_url( $twitter_image ) );
			}
		}
	}

	/**
	 * Gets the queried post when SEO output is supported.
	 *
	 * @return WP_Post|null
	 */
	private function get_supported_singular_post() {
		if ( ! is_singular( wpseo_console_get_supported_post_types() ) ) {
			return null;
		}

		$post = get_queried_object();

		return $post instanceof WP_Post ? $post : null;
	}
}
endif;
