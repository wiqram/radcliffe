<?php
/**
 * Buttons and links whose words and addresses are set in the Customizer.
 *
 * The Register button on the Summit page is printed by the generated template
 * (see scripts/build-wordpress-theme.js); the buttons that close the Agenda
 * page are printed here, so they look the same whether the programme is the
 * designed page or the blocks in the page editor.
 *
 * @package Redcliffe_Advisory
 */

defined( 'ABSPATH' ) || exit;

/**
 * One button: an anchor with editable words, opening in a new tab when it
 * leads away from the site.
 *
 * @param string $class     Button class (btn or btn-link).
 * @param string $url       Address.
 * @param string $label_key Content key of the words.
 * @param string $arrow     Arrow glyph.
 */
function rad_button( $class, $url, $label_key, $arrow ) {
	printf(
		'<a class="%1$s" href="%2$s"%3$s><span data-rad="%4$s">%5$s</span><span class="arr">%6$s</span></a>',
		esc_attr( $class ),
		esc_url( $url ),
		rad_is_external_url( $url ) ? ' target="_blank" rel="noopener"' : '',
		esc_attr( $label_key ),
		wp_kses_post( rad_get_html( $label_key ) ),
		esc_html( $arrow )
	);
}

/**
 * The buttons that close the Agenda page. The first defaults to the
 * registration page, the second to the Summit page; both can be re-worded and
 * pointed anywhere from Appearance > Customize > Agenda page.
 */
function rad_agenda_actions() {
	if ( ! rad_section_enabled( 'agenda.actions' ) ) {
		return;
	}

	$primary   = rad_saved_setting( 'agenda.actions.primaryUrl', '' );
	$primary   = '' !== $primary ? $primary : rad_registration_url();
	$primary   = '' !== $primary ? $primary : rad_url( 'contact' );
	$secondary = rad_saved_setting( 'agenda.actions.secondaryUrl', '' );
	$secondary = '' !== $secondary ? $secondary : rad_url( 'summit' );

	echo '<div class="cta-row agenda-actions">';
	rad_button( 'btn', $primary, 'agenda.actions.primaryLabel', '→' );
	rad_button( 'btn-link', $secondary, 'agenda.actions.secondaryLabel', '↗' );
	echo "</div>\n";
}

/**
 * The buttons the theme used to seed at the end of the Agenda's page content
 * are gone from the page (they were awkward to find and change), and never
 * shown even if one is somehow still there.
 *
 * @param string $html  Rendered block.
 * @param array  $block Parsed block.
 * @return string
 */
function rad_hide_retired_agenda_buttons( $html, $block ) {
	if ( ! empty( $block['attrs']['className'] ) && false !== strpos( $block['attrs']['className'], 'rad-agenda-actions' ) ) {
		return '';
	}

	return $html;
}
add_filter( 'render_block_core/buttons', 'rad_hide_retired_agenda_buttons', 10, 2 );

/**
 * Whether an address leads to the page with this slug, however it is written:
 * "/contact/", "?page_id=12" and the full web address all count.
 *
 * @param string $href Address found in a button.
 * @param string $slug Slug of a page.
 * @return bool
 */
function rad_href_points_to( $href, $slug ) {
	$page = get_page_by_path( $slug );

	if ( ! $page instanceof WP_Post || '' === $href ) {
		return false;
	}

	$url = ( 0 === strpos( $href, '/' ) || 0 === strpos( $href, '?' ) ) ? home_url( $href ) : $href;

	return (int) url_to_postid( $url ) === (int) $page->ID;
}

/**
 * One-off, for a site that already has the Agenda seeded: take the old buttons
 * block out of the page and keep whatever the owner had done to it. Words or
 * addresses they changed are carried into the Customizer settings; an
 * untouched block simply gives way to the new defaults. Nothing else on the
 * page is touched, and WordPress keeps the previous version as a revision.
 */
function rad_migrate_agenda_buttons() {
	if ( get_option( 'rad_agenda_buttons_migrated' ) ) {
		return;
	}

	$page = get_page_by_path( 'agenda' );

	if ( ! $page instanceof WP_Post ) {
		return; // Not created yet — try again on the next request.
	}

	$blocks = parse_blocks( $page->post_content );
	$kept   = array();
	$found  = false;

	foreach ( $blocks as $block ) {
		$class = isset( $block['attrs']['className'] ) ? (string) $block['attrs']['className'] : '';

		if ( 'core/buttons' !== $block['blockName'] || false === strpos( $class, 'rad-agenda-actions' ) ) {
			$kept[] = $block;
			continue;
		}

		$found   = true;
		$buttons = array_values(
			array_filter(
				$block['innerBlocks'],
				function ( $inner ) {
					return 'core/button' === $inner['blockName'];
				}
			)
		);

		// What the theme seeded: label and page for each of the two buttons.
		$seeded = array(
			'primary'   => array( 'Enquire about attending', 'contact' ),
			'secondary' => array( 'Back to the Summit', 'summit' ),
		);
		$index  = 0;

		foreach ( $seeded as $which => $original ) {
			if ( empty( $buttons[ $index ] ) || ! preg_match( '/<a[^>]*\shref="([^"]*)"[^>]*>(.*?)<\/a>/s', $buttons[ $index ]['innerHTML'], $found_link ) ) {
				$index++;
				continue;
			}
			$index++;

			$label = trim( wp_strip_all_tags( html_entity_decode( $found_link[2] ) ) );
			$href  = html_entity_decode( $found_link[1] );

			if ( '' !== $label && $label !== $original[0] && false === get_theme_mod( rad_mod_name( 'agenda.actions.' . $which . 'Label' ), false ) ) {
				set_theme_mod( rad_mod_name( 'agenda.actions.' . $which . 'Label' ), sanitize_text_field( $label ) );
			}

			if ( '' !== $href && ! rad_href_points_to( $href, $original[1] ) && false === get_theme_mod( rad_mod_name( 'agenda.actions.' . $which . 'Url' ), false ) ) {
				set_theme_mod( rad_mod_name( 'agenda.actions.' . $which . 'Url' ), esc_url_raw( 0 === strpos( $href, '/' ) ? home_url( $href ) : $href ) );
			}
		}
	}

	if ( $found ) {
		kses_remove_filters();
		$saved = wp_update_post(
			array(
				'ID'           => $page->ID,
				'post_content' => serialize_blocks( $kept ),
			)
		);
		kses_init_filters();

		if ( ! $saved || is_wp_error( $saved ) ) {
			return; // Try again next time; the buttons stay hidden meanwhile.
		}
	}

	update_option( 'rad_agenda_buttons_migrated', RAD_VERSION, false );
}
add_action( 'init', 'rad_migrate_agenda_buttons', 40 );
