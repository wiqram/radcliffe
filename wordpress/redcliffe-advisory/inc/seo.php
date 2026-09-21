<?php
/**
 * What search engines and social networks read: each page's title, a short
 * description, and the picture and words shown when a page is shared.
 *
 * Everything has a sensible default, so nothing has to be filled in. The owner
 * can change any of it under Appearance > Customize > Redcliffe Advisory >
 * Search and sharing. Articles use their own excerpt and featured image.
 *
 * Nothing is printed when an SEO plugin (Yoast, Rank Math, All in One SEO,
 * SEOPress, The SEO Framework) is active: two sets of tags would disagree.
 *
 * @package Redcliffe_Advisory
 */

defined( 'ABSPATH' ) || exit;

/**
 * The firm's name as it should appear in titles and emails.
 *
 * The Site Title in Settings > General is used, unless it is empty or is only
 * a web address (a new Hostinger site is called by its temporary address, and
 * that would otherwise end up in every article's title and every enquiry email).
 *
 * @return string
 */
function rad_brand_name() {
	$name = trim( html_entity_decode( (string) get_bloginfo( 'name' ), ENT_QUOTES, 'UTF-8' ) );

	if ( '' === $name || preg_match( '#^(https?://)?[a-z0-9][a-z0-9-]*(\.[a-z0-9-]+)+/?$#i', $name ) ) {
		$name = 'Redcliffe Advisory';
	}

	return (string) apply_filters( 'rad_brand_name', $name );
}

/**
 * Descriptions for the nine pages, drawn from what each page says.
 *
 * @return array Slug => description.
 */
function rad_seo_default_descriptions() {
	return array(
		'home'     => 'Redcliffe Advisory connects the world’s most independent minds, advising companies with a global outlook and bridging Deep Tech, Finance and Defence.',
		'practice' => 'Chair and CEO counsel from Redcliffe Advisory: strategic advice for companies with a global outlook, and the connections between Deep Tech, Finance and Defence.',
		'who'      => 'Karina Robinson, CEO of Redcliffe Advisory, FCSI (Hon.). Champion of the City of London, connecting the worlds of Finance, Deep Tech and Defence.',
		'city'     => 'The City of London: centuries of history at the cutting edge of technology, where the City meets the laboratory.',
		'summit'   => 'The City Quantum & AI Summit, sixth anniversary, 7 October 2026 at Mansion House, London EC4. Connections in Chaos: where world-changing technology meets the City of London.',
		'agenda'   => 'The agenda for The City Quantum & AI Summit, a single day at the Mansion House on 7 October 2026. The full speaker line-up is confirmed closer to the date.',
		'articles' => 'Articles and long reads from Redcliffe Advisory: Karina’s Column, dispatches for The Quantum Insider, and on-the-record conversations.',
		'ethics'   => 'Ethics and independence at Redcliffe Advisory: the principles by which the practice is held.',
		'contact'  => 'Contact Redcliffe Advisory for advisory enquiries, Summit attendance, media requests or private introductions.',
	);
}

/**
 * Name of the Customizer setting holding one page's title or description.
 *
 * @param string $kind "title" or "description".
 * @param string $slug Page slug from inc/pages.php.
 * @return string
 */
function rad_seo_setting( $kind, $slug ) {
	return 'rad_seo_' . $kind . '_' . $slug;
}

/**
 * What the owner has typed for a page, or an empty string.
 *
 * @param string $kind "title" or "description".
 * @param string $slug Page slug.
 * @return string
 */
function rad_seo_custom( $kind, $slug ) {
	return trim( (string) get_theme_mod( rad_seo_setting( $kind, $slug ), '' ) );
}

/**
 * The page titles written for search results, unless the owner has typed one.
 * Every other kind of page (articles, archives, 404) gets WordPress's own title
 * with the firm's name on the end.
 *
 * @param string $title Document title.
 * @return string
 */
function rad_document_title( $title ) {
	if ( ! is_front_page() && ! is_page() ) {
		return $title;
	}

	$slug   = rad_page_slug();
	$custom = rad_seo_custom( 'title', $slug );

	if ( '' !== $custom ) {
		return esc_html( $custom );
	}

	$titles = rad_defaults( 'titles' );

	return isset( $titles[ $slug ] ) ? $titles[ $slug ] : $title;
}
add_filter( 'pre_get_document_title', 'rad_document_title' );

/**
 * "Article title — Redcliffe Advisory".
 *
 * @param string $separator Separator.
 * @return string
 */
