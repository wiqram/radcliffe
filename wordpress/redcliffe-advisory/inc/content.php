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
 * A saved setting where "cleared" is an answer of its own: the default while
 * nothing has ever been saved, and an empty string once the owner has emptied
 * the field. That is what lets a button or link be switched off by clearing
 * its address, which rad_setting_url() cannot do.
 *
 * @param string $key     Content key of the setting.
 * @param string $default Value while nothing has been saved.
 * @return string
 */
function rad_saved_setting( $key, $default = '' ) {
	$mods = get_theme_mods();
	$name = rad_mod_name( $key );

	if ( is_array( $mods ) && array_key_exists( $name, $mods ) ) {
		return trim( (string) $mods[ $name ] );
	}

	return $default;
}

/**
 * Where "Register" goes: the address set under Summit page in the Customizer,
 * or the event's registration page until then. Empty when switched off.
 *
 * @return string
 */
function rad_registration_url() {
	$links = rad_defaults( 'links' );

	return rad_saved_setting( 'summit.hero.registerUrl', isset( $links['register'] ) ? $links['register'] : '' );
}

/**
 * Whether an address leads away from this website.
 *
 * @param string $url Address.
 * @return bool
 */
function rad_is_external_url( $url ) {
	$host = wp_parse_url( $url, PHP_URL_HOST );

	return $host && wp_parse_url( home_url(), PHP_URL_HOST ) !== $host;
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

	$page           = rad_design_page( $slug );
	$cache[ $slug ] = $page ? get_permalink( $page ) : home_url( '/' );

	return $cache[ $slug ];
}

/**
 * The page that plays one of the site's nine parts ("summit", "agenda" ...).
 *
 * Found by its address first. If the owner has changed that address (Pages >
 * Quick Edit, or the address under the title), it is found by the template it
 * uses instead, so links to it, its sponsors, its buttons and the cache all keep
 * working instead of quietly pointing at the homepage.
 *
 * @param string $slug One of the slugs in inc/pages.php.
 * @return WP_Post|null
 */
function rad_design_page( $slug ) {
	static $pages = array();

	if ( array_key_exists( $slug, $pages ) ) {
		return $pages[ $slug ];
	}

	$page = get_page_by_path( $slug );

	if ( ! $page instanceof WP_Post ) {
		$page = null;

		if ( 'home' === $slug ) {
			$front = (int) get_option( 'page_on_front' );
			$page  = $front ? get_post( $front ) : null;
		} else {
			$template = rad_template_for_slug( $slug );
			$found    = $template ? get_posts(
				array(
					'post_type'     => 'page',
					'post_status'   => 'publish',
					'numberposts'   => 1,
					'orderby'       => 'ID',
					'order'         => 'ASC',
					'meta_key'      => '_wp_page_template', // phpcs:ignore WordPress.DB.SlowDBQuery
					'meta_value'    => $template, // phpcs:ignore WordPress.DB.SlowDBQuery
					'no_found_rows' => true,
				)
			) : array();
			$page     = $found ? $found[0] : null;
		}
	}

	$pages[ $slug ] = $page instanceof WP_Post ? $page : null;

	return $pages[ $slug ];
}

/**
 * The template file a design slug uses ("summit" -> "template-summit.php").
 *
 * @param string $slug Slug from inc/pages.php.
 * @return string Empty when the slug is not one of the site's parts.
 */
function rad_template_for_slug( $slug ) {
	$definitions = rad_pages();

	foreach ( $definitions['pages'] as $page ) {
		if ( $page['slug'] === $slug && ! empty( $page['template'] ) && 'front-page.php' !== $page['template'] ) {
			return $page['template'];
		}
	}

	return '';
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

	if ( ! $object instanceof WP_Post ) {
		return '';
	}

	// A page on one of the site's templates is that part of the site, whatever its address has been changed to.
	if ( 'page' === $object->post_type ) {
		$template = get_page_template_slug( $object );

		foreach ( rad_pages()['pages'] as $page ) {
			if ( $template && $page['template'] === $template ) {
				return $page['slug'];
			}
		}
	}

	return $object->post_name;
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
