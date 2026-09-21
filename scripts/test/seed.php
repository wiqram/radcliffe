<?php
/**
 * Sample content for the test site, so layouts can be checked without anyone's
 * real logos or articles. Run with:  scripts/test/wp-env.sh seed
 *
 * Idempotent: it only adds what is missing.
 *  - sponsors in every tier, with logos of the awkward kinds — wide with huge
 *    margins, tall, tiny, white-on-transparent, a white JPEG, a dark opaque one
 *  - enough articles to need a second page on the Articles page
 */

require_once __DIR__ . '/seed-lib.php';

if ( ! wp_count_posts( 'rad_sponsor' )->publish ) {
	$navy  = array( 15, 42, 74 );
	$white = array( 255, 255, 255 );
	$logos = array(
		array( 'Northwind Capital', 'gold-sponsor', 900, 600, function ( $im, $w, $h ) use ( $navy ) { imagefilledellipse( $im, $w / 2, 230, 200, 200, imagecolorallocate( $im, 200, 40, 40 ) ); rad_seed_text( $im, 'NORTHWIND', 260, 380, $navy, 5 ); }, false ),
		array( 'Harbour & Vale', 'dinner-sponsor', 1200, 800, function ( $im, $w, $h ) use ( $navy ) { rad_seed_text( $im, 'Harbour&Vale', 240, 380, $navy, 6 ); }, false ), // wide, huge margins
		array( 'Kestrel Labs', 'silver-sponsor', 300, 300, function ( $im, $w, $h ) use ( $navy ) { imagefilledrectangle( $im, 60, 40, 240, 180, imagecolorallocate( $im, 20, 90, 160 ) ); rad_seed_text( $im, 'KESTREL', 70, 220, $navy, 2 ); }, true ), // opaque white JPEG
		array( 'Tall Tower Group', 'silver-sponsor', 200, 520, function ( $im, $w, $h ) use ( $navy ) { imagefilledrectangle( $im, 70, 20, 130, 400, imagecolorallocate( $im, 30, 120, 90 ) ); rad_seed_text( $im, 'TOWER', 30, 430, $navy, 3 ); }, false ), // tall
		array( 'Tiny Co', 'silver-sponsor', 120, 40, function ( $im, $w, $h ) use ( $navy ) { rad_seed_text( $im, 'TINY CO', 8, 12, $navy, 1 ); }, false ), // tiny
		array( 'Lumen (white logo)', 'bronze-sponsors', 800, 300, function ( $im, $w, $h ) use ( $white ) { rad_seed_text( $im, 'LUMEN', 220, 120, $white, 8 ); }, false ), // white on transparent
		array( 'Onyx Dark', 'bronze-sponsors', 700, 260, function ( $im, $w, $h ) { imagefilledrectangle( $im, 0, 0, $w, $h, imagecolorallocate( $im, 12, 33, 56 ) ); rad_seed_text( $im, 'ONYX', 250, 90, array( 255, 255, 255 ), 8 ); }, true ), // opaque, dark background
		array( 'Wide Banner Partners', 'collaborators', 1600, 160, function ( $im, $w, $h ) use ( $navy ) { rad_seed_text( $im, 'WIDE BANNER PARTNERS', 60, 60, $navy, 5 ); }, false ),
		array( 'Square Mark', 'collaborators', 400, 400, function ( $im, $w, $h ) { imagefilledellipse( $im, 200, 200, 300, 300, imagecolorallocate( $im, 120, 40, 140 ) ); }, false ),
		array( 'Aster', 'collaborators', 500, 200, function ( $im, $w, $h ) use ( $navy ) { rad_seed_text( $im, 'aster', 150, 70, $navy, 5 ); }, false ),
		array( 'The Advisory Network', 'partners', 600, 240, function ( $im, $w, $h ) use ( $navy ) { rad_seed_text( $im, 'TAN', 230, 70, $navy, 8 ); }, false ),
		array( 'No Logo Yet Ltd', 'partners', 0, 0, null, false ),
	);

	$order = 0;
	foreach ( $logos as $logo ) {
		list( $name, $tier, $w, $h, $draw, $jpeg ) = $logo;
		$post = wp_insert_post( array( 'post_type' => 'rad_sponsor', 'post_status' => 'publish', 'post_title' => $name, 'menu_order' => ( ++$order ) * 10 ) );
		if ( $draw ) {
			set_post_thumbnail( $post, rad_seed_logo( sanitize_title( $name ), $w, $h, $draw, $jpeg ) );
		}
		wp_set_object_terms( $post, array( $tier ), 'rad_tier' );
		if ( 'partners' === $tier ) {
			update_post_meta( $post, '_rad_sponsor_role', 'Communications partner' );
		}
	}
	echo "sponsors: added " . count( $logos ) . "\n";
}

$want = 13;
$have = (int) wp_count_posts( 'post' )->publish;
$tags = array( 'Quantum', 'AI', 'Finance', 'Defence', 'Policy' );
$cats = array( 'CISI', 'LSE', 'The Quantum Insider', "Karina's Column" );
for ( $i = $have + 1; $i <= $want; $i++ ) {
	$body = "<!-- wp:paragraph -->\n<p>Sample article number $i. It exists so the Articles page has enough to need a second page.</p>\n<!-- /wp:paragraph -->\n\n<!-- wp:heading -->\n<h2 class=\"wp-block-heading\">A heading</h2>\n<!-- /wp:heading -->\n\n<!-- wp:paragraph -->\n<p>More text under the heading.</p>\n<!-- /wp:paragraph -->";
	$post = wp_insert_post(
		array(
			'post_type'    => 'post',
			'post_status'  => 'publish',
			'post_title'   => "Sample article $i",
			'post_content' => $body,
			'post_date'    => gmdate( 'Y-m-d H:i:s', strtotime( "-$i weeks" ) ),
		)
	);
	wp_set_post_terms( $post, array( $cats[ $i % count( $cats ) ] ), 'category' );
	wp_set_post_terms( $post, array( $tags[ $i % count( $tags ) ], $tags[ ( $i + 2 ) % count( $tags ) ] ), 'post_tag' );
}
echo 'articles: ' . wp_count_posts( 'post' )->publish . " published\n";
