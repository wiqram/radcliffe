<?php
/**
 * Throwaway test-only mu-plugin: logs the browser in as user 1 when the URL
 * carries ?rad_test_login=1, then redirects so the new cookie is in effect.
 * Mounted into the Docker test site only; never part of the theme.
 */
add_action( 'init', function () {
	if ( empty( $_GET['rad_test_login'] ) || is_user_logged_in() ) {
		return;
	}
	wp_set_current_user( 1 );
	wp_set_auth_cookie( 1, true );
	wp_safe_redirect( remove_query_arg( 'rad_test_login' ) );
	exit;
} );
