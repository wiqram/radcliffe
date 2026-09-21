<?php
/**
 * Keep what visitors see in step with what the owner has just changed.
 *
 * The hosting keeps finished pages in a cache (LiteSpeed, with a network of
 * edge servers in front of it). LiteSpeed clears a page when that page itself
 * is edited, but not when something on it comes from elsewhere: a sponsor, a
 * tier, a menu, a Customizer setting, a new article. Without the rules below
 * the owner would make a change, see it himself (people who are logged in are
 * never served from the cache) and find that visitors still saw the old page
 * for days.
 *
 * Two safeguards:
 * 1. Clear the pages a change shows on, straight away.
 * 2. Keep cached copies short-lived, so anything that could not be cleared
 *    (the edge servers cannot be reached from here) is out of date for minutes,
 *    not days.
 *
 * Everything is skipped where the LiteSpeed plugin is not installed, so the
 * theme behaves the same anywhere else it is used.
 *
 * @package Redcliffe_Advisory
 */

defined( 'ABSPATH' ) || exit;

/**
 * How long, in seconds, a visitor may be shown a copy of a page that has
 * since changed. Ten minutes by default.
 *
 * @return int
 */
function rad_cache_ttl() {
	return max( 60, (int) apply_filters( 'rad_cache_ttl', 10 * MINUTE_IN_SECONDS ) );
}

/**
 * Ask the cache to keep this page for a short time only.
 */
function rad_cache_short_lived() {
	if ( is_admin() || is_user_logged_in() || ! has_action( 'litespeed_control_set_ttl' ) ) {
		return;
	}

	do_action( 'litespeed_control_set_ttl', rad_cache_ttl(), 'Redcliffe: short-lived, so changes reach visitors quickly' );
}
add_action( 'template_redirect', 'rad_cache_short_lived', 1 );

/**
 * Clear every cached page.
 *
 * Once per request however many changes trigger it: the cache is told by a
 * header on the response being sent, and one instruction is enough.
 */
function rad_purge_all_pages() {
	static $done = false;

	if ( $done || ! has_action( 'litespeed_purge_all' ) ) {
		return;
	}

	$done = true;
	do_action( 'litespeed_purge_all' );
}

/**
 * Clear the cached copy of one page, found by its address (slug).
 *
 * @param string $slug Page slug: "summit", "articles".
 */
function rad_purge_page( $slug ) {
	static $done = array();

	if ( isset( $done[ $slug ] ) || ! has_action( 'litespeed_purge_post' ) ) {
		return;
	}

	$page = rad_design_page( $slug );

	if ( ! $page instanceof WP_Post ) {
		return;
	}

	$done[ $slug ] = true;
	do_action( 'litespeed_purge_post', $page->ID );
}

/* ----------------------------------------------------------------------
 * Changes that show on every page.
 * ---------------------------------------------------------------------- */

foreach ( array(
	'customize_save_after',        // Appearance > Customize > Publish.
	'wp_create_nav_menu',          // The menu along the top, and what is in it.
	'wp_update_nav_menu',
	'wp_update_nav_menu_item',
	'wp_delete_nav_menu',
	'after_switch_theme',
	'attachment_updated',          // A picture edited in the Media Library.
	'delete_attachment',
	'update_option_blogname',
	'update_option_blogdescription',
	'update_option_show_on_front',
	'update_option_page_on_front',
	'update_option_page_for_posts',
	'update_option_site_icon',
) as $rad_event ) {
	add_action( $rad_event, 'rad_purge_all_pages' );
}
unset( $rad_event );

// The Customizer's saved settings, however they were saved.
add_action( 'update_option_theme_mods_' . get_stylesheet(), 'rad_purge_all_pages' );
add_action( 'add_option_theme_mods_' . get_stylesheet(), 'rad_purge_all_pages' );

/**
 * A page's name or address changes what the menu and links say on every page,
 * and so does a menu item being removed.
 *
 * @param int     $post_id Post ID.
 * @param WP_Post $after   The page as it is now.
 * @param WP_Post $before  The page as it was.
 */
