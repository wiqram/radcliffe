<?php
/**
 * Karina's LinkedIn posts on the homepage.
 *
 * The posts come from a SociableKit feed, as they did on the previous website.
 * The feed's number and the profile address are settings in the Customizer
 * (Homepage), and the widget itself is only fetched once a visitor scrolls
 * close to it (assets/js/site.js), so it never slows the top of the page.
 * Without JavaScript, or if the feed is ever unavailable, the link to the
 * profile below it still works.
 *
 * @package Redcliffe_Advisory
 */

defined( 'ABSPATH' ) || exit;

/**
 * Print the feed and the link to the LinkedIn profile.
 */
function rad_linkedin_feed() {
	$embed   = preg_replace( '/\D/', '', rad_saved_setting( 'home.linkedin.embedId', '114149' ) );
	$profile = rad_saved_setting( 'home.linkedin.profileUrl', 'https://www.linkedin.com/in/karina-robinson/' );

	if ( $embed ) {
		// The feed is long (every recent post), so the page shows its first
		// rows and a "Show more posts" button opens the rest (assets/js/site.js).
		printf(
			'<div class="linkedin-feed is-collapsed reveal" id="linkedin-feed" data-linkedin-src="%s"><div class="sk-ww-linkedin-profile-post" data-embed-id="%s"></div></div>',
			esc_url( 'https://widgets.sociablekit.com/linkedin-profile-posts/widget.js' ),
			esc_attr( $embed )
		);
	}

	echo '<p class="linkedin-actions">';
	if ( $embed ) {
		printf(
			'<button type="button" class="btn-link linkedin-toggle" aria-controls="linkedin-feed" aria-expanded="false" hidden><span data-rad="home.linkedin.moreLabel">%s</span><span class="arr">↓</span></button>',
			wp_kses_post( rad_get_html( 'home.linkedin.moreLabel' ) )
		);
	}
	if ( $profile ) {
		rad_button( 'btn-link', $profile, 'home.linkedin.followLabel', '↗' );
	}
	echo "</p>\n";
}