function rad_document_title_separator( $separator ) {
	return '—';
}
add_filter( 'document_title_separator', 'rad_document_title_separator' );

/**
 * Put the firm's name, not a temporary web address, on the end of titles.
 *
 * @param array $parts Title parts.
 * @return array
 */
function rad_document_title_parts( $parts ) {
	if ( isset( $parts['site'] ) ) {
		$parts['site'] = rad_brand_name();
	}

	return $parts;
}
add_filter( 'document_title_parts', 'rad_document_title_parts' );

/**
 * Whether another plugin is already writing these tags.
 *
 * @return bool
 */
function rad_seo_plugin_active() {
	return defined( 'WPSEO_VERSION' ) || defined( 'RANK_MATH_VERSION' ) || defined( 'AIOSEO_VERSION' ) || defined( 'SEOPRESS_VERSION' ) || defined( 'THE_SEO_FRAMEWORK_VERSION' );
}

/**
 * The short description of the page being viewed.
 *
 * @return string Plain text.
 */
function rad_seo_description() {
	$object = get_queried_object();
	$slug   = rad_page_slug();

	if ( $object instanceof WP_Post && is_singular() ) {
		$custom = 'page' === $object->post_type || is_front_page() ? rad_seo_custom( 'description', $slug ) : '';

		if ( '' !== $custom ) {
			return $custom;
		}

		$defaults = rad_seo_default_descriptions();

		if ( ( 'page' === $object->post_type || is_front_page() ) && isset( $defaults[ $slug ] ) ) {
			return $defaults[ $slug ];
		}

		// An article or a page the owner has added: its excerpt, or the start of its text.
		$text = has_excerpt( $object ) ? $object->post_excerpt : excerpt_remove_blocks( strip_shortcodes( $object->post_content ) );
		$text = trim( preg_replace( '/\s+/', ' ', wp_strip_all_tags( $text ) ) );

		if ( '' !== $text ) {
			return wp_trim_words( $text, 30, '…' );
		}
	}

	$tagline = trim( (string) get_bloginfo( 'description' ) );

	if ( '' !== $tagline && 'Just another WordPress site' !== $tagline ) {
		return $tagline;
	}

	$defaults = rad_seo_default_descriptions();

	return $defaults['home'];
}

/**
 * The picture for a shared link: the article's featured image, else the one
 * chosen under Search and sharing, else the Summit hall photograph.
 *
 * @return array url, width, height, alt.
 */
function rad_seo_image() {
	$id = is_singular() ? (int) get_post_thumbnail_id( get_queried_object_id() ) : 0;

	if ( ! $id ) {
		$id = (int) get_theme_mod( 'rad_seo_share_image', 0 );
	}

	if ( $id ) {
		$src = wp_get_attachment_image_src( $id, 'large' );

		if ( $src ) {
			return array(
				'url'    => $src[0],
				'width'  => (int) $src[1],
				'height' => (int) $src[2],
				'alt'    => trim( (string) get_post_meta( $id, '_wp_attachment_image_alt', true ) ),
			);
		}
	}

	return array(
		'url'    => get_theme_file_uri( 'images/share-default.jpg' ),
		'width'  => 750,
		'height' => 394,
		'alt'    => '',
	);
}

/**
 * The address of the page being viewed, for the tags that name it.
 *
 * @return string
 */
function rad_seo_url() {
	global $wp;

	if ( is_singular() ) {
		$url = wp_get_canonical_url();

		return $url ? $url : (string) get_permalink( get_queried_object_id() );
	}

	return isset( $wp->request ) && '' !== $wp->request ? home_url( user_trailingslashit( $wp->request ) ) : home_url( '/' );
}

/**
 * Print the description, and the Open Graph and Twitter tags.
 */
