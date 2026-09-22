<?php
/**
 * Redirects for old addresses, so links people already have — on the Summit
 * page, in emails, in search results — keep working once redcliffeadvisory.com
 * is connected to this site.
 *
 * @package Redcliffe_Advisory
 */

defined( 'ABSPATH' ) || exit;

/**
 * Old address (no domain, no leading or trailing slash) -> new page slug.
 *
 * @return array
 */
function rad_legacy_redirects() {
	return array(
		'the-city-quantum-and-ai-summit'             => 'summit',
		'the-city-quantum-and-ai-summit-2026'        => 'summit',
		'the-city-quantum-and-ai-summit-2025'        => 'summit',
		'the-city-quantum-and-ai-summit-2026-agenda' => 'agenda',
		'the-city-quantum-and-ai-summit-2025-agenda' => 'agenda',
		'summit-2024'                                => 'summit',
		'summit-2023'                                => 'summit',
		'summit-2023-old'                            => 'summit',
		'whos-who'                                   => 'who',
		'the-city'                                   => 'city',
		'chairman-avisory'                           => 'practice',
	);
}

/**
 * Send a visitor on an old address to the matching page, permanently.
 *
 * Runs only when nothing else already answered the request (is_404()), so it
 * can never intercept a real page, post or file. Registered at priority 1, so
 * it runs and exits before core's own redirect_canonical() (priority 10 on
 * this same hook): otherwise, for a mapped address that happens to share
 * words with an old post's slug, WordPress's own "did you mean this post?"
 * guess (redirect_guess_404_permalink()) fires first and wins, sending the
 * visitor to that unrelated post instead of the page we deliberately chose.
 */
function rad_redirect_legacy_urls() {
	if ( ! is_404() || empty( $_SERVER['REQUEST_URI'] ) ) {
		return;
	}

	$request = sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) );
	$path    = trim( (string) wp_parse_url( $request, PHP_URL_PATH ), '/' );
	$map     = rad_legacy_redirects();

	if ( isset( $map[ $path ] ) ) {
		wp_redirect( rad_url( $map[ $path ] ), 301 );
		exit;
	}
}
add_action( 'template_redirect', 'rad_redirect_legacy_urls', 1 );
