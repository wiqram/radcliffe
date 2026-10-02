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

	/**
	 * A piece of text on its way to the page.
	 *
	 * @param string $value Text, with the design's own wording as the fallback.
	 * @param string $key   Content key.
	 */
	return apply_filters( 'rad_content_html', $value, $key );
}

/**
 * Keep a quotation printing correctly however it was typed.
 *
 * The homepage quotations used to carry their own decoration inside the field:
 * the gold quote marks, and a <span> around the name. The quote marks are part
 * of the page's markup now, and the name is emphasised for whoever typed it, so
 * an owner can type a quotation plainly — and a quotation saved before theme
 * 1.4.7 still prints one pair of quote marks, not two.
 *
 * @param string $value Text about to be printed.
 * @param string $key   Content key.
 * @return string
 */
function rad_testimonial_wording( $value, $key ) {
	if ( preg_match( '/^home\.testimonial\.\d+\.quote$/', $key ) ) {
		// Only ever a quote mark's worth of characters: a half-deleted tag in a
		// saved quotation must not let this swallow the words themselves.
		return preg_replace( '#<span class="(?:open|close)-q">[^<]{0,4}</span>#', '', $value );
	}

	if ( preg_match( '/^home\.testimonial\.\d+\.attribution$/', $key ) && false === strpos( $value, 'class="name"' ) ) {
		// "A City Chair · Under Chatham House rule" — the name is what precedes
		// the first separator, and there may not be one.
		return preg_replace( '#^\s*([^·<]+?)\s*·#u', '<span class="name">$1</span> ·', $value );
	}

	return $value;
}
add_filter( 'rad_content_html', 'rad_testimonial_wording', 10, 2 );

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
 * A list the owner can add rows to and take rows away from: the roles beside
 * the portrait on Who's Who, and its three career lists.
 *
 * Saved as text, one row per line with the parts separated by "|". The design's
 * own rows are the default and come back when the box is cleared, and a part
 * left empty on a line is left out of the page rather than printed blank.
 *
 * @param string $key List key.
 * @return array|null columns, template, cells and rows — or null for an unknown key.
 */
function rad_list_definition( $key ) {
	$lists = rad_defaults( 'lists' );

	return isset( $lists[ $key ] ) ? $lists[ $key ] : null;
}

/**
 * The design's rows for a list, as the text the Customizer box starts with.
 *
 * @param string $key List key.
 * @return string
 */
function rad_list_default_text( $key ) {
	$list = rad_list_definition( $key );

	if ( ! $list ) {
		return '';
	}

	$lines = array();
	foreach ( $list['rows'] as $cells ) {
		$lines[] = implode( ' | ', $cells );
	}

	return implode( "\n", $lines );
}

/**
 * Rows out of the text in a list's box. Blank lines and rows with nothing in
 * any part are skipped; a line with too few parts is padded, too many trimmed.
 *
 * @param string $text    One row per line, parts separated by "|".
 * @param int    $columns How many parts a row has.
 * @return array[]
 */
function rad_list_parse( $text, $columns ) {
	$rows = array();

	foreach ( preg_split( '/\r\n|\r|\n/', (string) $text ) as $line ) {
		$cells = array_map( 'trim', explode( '|', $line ) );
		$cells = array_slice( array_pad( $cells, $columns, '' ), 0, $columns );

		if ( '' !== implode( '', $cells ) ) {
			$rows[] = $cells;
		}
	}

	return $rows;
}

/**
 * Keep each line of a list to the inline formatting text fields allow.
 *
 * @param string $value Submitted value.
 * @return string
 */
function rad_sanitize_list( $value ) {
	$lines = preg_split( '/\r\n|\r|\n/', (string) $value );

	return implode( "\n", array_map( 'wp_kses_post', $lines ) );
}

/**
 * The rows a list shows: the saved ones, or the design's while none are saved.
 *
 * @param string $key List key.
 * @return array[]
 */
function rad_list_rows( $key ) {
	$list = rad_list_definition( $key );

	if ( ! $list ) {
		return array();
	}

	$saved = get_theme_mod( rad_mod_name( $key ), '' );
	$rows  = is_string( $saved ) ? rad_list_parse( $saved, count( $list['columns'] ) ) : array();

	return $rows ? $rows : $list['rows'];
}

/**
 * A list's rows as theme 1.4.7 printed them, from the separate fields it kept
 * each part in — or '' when none of those fields was ever changed.
 *
 * @param string $key List key.
 * @return string One row per line, parts separated by "|".
 */
