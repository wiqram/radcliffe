<?php
/**
 * Recreates what the owner had done by hand on the Summit page before the
 * Sponsors screen existed: tier headings with logos under them — first as
 * plain pictures, then as galleries made with the retired "Sponsor logos"
 * pattern, some logos twice — plus a sentence of unrelated text and a photo
 * that must survive the move. Run with:  scripts/test/wp-env.sh seed-legacy
 */

require_once __DIR__ . '/seed-lib.php';

$navy  = array( 15, 42, 74 );
$logos = array();
foreach ( array( 'multiverse' => 'MULTIVERSE', 'clifford-chance' => 'CLIFFORD', 'oqc' => 'OQC', 'delta-g' => 'DELTA G', 'cpd-endorsed' => 'CPD', 'iop' => 'IOP', 'mbda' => 'MBDA', 'farnborough' => 'FARNBOROUGH' ) as $slug => $label ) {
	$logos[ $slug ] = rad_seed_logo(
		'legacy-' . $slug . '-logo',
		600,
		300,
		function ( $im, $w, $h ) use ( $label, $navy ) {
			rad_seed_text( $im, $label, 40, 120, $navy, 5 );
		},
		'cpd-endorsed' === $slug
	);
}
$photo = rad_seed_logo( 'a-photo-of-the-hall', 800, 400, function ( $im, $w, $h ) { imagefilledrectangle( $im, 0, 0, $w, $h, imagecolorallocate( $im, 120, 140, 160 ) ); }, true );

$image = function ( $id, $size = 'full', $align = ' aligncenter' ) {
	return sprintf( "<!-- wp:image {\"id\":%1\$d,\"sizeSlug\":\"%2\$s\"} -->\n<figure class=\"wp-block-image%3\$s size-%2\$s\"><img src=\"%4\$s\" alt=\"\" class=\"wp-image-%1\$d\"/></figure>\n<!-- /wp:image -->", $id, $size, $align, wp_get_attachment_url( $id ) );
};
$gallery = function ( array $ids ) use ( $image ) {
	$inner = implode( "\n\n", array_map( function ( $id ) use ( $image ) { return $image( $id, 'large', '' ); }, $ids ) );
	return "<!-- wp:gallery {\"linkTo\":\"none\",\"className\":\"rad-logos\"} -->\n<figure class=\"wp-block-gallery has-nested-images columns-default is-cropped rad-logos\">$inner</figure>\n<!-- /wp:gallery -->";
};
$h2 = function ( $t ) { return "<!-- wp:heading -->\n<h2 class=\"wp-block-heading\">$t</h2>\n<!-- /wp:heading -->"; };
$p  = function ( $t ) { return "<!-- wp:paragraph -->\n<p>$t</p>\n<!-- /wp:paragraph -->"; };

$blocks = array(
	$image( $photo, 'large', '' ),
	$h2( 'Gold Sponsor:' ), $image( $logos['multiverse'] ),
	$h2( 'Dinner Sponsor:' ), $image( $logos['clifford-chance'], 'large' ),
	$h2( 'Silver Sponsor:' ), $image( $logos['oqc'] ),
	$h2( 'Bronze Sponsors:' ), $gallery( array( $logos['delta-g'], $logos['cpd-endorsed'] ) ),
	$h2( 'Collaborators:' ),
	$p( 'Gold Sponsor' ), $gallery( array( $logos['multiverse'] ) ),
	$p( 'Dinner Sponsor' ), $gallery( array( $logos['clifford-chance'] ) ),
	$p( 'Bronze Sponsors' ), $gallery( array( $logos['delta-g'], $logos['cpd-endorsed'] ) ),
	$p( 'Collaborators' ), $gallery( array( $logos['iop'], $logos['mbda'], $logos['farnborough'] ) ),
	$p( '' ),
	$p( 'With thanks to everyone who makes the Summit possible.' ),
);

$page = get_page_by_path( 'summit' );
kses_remove_filters();
wp_update_post( array( 'ID' => $page->ID, 'post_content' => implode( "\n\n", $blocks ) ) );
kses_init_filters();
echo "summit page now holds hand-placed logos for 8 organisations (5 tiers), plus a photo and a sentence of thanks\n";
