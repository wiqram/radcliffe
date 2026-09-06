<?php
/**
 * Editable content.
 *
 * Every editable piece of the site is a Customizer setting whose default is the
 * original wording from the design. Clearing a field in the Customizer restores
 * that original, so the site can never end up with an empty hero or a missing
 * photograph.
 *
 * @package Redcliffe_Advisory
 */

defined( 'ABSPATH' ) || exit;

/**
 * The generated defaults, loaded once.
 *
 * @param string|null $group One of text, images, image_alt, titles.
 * @return array
 */
function rad_defaults( $group = null ) {
	static $defaults = null;

	if ( null === $defaults ) {
		$defaults = require get_theme_file_path( 'inc/content-defaults.php' );
	}

	if ( null === $group ) {
		return $defaults;
	}

	return isset( $defaults[ $group ] ) ? $defaults[ $group ] : array();
}

/**
 * Turn a content key such as home.hero.title into a theme_mod name.
 *
 * @param string $key Content key.
 * @return string
 */
function rad_mod_name( $key ) {
	return 'rad_' . str_replace( array( '.', '-' ), '_', $key );
}

/**
 * The saved value for a text field, falling back to the design's own wording.
 *
 * @param string $key Content key.
 * @return string
 */
function rad_get_html( $key ) {
	$defaults = rad_defaults( 'text' );
	$default  = isset( $defaults[ $key ] ) ? $defaults[ $key ] : '';
	$value    = get_theme_mod( rad_mod_name( $key ), $default );

	if ( ! is_string( $value ) || '' === trim( $value ) ) {
		$value = $default;
	}

	// {{url:contact}} inside a piece of text is the address of that page.
	if ( false !== strpos( $value, '{{url:' ) ) {
		$value = preg_replace_callback(
			'/\{\{url:([a-z-]+)\}\}/',
			function ( $m ) {
				return esc_url( rad_url( $m[1] ) );
			},
			$value
		);
	}

	return $value;
}

/**
 * Print an editable piece of text.
 *
 * The original design uses inline <em>, <span> and <br /> inside these fields,
 * so a limited set of HTML is allowed through, exactly as WordPress allows in
 * post content.
 *
 * @param string $key Content key.
 */
function rad_html( $key ) {
	echo wp_kses_post( rad_get_html( $key ) );
}

/**
 * Print an editable piece of text with all markup stripped.
 *
 * @param string $key Content key.
 */
function rad_text( $key ) {
	echo esc_html( wp_strip_all_tags( rad_get_html( $key ) ) );
}

/**
 * The URL for an editable image: the chosen one, or the design's original.
 *
 * @param string $key Image key.
 * @return string
 */
function rad_image_url( $key ) {
	$value = get_theme_mod( rad_mod_name( $key ), '' );

	if ( $value ) {
		if ( is_numeric( $value ) ) {
			$url = wp_get_attachment_image_url( (int) $value, 'full' );
			if ( $url ) {
				return $url;
			}
		} elseif ( filter_var( $value, FILTER_VALIDATE_URL ) ) {
			return $value;
		}
	}

	$defaults = rad_defaults( 'images' );
	$file     = isset( $defaults[ $key ] ) ? $defaults[ $key ] : 'images/favicon.png';

	// A few of the design's images are hosted elsewhere; those are already
	// complete addresses and must not be treated as files inside the theme.
	if ( preg_match( '#^(https?:)?//#', $file ) ) {
		return $file;
	}

	return get_theme_file_uri( $file );
}

/**
 * Alternative text for an editable image.
 *
 * Uses the alt text set on the uploaded image in the Media Library when there
 * is one, so screen-reader users get a real description rather than a stale one.
 *
 * @param string $key Image key.
 * @return string
 */
function rad_image_alt( $key ) {
	$value = get_theme_mod( rad_mod_name( $key ), '' );

	if ( $value && is_numeric( $value ) ) {
		$alt = get_post_meta( (int) $value, '_wp_attachment_image_alt', true );
		if ( $alt ) {
			return $alt;
		}
	}

	$defaults = rad_defaults( 'image_alt' );

	return isset( $defaults[ $key ] ) ? $defaults[ $key ] : '';
}

/**
 * Whether a section of a page is switched on.
 *
 * @param string $key Section key.
 * @return bool
 */
function rad_section_enabled( $key ) {
	return (bool) get_theme_mod( rad_mod_name( 'section.' . $key ), true );
}

/**
 * The page definitions shared with the build script.
 *
 * @return array
 */
function rad_pages() {
	static $pages = null;

	if ( null === $pages ) {
		$pages = require get_theme_file_path( 'inc/pages.php' );
	}

	return $pages;
}

/**
 * A web address saved in the Customizer, or a fallback when it is empty.
 *
 * @param string $key      Content key of the URL setting.
 * @param string $fallback Address to use when nothing is saved.
 * @return string
 */
function rad_setting_url( $key, $fallback ) {
	$value = trim( (string) get_theme_mod( rad_mod_name( $key ), '' ) );

	return '' === $value ? $fallback : $value;
}

/**
 * Link to one of the site's pages by its slug.
 *
 * Falls back to the homepage if a page has been deleted, so a missing page can
 * never produce a broken link.
 *
 * @param string $slug Page slug.
 * @return string
 */
function rad_url( $slug ) {
	static $cache = array();

	if ( isset( $cache[ $slug ] ) ) {
		return $cache[ $slug ];
	}

	if ( 'home' === $slug ) {
		$cache[ $slug ] = home_url( '/' );
		return $cache[ $slug ];
	}

	$page           = get_page_by_path( $slug );
	$cache[ $slug ] = $page ? get_permalink( $page ) : home_url( '/' );

	return $cache[ $slug ];
}

/**
 * The slug of the page being viewed, used for the body's data-page hook.
 *
 * @return string
 */
function rad_page_slug() {
	if ( is_front_page() ) {
		return 'home';
	}

	$object = get_queried_object();

	return ( $object instanceof WP_Post ) ? $object->post_name : '';
}

/**
 * Print ` class="is-active"` when the given slug is the page being viewed.
 *
 * Used by the fallback navigation, which runs when no menu has been assigned.
 *
 * @param string $slug    Page slug.
 * @param string $classes Additional classes to always apply.
 */
function rad_nav_attributes( $slug, $classes = '' ) {
	$applied = $classes ? explode( ' ', $classes ) : array();

	// The call-to-action button never carries the current-page underline.
	if ( rad_page_slug() === $slug && ! in_array( 'cta', $applied, true ) ) {
		$applied[] = 'is-active';
	}

	if ( ! $applied ) {
		return;
	}

	printf( ' class="%s"', esc_attr( implode( ' ', $applied ) ) );
}