function rad_list_legacy_text( $key ) {
	$list = rad_list_definition( $key );

	if ( ! $list || empty( $list['legacy'] ) ) {
		return '';
	}

	// Plain words, as typed into a list: entities back to characters (but not
	// < and >, which would turn words into markup) and no line breaks or "|".
	$plain = function ( $html ) {
		$html = preg_replace_callback(
			'/&(?!lt;|gt;)#?[a-z0-9]+;/i',
			function ( $m ) {
				return html_entity_decode( $m[0], ENT_QUOTES | ENT_HTML5, 'UTF-8' );
			},
			$html
		);

		return trim( preg_replace( '/\s+/u', ' ', str_replace( '|', '&#124;', $html ) ) );
	};

	$changed = false;
	$lines   = array();
	foreach ( $list['legacy'] as $n => $sources ) {
		$cells = isset( $list['rows'][ $n ] ) ? $list['rows'][ $n ] : array();
		foreach ( $sources as $source ) {
			$value = get_theme_mod( rad_mod_name( $source['key'] ), '' );
			if ( ! is_string( $value ) || '' === trim( $value ) ) {
				continue; // 1.4.7 printed the design's wording, as the list does.
			}
			$changed = true;

			if ( isset( $source['part'] ) ) {
				$cells[ $source['part'] ] = $plain( $value );
				continue;
			}

			// The role in its own element, then the note as the loose words after it.
			foreach ( $source['within'] as $class => $part ) {
				$cells[ $part ] = '';
				$pattern        = '#<([a-z0-9]+)\b[^>]*\bclass="' . preg_quote( $class, '#' ) . '"[^>]*>(.*?)</\1>#is';
				if ( preg_match( $pattern, $value, $m ) ) {
					$cells[ $part ] = $plain( $m[2] );
					$value          = str_replace( $m[0], ' ', $value );
				}
			}
			$cells[ $source['rest'] ] = $plain( $value );
		}
		$lines[] = implode( ' | ', $cells );
	}

	return $changed ? implode( "\n", $lines ) : '';
}

/**
 * Once, on the first page load after updating from theme 1.4.7: start each list
 * from the wording visitors were reading, so the update changes nothing on the
 * page. "Put the original list back" still returns the design's rows.
 */
function rad_carry_over_lists() {
	if ( get_option( 'rad_lists_carried_over' ) ) {
		return;
	}

	$mods = get_theme_mods();
	foreach ( array_keys( rad_defaults( 'lists' ) ) as $key ) {
		if ( is_array( $mods ) && array_key_exists( rad_mod_name( $key ), $mods ) ) {
			continue; // Already a list of its own.
		}
		$text = rad_list_legacy_text( $key );
		if ( '' !== $text ) {
			set_theme_mod( rad_mod_name( $key ), rad_sanitize_list( $text ) );
		}
	}

	update_option( 'rad_lists_carried_over', RAD_VERSION );
}
add_action( 'init', 'rad_carry_over_lists' );

/**
 * The markup of every row of a list, each part in the element the design gave it.
 *
 * @param string $key List key.
 * @return string
 */
function rad_get_list_html( $key ) {
	$list = rad_list_definition( $key );

	if ( ! $list ) {
		return '';
	}

	$out = array();
	foreach ( rad_list_rows( $key ) as $cells ) {
		$parts = array();
		foreach ( $list['cells'] as $n => $tags ) {
			$value                   = isset( $cells[ $n ] ) ? $cells[ $n ] : '';
			$parts[ '{{' . $n . '}}' ] = '' === $value ? '' : $tags[0] . wp_kses_post( $value ) . $tags[1];
		}
		$out[] = strtr( $list['template'], $parts );
	}

	return implode( "\n", $out );
}

/**
 * Print a list. Every part of every row has been through wp_kses_post(); the
 * markup around the parts is the design's own.
 *
 * @param string $key List key.
 */
function rad_list( $key ) {
	echo rad_get_list_html( $key ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
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
 * Which of the site's nine parts a page plays ("who", "summit" ...), by the
 * template it uses, or "home" for the front page. Empty for a page the owner
 * has added, which has nothing designed to edit in the Customizer.
 *
 * @param WP_Post|int $page Page.
 * @return string
 */
function rad_design_slug_for_page( $page ) {
	$page = get_post( $page );

	if ( ! $page instanceof WP_Post || 'page' !== $page->post_type ) {
		return '';
	}

	$front = (int) get_option( 'page_on_front' );

	if ( $front && (int) $page->ID === $front ) {
		return 'home';
	}

	$template    = get_page_template_slug( $page );
	$definitions = rad_pages();

	foreach ( $definitions['pages'] as $definition ) {
		if ( $template && $definition['template'] === $template ) {
			return $definition['slug'];
		}
	}

	return '';
}

/**
 * The address that opens the Customizer with this page in the preview and its
 * own section (its words and photographs) already open. Empty when the page is
 * not one of the site's designed pages, or is not published.
 *
 * @param WP_Post|int $page Page.
 * @return string
 */
function rad_customizer_link_for_page( $page ) {
	$page = get_post( $page );
	$slug = rad_design_slug_for_page( $page );

	if ( '' === $slug || 'publish' !== $page->post_status || ! current_user_can( 'customize' ) ) {
		return '';
	}

	return admin_url(
		'customize.php?' . http_build_query(
			array(
				'url'       => get_permalink( $page ),
				'return'    => admin_url( 'edit.php?post_type=page' ),
				'autofocus' => array( 'section' => 'rad_section_' . $slug ),
			)
		)
	);
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
