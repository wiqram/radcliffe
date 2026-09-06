<?php
/**
 * Extra page sections, written in the ordinary WordPress page editor.
 *
 * The nine designed pages are fixed. Underneath the last designed section of
 * each one there is a slot where whatever the owner adds to the page in
 * Pages > Edit appears, styled to match the site. That is how new sections,
 * new photographs, galleries and quotes are added without touching the theme.
 *
 * A handful of block patterns ("Redcliffe Advisory" in the pattern picker)
 * give the owner ready-made sections to start from.
 *
 * @package Redcliffe_Advisory
 */

defined( 'ABSPATH' ) || exit;

/**
 * Print the page's own block content as an extra section, if there is any.
 *
 * Called from the generated templates where the old CMS's mount point sat.
 */
function rad_extra_sections() {
	$page = get_queried_object();

	if ( ! $page instanceof WP_Post || 'page' !== $page->post_type ) {
		return;
	}

	$content = trim( (string) $page->post_content );

	if ( '' === $content ) {
		return;
	}

	// Run the normal content filters so blocks, shortcodes and embeds all render.
	$html = apply_filters( 'the_content', $page->post_content ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals

	if ( '' === trim( wp_strip_all_tags( $html ) ) && false === strpos( $html, '<img' ) && false === strpos( $html, '<iframe' ) ) {
		return; // Only empty paragraphs — nothing worth a section.
	}

	echo '<section class="section rad-extra" id="more">' . "\n";
	echo '<div class="container">' . "\n";
	echo '<div class="rad-blocks entry-content">' . "\n";
	echo $html; // phpcs:ignore WordPress.Security.EscapeOutput -- post content, already filtered.
	echo "\n</div>\n</div>\n</section>\n";
}

/**
 * Editor support: the site's own colours and type inside the block editor, so
 * a section looks the same while it is being written as it does on the site.
 */
function rad_block_editor_support() {
	add_theme_support( 'editor-styles' );
	add_editor_style( array( rad_fonts_url(), 'assets/css/blocks.css' ) );

	add_theme_support( 'wp-block-styles' );
	add_theme_support( 'align-wide' );

	add_theme_support(
		'editor-color-palette',
		array(
			array(
				'name'  => __( 'Navy', 'redcliffe-advisory' ),
				'slug'  => 'navy',
				'color' => '#0F2A4A',
			),
			array(
				'name'  => __( 'City blue', 'redcliffe-advisory' ),
				'slug'  => 'city-blue',
				'color' => '#2E6CA6',
			),
			array(
				'name'  => __( 'Light blue', 'redcliffe-advisory' ),
				'slug'  => 'light-blue',
				'color' => '#8FBEE2',
			),
			array(
				'name'  => __( 'Paper', 'redcliffe-advisory' ),
				'slug'  => 'paper',
				'color' => '#E7EEF6',
			),
			array(
				'name'  => __( 'White', 'redcliffe-advisory' ),
				'slug'  => 'white',
				'color' => '#FFFFFF',
			),
		)
	);
	add_theme_support( 'disable-custom-colors' );
	add_theme_support( 'disable-custom-gradients' );
	add_theme_support( 'disable-custom-font-sizes' );
	add_theme_support(
		'editor-font-sizes',
		array(
			array(
				'name' => __( 'Normal', 'redcliffe-advisory' ),
				'slug' => 'normal',
				'size' => 20,
			),
			array(
				'name' => __( 'Large', 'redcliffe-advisory' ),
				'slug' => 'large',
				'size' => 26,
			),
		)
	);
}
add_action( 'after_setup_theme', 'rad_block_editor_support', 20 );

/**
 * The Google Fonts URL the site uses, shared by the front end and the editor.
 *
 * @return string
 */
function rad_fonts_url() {
	return 'https://fonts.googleapis.com/css2?family=EB+Garamond:ital,wght@0,400;0,500;0,600;1,400;1,500&family=Hanken+Grotesk:wght@400;500;600&family=JetBrains+Mono:wght@400;500&display=swap';
}

/**
 * Front-end styles for block content.
 */
function rad_enqueue_block_styles() {
	wp_enqueue_style( 'rad-blocks', get_theme_file_uri( 'assets/css/blocks.css' ), array( 'rad-style' ), RAD_VERSION );
}
add_action( 'wp_enqueue_scripts', 'rad_enqueue_block_styles', 20 );

/**
 * Ready-made sections in the pattern picker.
 */
function rad_register_block_patterns() {
	if ( ! function_exists( 'register_block_pattern_category' ) ) {
		return;
	}

	register_block_pattern_category(
		'redcliffe-advisory',
		array( 'label' => __( 'Redcliffe Advisory', 'redcliffe-advisory' ) )
	);

	$photo = esc_url( get_theme_file_uri( 'images/city-interior.webp' ) );
	$photo2 = esc_url( get_theme_file_uri( 'images/summit-hall.webp' ) );
	$photo3 = esc_url( get_theme_file_uri( 'images/summit-reception.webp' ) );

	$patterns = array(
		'text-section'      => array(
			'title'       => __( 'Heading and text', 'redcliffe-advisory' ),
			'description' => __( 'A small label, a large heading and a few paragraphs, like the sections already on the site.', 'redcliffe-advisory' ),
			'content'     => '<!-- wp:group {"className":"rad-section-head"} -->
<div class="wp-block-group rad-section-head"><!-- wp:paragraph {"className":"rad-label"} -->
<p class="rad-label">New section</p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2 class="wp-block-heading">A heading for <em>this section</em></h2>
<!-- /wp:heading --></div>
<!-- /wp:group -->

<!-- wp:paragraph {"className":"rad-lede"} -->
<p class="rad-lede">An opening sentence in italics, setting out what this section is about.</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>Replace this with your own text. You can add as many paragraphs as you like — press Enter at the end of a paragraph to start a new one.</p>
<!-- /wp:paragraph -->',
		),
		'photo'             => array(
			'title'       => __( 'Photograph with caption', 'redcliffe-advisory' ),
			'description' => __( 'One large photograph with a short caption underneath.', 'redcliffe-advisory' ),
			'content'     => '<!-- wp:image {"sizeSlug":"large","className":"rad-figure"} -->
<figure class="wp-block-image size-large rad-figure"><img src="' . $photo . '" alt=""/><figcaption class="wp-element-caption">A short caption for the photograph.</figcaption></figure>
<!-- /wp:image -->',
		),
		'photo-and-text'    => array(
			'title'       => __( 'Text beside a photograph', 'redcliffe-advisory' ),
			'description' => __( 'A photograph on one side, a heading and text on the other.', 'redcliffe-advisory' ),
			'content'     => '<!-- wp:media-text {"mediaType":"image","className":"rad-media-text"} -->
<div class="wp-block-media-text is-stacked-on-mobile rad-media-text"><figure class="wp-block-media-text__media"><img src="' . $photo2 . '" alt=""/></figure><div class="wp-block-media-text__content"><!-- wp:heading {"level":3} -->
<h3 class="wp-block-heading">A heading beside the picture</h3>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>A few sentences about the photograph, an event, a person or an idea. Click the picture to replace it with one of your own.</p>
<!-- /wp:paragraph --></div></div>
<!-- /wp:media-text -->',
		),
		'gallery'           => array(
			'title'       => __( 'Photo gallery', 'redcliffe-advisory' ),
			'description' => __( 'A grid of photographs. Add as many as you like.', 'redcliffe-advisory' ),
			'content'     => '<!-- wp:gallery {"columns":3,"linkTo":"none","className":"rad-gallery"} -->
<figure class="wp-block-gallery has-nested-images columns-3 is-cropped rad-gallery"><!-- wp:image {"sizeSlug":"large"} -->
<figure class="wp-block-image size-large"><img src="' . $photo . '" alt=""/></figure>
<!-- /wp:image -->

<!-- wp:image {"sizeSlug":"large"} -->
<figure class="wp-block-image size-large"><img src="' . $photo2 . '" alt=""/></figure>
<!-- /wp:image -->

<!-- wp:image {"sizeSlug":"large"} -->
<figure class="wp-block-image size-large"><img src="' . $photo3 . '" alt=""/></figure>
<!-- /wp:image --></figure>
<!-- /wp:gallery -->',
		),
		'quote'             => array(
			'title'       => __( 'Quotation', 'redcliffe-advisory' ),
			'description' => __( 'A large quotation with the name of the person who said it.', 'redcliffe-advisory' ),
			'content'     => '<!-- wp:quote {"className":"rad-pullquote"} -->
<blockquote class="wp-block-quote rad-pullquote"><!-- wp:paragraph -->
<p>The words of the quotation go here.</p>
<!-- /wp:paragraph --><cite>Who said it, and where</cite></blockquote>
<!-- /wp:quote -->',
		),
		'two-columns'       => array(
			'title'       => __( 'Two columns of text', 'redcliffe-advisory' ),
			'description' => __( 'Two short pieces of text side by side, each with its own heading.', 'redcliffe-advisory' ),
			'content'     => '<!-- wp:columns {"className":"rad-columns"} -->
<div class="wp-block-columns rad-columns"><!-- wp:column -->
<div class="wp-block-column"><!-- wp:heading {"level":3} -->
<h3 class="wp-block-heading">First heading</h3>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Text for the first column.</p>
<!-- /wp:paragraph --></div>
<!-- /wp:column -->

<!-- wp:column -->
<div class="wp-block-column"><!-- wp:heading {"level":3} -->
<h3 class="wp-block-heading">Second heading</h3>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Text for the second column.</p>
<!-- /wp:paragraph --></div>
<!-- /wp:column --></div>
<!-- /wp:columns -->',
		),
		'button'            => array(
			'title'       => __( 'Button', 'redcliffe-advisory' ),
			'description' => __( 'A dark blue button that links to another page or website.', 'redcliffe-advisory' ),
			'content'     => '<!-- wp:buttons -->
<div class="wp-block-buttons"><!-- wp:button {"className":"rad-button"} -->
<div class="wp-block-button rad-button"><a class="wp-block-button__link wp-element-button" href="' . esc_url( home_url( '/contact/' ) ) . '">Get in touch</a></div>
<!-- /wp:button --></div>
<!-- /wp:buttons -->',
		),
	);

	foreach ( $patterns as $slug => $pattern ) {
		register_block_pattern(
			'redcliffe-advisory/' . $slug,
			array(
				'title'       => $pattern['title'],
				'description' => $pattern['description'],
				'categories'  => array( 'redcliffe-advisory' ),
				'content'     => $pattern['content'],
			)
		);
	}
}
add_action( 'init', 'rad_register_block_patterns' );

/**
 * A short reminder at the top of the page editor explaining where the content
 * will appear, because the designed part of the page is not shown there.
 */
function rad_page_editor_notice() {
	$screen = get_current_screen();

	if ( ! $screen || 'page' !== $screen->post_type ) {
		return;
	}

	wp_add_inline_script(
		'wp-edit-post',
		"wp.domReady(function(){ if (wp.data && wp.data.dispatch('core/notices')) { wp.data.dispatch('core/notices').createInfoNotice(" . wp_json_encode( __( 'Anything you add here appears as a new section at the bottom of this page on the website, underneath the designed part of the page. To change the existing headline, opening text or photographs, use Appearance → Customize.', 'redcliffe-advisory' ) ) . ", { isDismissible: true, id: 'rad-where-it-appears' }); } });"
	);
}
add_action( 'enqueue_block_editor_assets', 'rad_page_editor_notice' );