function rad_purge_all_for_page_change( $post_id, $after, $before ) {
	if ( 'page' === $after->post_type && ( $after->post_title !== $before->post_title || $after->post_name !== $before->post_name || $after->post_status !== $before->post_status ) ) {
		rad_purge_all_pages();
	}
}
add_action( 'post_updated', 'rad_purge_all_for_page_change', 10, 3 );

/**
 * A page or menu item is trashed, restored or deleted.
 *
 * @param int $post_id Post ID.
 */
function rad_purge_all_for_removal( $post_id ) {
	if ( in_array( get_post_type( $post_id ), array( 'page', 'nav_menu_item' ), true ) ) {
		rad_purge_all_pages();
	}
}
add_action( 'trashed_post', 'rad_purge_all_for_removal' );
add_action( 'untrashed_post', 'rad_purge_all_for_removal' );
add_action( 'before_delete_post', 'rad_purge_all_for_removal' );

/**
 * A new version of the theme or a plugin has just been installed.
 *
 * @param WP_Upgrader $upgrader Upgrader.
 * @param array       $data     What was upgraded.
 */
function rad_purge_after_upgrade( $upgrader, $data ) {
	if ( isset( $data['type'] ) && in_array( $data['type'], array( 'theme', 'plugin' ), true ) ) {
		rad_purge_all_pages();
	}
}
add_action( 'upgrader_process_complete', 'rad_purge_after_upgrade', 10, 2 );

/* ----------------------------------------------------------------------
 * Sponsors and tiers show on the Summit page only.
 * ---------------------------------------------------------------------- */

/**
 * Clear the Summit page when a sponsor is saved, trashed, restored or deleted.
 *
 * @param int $post_id Post ID.
 */
function rad_purge_summit_for_sponsor( $post_id ) {
	if ( 'rad_sponsor' === get_post_type( $post_id ) ) {
		rad_purge_page( 'summit' );
	}
}
add_action( 'save_post_rad_sponsor', 'rad_purge_summit_for_sponsor', 30 );
add_action( 'trashed_post', 'rad_purge_summit_for_sponsor' );
add_action( 'untrashed_post', 'rad_purge_summit_for_sponsor' );
add_action( 'before_delete_post', 'rad_purge_summit_for_sponsor' );

/**
 * Clear the Summit page when a tier is added, changed or removed.
 */
function rad_purge_summit_for_tier() {
	rad_purge_page( 'summit' );
}
add_action( 'created_rad_tier', 'rad_purge_summit_for_tier', 30 );
add_action( 'edited_rad_tier', 'rad_purge_summit_for_tier', 30 );
add_action( 'delete_rad_tier', 'rad_purge_summit_for_tier' );

/* ----------------------------------------------------------------------
 * Articles are listed on the Articles page.
 * ---------------------------------------------------------------------- */

/**
 * Clear the Articles page when an article is published, changed, unpublished
 * or removed. LiteSpeed clears the article's own address; the list is a
 * different page.
 *
 * @param int $post_id Post ID.
 */
function rad_purge_articles_for_post( $post_id ) {
	if ( 'post' === get_post_type( $post_id ) ) {
		rad_purge_page( 'articles' );
	}
}
add_action( 'save_post_post', 'rad_purge_articles_for_post', 30 );
add_action( 'trashed_post', 'rad_purge_articles_for_post' );
add_action( 'untrashed_post', 'rad_purge_articles_for_post' );
add_action( 'before_delete_post', 'rad_purge_articles_for_post' );

/**
 * Clear the Articles page when a category or tag (the filter buttons and the
 * words on each card) is added, renamed or removed.
 *
 * @param int    $term_id  Term ID.
 * @param int    $tt_id    Term taxonomy ID.
 * @param string $taxonomy Taxonomy.
 */
function rad_purge_articles_for_term( $term_id, $tt_id = 0, $taxonomy = '' ) {
	if ( in_array( $taxonomy, array( 'category', 'post_tag' ), true ) ) {
		rad_purge_page( 'articles' );
	}
}
add_action( 'created_term', 'rad_purge_articles_for_term', 30, 3 );
add_action( 'edited_term', 'rad_purge_articles_for_term', 30, 3 );
add_action( 'delete_term', 'rad_purge_articles_for_term', 30, 3 );