function rad_seo_head() {
	if ( is_404() || is_search() || ! apply_filters( 'rad_seo_output', ! rad_seo_plugin_active() ) ) {
		return;
	}

	$brand       = rad_brand_name();
	$title       = trim( html_entity_decode( wp_strip_all_tags( wp_get_document_title() ), ENT_QUOTES, 'UTF-8' ) );
	$suffix      = ' — ' . $brand;
	$description = rad_seo_description();
	$image       = rad_seo_image();
	$is_article  = is_singular( 'post' );

	// The firm's name is shown by the site itself in a shared link; keep the title to what is particular to the page.
	if ( strlen( $title ) > strlen( $suffix ) && substr( $title, -strlen( $suffix ) ) === $suffix ) {
		$title = substr( $title, 0, -strlen( $suffix ) );
	}

	$tags = array(
		array( 'name', 'description', $description ),
		array( 'property', 'og:site_name', $brand ),
		array( 'property', 'og:locale', get_locale() ),
		array( 'property', 'og:type', $is_article ? 'article' : 'website' ),
		array( 'property', 'og:title', $title ),
		array( 'property', 'og:description', $description ),
		array( 'property', 'og:url', rad_seo_url() ),
		array( 'property', 'og:image', $image['url'] ),
	);

	if ( $image['width'] && $image['height'] ) {
		$tags[] = array( 'property', 'og:image:width', (string) $image['width'] );
		$tags[] = array( 'property', 'og:image:height', (string) $image['height'] );
	}

	if ( '' !== $image['alt'] ) {
		$tags[] = array( 'property', 'og:image:alt', $image['alt'] );
	}

	if ( $is_article ) {
		$tags[] = array( 'property', 'article:published_time', get_post_time( 'c', true, get_queried_object_id() ) );
		$tags[] = array( 'property', 'article:modified_time', get_post_modified_time( 'c', true, get_queried_object_id() ) );
	}

	$tags[] = array( 'name', 'twitter:card', 'summary_large_image' );
	$tags[] = array( 'name', 'twitter:title', $title );
	$tags[] = array( 'name', 'twitter:description', $description );
	$tags[] = array( 'name', 'twitter:image', $image['url'] );

	foreach ( $tags as $tag ) {
		printf( '<meta %1$s="%2$s" content="%3$s" />' . "\n", esc_attr( $tag[0] ), esc_attr( $tag[1] ), esc_attr( $tag[2] ) );
	}
}
add_action( 'wp_head', 'rad_seo_head', 5 );

/**
 * Appearance > Customize > Redcliffe Advisory > Search and sharing.
 *
 * @param WP_Customize_Manager $wp_customize Customizer manager.
 */
function rad_seo_customize_register( $wp_customize ) {
	$section = 'rad_section_seo';

	$wp_customize->add_section(
		$section,
		array(
			'title'       => __( 'Search and sharing', 'redcliffe-advisory' ),
			'description' => __( 'What Google shows for each page, and the picture and words that appear when a page is shared on LinkedIn or in a message. Everything already has wording; fill a field in only to change it, and clear it to go back. Articles use their own summary and featured image.', 'redcliffe-advisory' ),
			'panel'       => 'rad_content',
			'priority'    => 900,
		)
	);

	$wp_customize->add_setting(
		'rad_seo_share_image',
		array(
			'default'           => '',
			'sanitize_callback' => 'absint',
			'transport'         => 'refresh',
		)
	);
	$wp_customize->add_control(
		new WP_Customize_Media_Control(
			$wp_customize,
			'rad_seo_share_image',
			array(
				'label'       => __( 'Picture shown when a page is shared', 'redcliffe-advisory' ),
				'section'     => $section,
				'mime_type'   => 'image',
				'description' => __( 'A wide picture works best, about twice as wide as it is tall. Leave empty to use the photograph of the Summit hall.', 'redcliffe-advisory' ),
			)
		)
	);

	$titles       = rad_defaults( 'titles' );
	$descriptions = rad_seo_default_descriptions();
	$definitions  = rad_pages();

	foreach ( $definitions['pages'] as $page ) {
		$slug = $page['slug'];

		foreach ( array( 'title', 'description' ) as $kind ) {
			$setting = rad_seo_setting( $kind, $slug );
			$default = 'title' === $kind ? ( isset( $titles[ $slug ] ) ? $titles[ $slug ] : '' ) : ( isset( $descriptions[ $slug ] ) ? $descriptions[ $slug ] : '' );

			$wp_customize->add_setting(
				$setting,
				array(
					'default'           => '',
					'sanitize_callback' => 'title' === $kind ? 'sanitize_text_field' : 'sanitize_textarea_field',
					'transport'         => 'refresh',
				)
			);
			$wp_customize->add_control(
				$setting,
				array(
					/* translators: 1: page name, 2: "title" or "description". */
					'label'       => sprintf( 'title' === $kind ? __( '%s: title in search results', 'redcliffe-advisory' ) : __( '%s: description in search results', 'redcliffe-advisory' ), $page['title'] ),
					'section'     => $section,
					'type'        => 'title' === $kind ? 'text' : 'textarea',
					'input_attrs' => array( 'placeholder' => html_entity_decode( $default, ENT_QUOTES, 'UTF-8' ) ),
				)
			);
		}
	}
}
add_action( 'customize_register', 'rad_seo_customize_register', 20 );
