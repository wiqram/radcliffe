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
 * give the owner ready-made sections to start from. On the Summit agenda the
 * page content *is* the programme: see rad_seed_block_pages().
 *
 * @package Redcliffe_Advisory
 */

defined( 'ABSPATH' ) || exit;

/**
 * The page's block content, rendered, or '' when there is nothing worth showing.
 *
 * @param WP_Post|null $page The page; defaults to the one being viewed.
 * @return string
 */
function rad_page_content_html( $page = null ) {
	if ( null === $page ) {
		$page = get_queried_object();
	}

	if ( ! $page instanceof WP_Post || 'page' !== $page->post_type ) {
		return '';
	}

	if ( '' === trim( (string) $page->post_content ) ) {
		return '';
	}

	// Run the normal content filters so blocks, shortcodes and embeds all render.
	$html = apply_filters( 'the_content', $page->post_content ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals

	if ( '' === trim( wp_strip_all_tags( $html ) ) && false === strpos( $html, '<img' ) && false === strpos( $html, '<iframe' ) ) {
		return ''; // Only empty paragraphs — nothing worth a section.
	}

	return $html;
}

/**
 * Whether the page being viewed has block content of its own.
 *
 * @return bool
 */
function rad_page_has_content() {
	return '' !== rad_page_content_html();
}

/**
 * Print the page's block content (inside a wrapper the template provides).
 */
function rad_page_content() {
	echo rad_page_content_html(); // phpcs:ignore WordPress.Security.EscapeOutput -- post content, already filtered.
}

/**
 * Print the page's own block content as an extra section, if there is any.
 *
 * Called from the generated templates where the old CMS's mount point sat.
 */
function rad_extra_sections() {
	$html = rad_page_content_html();

	if ( '' === $html ) {
		return;
	}

	echo '<section class="section rad-extra" id="more">' . "\n";
	echo '<div class="container">' . "\n";
	echo '<div class="rad-blocks entry-content">' . "\n";
	echo $html; // phpcs:ignore WordPress.Security.EscapeOutput -- post content, already filtered.
	echo "\n</div>\n</div>\n</section>\n";
}

/**
 * Pages whose designed section is written as blocks (today: the Summit agenda)
 * start out with that section's content already in the page editor, so the
 * owner edits the real programme instead of starting from nothing.
 *
 * Runs once per page, on the first request after the theme is installed or
 * updated; a page that already has content is never overwritten.
 */
function rad_seed_block_pages() {
	$file = get_theme_file_path( 'inc/content-seeds.php' );

	if ( ! file_exists( $file ) ) {
		return;
	}

	$seeds = require $file;
	$done  = (array) get_option( 'rad_seeded_pages', array() );

	foreach ( $seeds as $slug => $seed ) {
		if ( ! empty( $done[ $slug ] ) ) {
			continue;
		}

		$page = get_page_by_path( $slug );

		if ( ! $page instanceof WP_Post ) {
			continue; // Not created yet — try again on the next request.
		}

		if ( '' !== rad_page_content_html( $page ) ) {
			continue; // The page already says something (an empty paragraph does not count); leave it be.
		}

		// Site-relative links, so they still work once the real domain is connected.
		$links = array_map( 'wp_make_link_relative', array_map( 'rad_url', $seed['links'] ) );
		$html  = vsprintf( $seed['markup'], array_map( 'esc_url', $links ) );

		// Save exactly what the build produced, whoever is logged in.
		kses_remove_filters();
		$saved = wp_update_post(
			array(
				'ID'           => $page->ID,
				'post_content' => $html,
			)
		);
		kses_init_filters();

		if ( $saved && ! is_wp_error( $saved ) ) {
			$done[ $slug ] = RAD_VERSION;
			update_option( 'rad_seeded_pages', $done );
		}
	}
}
add_action( 'init', 'rad_seed_block_pages', 30 );

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
		'agenda-slot'       => array(
			'title'       => __( 'Agenda: a time slot', 'redcliffe-advisory' ),
			'description' => __( 'One line of the Summit programme: a time, a title and a short description.', 'redcliffe-advisory' ),
			'content'     => '<!-- wp:columns {"className":"rad-agenda-row"} -->
<div class="wp-block-columns rad-agenda-row"><!-- wp:column {"width":"132px"} -->
<div class="wp-block-column" style="flex-basis:132px"><!-- wp:paragraph {"className":"rad-agenda-time"} -->
<p class="rad-agenda-time">10.00</p>
<!-- /wp:paragraph --></div>
<!-- /wp:column -->

<!-- wp:column -->
<div class="wp-block-column"><!-- wp:paragraph {"className":"rad-agenda-title"} -->
<p class="rad-agenda-title">Title of the session</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph {"className":"rad-agenda-desc"} -->
<p class="rad-agenda-desc">Who is speaking, and what the session is about.</p>
<!-- /wp:paragraph --></div>
<!-- /wp:column --></div>
<!-- /wp:columns -->',
		),
		'agenda-part'       => array(
			'title'       => __( 'Agenda: part of the day', 'redcliffe-advisory' ),
			'description' => __( 'A small heading such as Morning or Afternoon, dividing the programme.', 'redcliffe-advisory' ),
			'content'     => '<!-- wp:heading {"level":3,"className":"rad-agenda-part"} -->
<h3 class="wp-block-heading rad-agenda-part">Afternoon</h3>
<!-- /wp:heading -->',
		),
		'speaker'           => array(
			'title'       => __( 'Speaker', 'redcliffe-advisory' ),
			'description' => __( 'A photograph, a name, a role and a line or two about a speaker. Add one per person.', 'redcliffe-advisory' ),
			'content'     => '<!-- wp:media-text {"mediaType":"image","mediaWidth":30,"className":"rad-speaker"} -->
<div class="wp-block-media-text is-stacked-on-mobile rad-speaker" style="grid-template-columns:30% auto"><figure class="wp-block-media-text__media"><img src="' . esc_url( get_theme_file_uri( 'images/karina-portrait-2026.jpeg' ) ) . '" alt=""/></figure><div class="wp-block-media-text__content"><!-- wp:heading {"level":3} -->
<h3 class="wp-block-heading">Name of the speaker</h3>
<!-- /wp:heading -->

<!-- wp:paragraph {"className":"rad-label"} -->
<p class="rad-label">Role · Organisation</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>A sentence or two about the speaker and what they will be talking about. Click the photograph to replace it.</p>
<!-- /wp:paragraph --></div></div>
<!-- /wp:media-text -->',
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

	global $post;

	$seeds   = file_exists( get_theme_file_path( 'inc/content-seeds.php' ) ) ? require get_theme_file_path( 'inc/content-seeds.php' ) : array();
	$message = __( 'Anything you add here appears as a new section at the bottom of this page on the website, underneath the designed part of the page. To change the words or photographs in the designed part, use Appearance → Customize and click the pencil next to them.', 'redcliffe-advisory' );

	if ( $post instanceof WP_Post && isset( $seeds[ $post->post_name ] ) ) {
		$message = __( 'This is the programme as it appears on the website. Click any time, title or description to change it. To add a slot, click the + and choose "Agenda: a time slot" under Redcliffe Advisory; to remove one, click it, press the three dots and choose Delete. Press Update when you are done.', 'redcliffe-advisory' );
	}

	wp_add_inline_script(
		'wp-edit-post',
		"wp.domReady(function(){ if (wp.data && wp.data.dispatch('core/notices')) { wp.data.dispatch('core/notices').createInfoNotice(" . wp_json_encode( $message ) . ", { isDismissible: true, id: 'rad-where-it-appears' }); } });"
	);
}
add_action( 'enqueue_block_editor_assets', 'rad_page_editor_notice' );
