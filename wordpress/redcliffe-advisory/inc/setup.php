<?php
/**
 * First-run setup.
 *
 * Activating the theme builds the whole website: the nine pages, each on its
 * own template, the homepage set as the front page, and the primary menu. There
 * is nothing for the person installing it to wire up by hand.
 *
 * Safe to run more than once — existing pages are reused, never duplicated.
 *
 * @package Redcliffe_Advisory
 */

defined( 'ABSPATH' ) || exit;

/**
 * Build the site when the theme is activated.
 */
function rad_activate_theme() {
	$definitions = rad_pages();
	$ids         = array();

	foreach ( $definitions['pages'] as $page ) {
		$existing = get_page_by_path( $page['slug'] );

		if ( $existing ) {
			$id = $existing->ID;
		} else {
			$id = wp_insert_post(
				array(
					'post_type'    => 'page',
					'post_status'  => 'publish',
					'post_title'   => $page['title'],
					'post_name'    => $page['slug'],
					'post_content' => '',
				)
			);
		}

		if ( is_wp_error( $id ) || ! $id ) {
			continue;
		}

		$ids[ $page['slug'] ] = $id;

		// The front page uses front-page.php automatically and must not carry a
		// template of its own, or WordPress would use it everywhere.
		if ( ! empty( $page['template'] ) && 'front-page.php' !== $page['template'] ) {
			update_post_meta( $id, '_wp_page_template', $page['template'] );
		}
	}

	if ( isset( $ids['home'] ) ) {
		update_option( 'show_on_front', 'page' );
		update_option( 'page_on_front', $ids['home'] );
	}

	rad_create_primary_menu( $ids, $definitions['nav'] );

	set_transient( 'rad_activated', 1, HOUR_IN_SECONDS );

	flush_rewrite_rules();
}
add_action( 'after_switch_theme', 'rad_activate_theme' );

/**
 * Create and assign the primary menu, unless one is already in place.
 *
 * @param array $ids  Page IDs keyed by slug.
 * @param array $nav  Navigation definition.
 */
function rad_create_primary_menu( $ids, $nav ) {
	if ( has_nav_menu( 'primary' ) ) {
		return; // Someone has already set one up; leave their work alone.
	}

	if ( ! function_exists( 'wp_update_nav_menu_item' ) ) {
		require_once ABSPATH . 'wp-admin/includes/nav-menu.php';
	}

	$name = __( 'Primary menu', 'redcliffe-advisory' );
	$menu = wp_get_nav_menu_object( $name );

	if ( $menu ) {
		$menu_id = $menu->term_id;
	} else {
		$menu_id = wp_create_nav_menu( $name );
	}

	if ( is_wp_error( $menu_id ) ) {
		return;
	}

	// Only populate an empty menu, so re-activating never doubles it up.
	if ( ! wp_get_nav_menu_items( $menu_id ) ) {
		foreach ( $nav as $item ) {
			if ( ! isset( $ids[ $item['slug'] ] ) ) {
				continue;
			}

			$item_id = wp_update_nav_menu_item(
				$menu_id,
				0,
				array(
					'menu-item-title'     => $item['label'],
					'menu-item-object'    => 'page',
					'menu-item-object-id' => $ids[ $item['slug'] ],
					'menu-item-type'      => 'post_type',
					'menu-item-status'    => 'publish',
				)
			);

			if ( ! is_wp_error( $item_id ) && ! empty( $item['classes'] ) ) {
				update_post_meta( $item_id, '_menu_item_classes', array( $item['classes'] ) );
			}
		}
	}

	$locations            = get_theme_mod( 'nav_menu_locations', array() );
	$locations['primary'] = $menu_id;
	set_theme_mod( 'nav_menu_locations', $locations );
}

/**
 * Point whoever installed the theme at the two things they will want next.
 */
function rad_activation_notice() {
	if ( ! get_transient( 'rad_activated' ) || ! current_user_can( 'edit_theme_options' ) ) {
		return;
	}

	delete_transient( 'rad_activated' );

	printf(
		'<div class="notice notice-success is-dismissible"><p><strong>%s</strong></p><p>%s</p><p>%s</p></div>',
		esc_html__( 'The Redcliffe Advisory website is set up.', 'redcliffe-advisory' ),
		sprintf(
			/* translators: 1: link to the site, 2: link to the Customizer. */
			wp_kses_post( __( 'All nine pages have been created and the menu is in place. <a href="%1$s">View the website</a> or <a href="%2$s">edit its words and pictures</a>.', 'redcliffe-advisory' ) ),
			esc_url( home_url( '/' ) ),
			esc_url( admin_url( 'customize.php' ) )
		),
		sprintf(
			/* translators: %s: link to the guide. */
			wp_kses_post( __( 'Contact form messages arrive under "Enquiries" in the menu on the left. The <a href="%s">Website guide</a> explains how to look after the site.', 'redcliffe-advisory' ) ),
			esc_url( admin_url( 'admin.php?page=rad-guide' ) )
		)
	);
}
add_action( 'admin_notices', 'rad_activation_notice' );
