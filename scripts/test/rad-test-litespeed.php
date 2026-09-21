<?php
/**
 * Test stand-in for the LiteSpeed Cache plugin: it answers the same actions the
 * plugin does and writes down what the theme asked for, so scripts/test/cache.sh
 * can check it. Copied into wp-content/mu-plugins by wp-env.sh. Never installed on a real site.
 */
$rad_log = function ( $line ) {
	file_put_contents( WP_CONTENT_DIR . '/rad-cache.log', $line . "\n", FILE_APPEND );
};

add_action( 'litespeed_purge_all', function () use ( $rad_log ) {
	$rad_log( 'purge_all' );
} );
add_action( 'litespeed_purge_post', function ( $id ) use ( $rad_log ) {
	$rad_log( 'purge_post:' . get_post_field( 'post_name', $id ) );
} );
add_action( 'litespeed_control_set_ttl', function ( $ttl ) use ( $rad_log ) {
	$rad_log( 'ttl:' . $ttl );
}, 10, 2 );
