<?php
/**
 * Redcliffe Advisory theme.
 *
 * The nine pages of the website are reproduced exactly as designed. Everything
 * the old Node CMS made editable is editable here too, under
 * Appearance > Customize > Redcliffe Advisory. New sections and pictures are
 * added to any page with the ordinary page editor (inc/blocks.php), and the
 * owner's guide lives in the dashboard (inc/guide.php).
 *
 * @package Redcliffe_Advisory
 */

defined( 'ABSPATH' ) || exit;

define( 'RAD_VERSION', '1.2.0' );

require_once get_theme_file_path( 'inc/content.php' );
require_once get_theme_file_path( 'inc/customizer.php' );
require_once get_theme_file_path( 'inc/contact.php' );
require_once get_theme_file_path( 'inc/setup.php' );
require_once get_theme_file_path( 'inc/blocks.php' );
require_once get_theme_file_path( 'inc/guide.php' );

/**
 * Theme supports and menu locations.
 */
function rad_after_setup_theme() {
	load_theme_textdomain( 'redcliffe-advisory', get_theme_file_path( 'languages' ) );

	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'automatic-feed-links' );
	add_theme_support( 'customize-selective-refresh-widgets' );
	add_theme_support( 'responsive-embeds' );
	add_theme_support(
		'html5',
		array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script' )
	);

	register_nav_menus(
		array(
			'primary' => __( 'Primary menu', 'redcliffe-advisory' ),
		)
	);
}
add_action( 'after_setup_theme', 'rad_after_setup_theme' );

/**
 * Stylesheet, fonts and the site script.
 */
function rad_enqueue_assets() {
	wp_enqueue_style(
		'rad-fonts',
		rad_fonts_url(),
		array(),
		null // Google serves its own versioned URLs.
	);

	wp_enqueue_style( 'rad-style', get_stylesheet_uri(), array( 'rad-fonts' ), RAD_VERSION );

	wp_enqueue_script( 'rad-site', get_theme_file_uri( 'assets/js/site.js' ), array(), RAD_VERSION, true );
}
add_action( 'wp_enqueue_scripts', 'rad_enqueue_assets' );

/**
 * Warm up the font connections, as the original pages did.
 *
 * @param array  $urls          Resource URLs.
 * @param string $relation_type Hint type.
 * @return array
 */
function rad_resource_hints( $urls, $relation_type ) {
	if ( 'preconnect' === $relation_type ) {
		$urls[] = array( 'href' => 'https://fonts.googleapis.com' );
		$urls[] = array(
			'href'        => 'https://fonts.gstatic.com',
			'crossorigin' => 'anonymous',
		);
	}
	return $urls;
}
add_filter( 'wp_resource_hints', 'rad_resource_hints', 10, 2 );

/**
 * The theme's own favicon, unless a Site Icon has been set in WordPress.
 */
function rad_favicon() {
	if ( has_site_icon() ) {
		return;
	}
	$icon = esc_url( get_theme_file_uri( 'images/favicon.png' ) );
	printf( '<link rel="icon" type="image/png" href="%s" />' . "\n", $icon ); // phpcs:ignore WordPress.Security.EscapeOutput
	printf( '<link rel="apple-touch-icon" href="%s" />' . "\n", $icon ); // phpcs:ignore WordPress.Security.EscapeOutput
}
add_action( 'wp_head', 'rad_favicon' );

/**
 * Keep the original page titles, which were written for search results.
 *
 * @param string $title Document title.
 * @return string
 */
function rad_document_title( $title ) {
	if ( ! is_front_page() && ! is_page() ) {
		return $title;
	}

	$titles = rad_defaults( 'titles' );
	$slug   = rad_page_slug();

	return isset( $titles[ $slug ] ) ? $titles[ $slug ] : $title;
}
add_filter( 'pre_get_document_title', 'rad_document_title' );

/**
 * Menu markup for this design: bare anchors inside <nav class="nav">, with no
 * list wrapper, matching the stylesheet the pages were built against.
 */
class Rad_Nav_Walker extends Walker_Nav_Menu {

	/**
	 * No sub-menus in this design.
	 *
	 * @param string $output Menu markup.
	 * @param int    $depth  Depth.
	 * @param array  $args   Arguments.
	 */
	public function start_lvl( &$output, $depth = 0, $args = null ) {}

	/**
	 * No sub-menus in this design.
	 *
	 * @param string $output Menu markup.
	 * @param int    $depth  Depth.
	 * @param array  $args   Arguments.
	 */
	public function end_lvl( &$output, $depth = 0, $args = null ) {}

	/**
	 * Output one link.
	 *
	 * @param string  $output Menu markup.
	 * @param WP_Post $item   Menu item.
	 * @param int     $depth  Depth.
	 * @param array   $args   Arguments.
	 * @param int     $id     Item ID.
	 */
	public function start_el( &$output, $item, $depth = 0, $args = null, $id = 0 ) {
		$classes = empty( $item->classes ) ? array() : (array) $item->classes;
		$applied = array();

		$is_cta = in_array( 'cta', $classes, true );

		if ( $is_cta ) {
			$applied[] = 'cta';
		}

		// The call-to-action button is styled as a button and never carries the
		// current-page underline, matching the original design.
		if ( ! $is_cta && array_intersect( array( 'current-menu-item', 'current_page_item', 'current-menu-ancestor' ), $classes ) ) {
			$applied[] = 'is-active';
		}

		$attribute = $applied ? sprintf( ' class="%s"', esc_attr( implode( ' ', $applied ) ) ) : '';

		$output .= sprintf(
			'<a href="%s"%s>%s</a>',
			esc_url( $item->url ),
			$attribute, // Built from a fixed whitelist above.
			esc_html( $item->title )
		);
	}

	/**
	 * Links need no closing wrapper.
	 *
	 * @param string  $output Menu markup.
	 * @param WP_Post $item   Menu item.
	 * @param int     $depth  Depth.
	 * @param array   $args   Arguments.
	 */
	public function end_el( &$output, $item, $depth = 0, $args = null ) {}
}
