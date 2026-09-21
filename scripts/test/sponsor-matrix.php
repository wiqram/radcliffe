<?php
/**
 * What happens to the Summit page when the owner does the things owners do with sponsors: logos of very
 * odd shapes, a file the server cannot read, a huge one, a WebP, nine in one tier, a website address,
 * deleting a tier that has sponsors in it, editing a picture in the Media Library, deleting a picture.
 *
 *   scripts/test/wp-env.sh sponsor-matrix
 *
 * Each line is PASS or FAIL; exits 1 if any failed. Rebuilds the sponsors from scratch.
 */

require_once __DIR__ . '/seed-lib.php';

$failed = 0;
function rad_m_check( $name, $ok, $detail = '' ) {
	global $failed;
	if ( ! $ok ) {
		$failed++;
	}
	echo ( $ok ? 'PASS' : 'FAIL' ) . '  ' . $name . ( $ok || '' === $detail ? '' : ' — ' . $detail ) . "\n";
}
function rad_m_render() {
	wp_cache_flush();
	rad_sponsor_tiers( true );
	ob_start();
	rad_render_sponsors();
	return ob_get_clean();
}
function rad_m_tile( $html, $name ) {
	return preg_match( '#<li class="sponsor [^"]*"[^>]*>(?:(?!</li>).)*' . preg_quote( esc_html( $name ), '#' ) . '(?:(?!</li>).)*</li>#s', $html, $m ) ? $m[0] : '';
}
function rad_m_sponsor( $name, $tier, $attachment = 0, $url = '' ) {
	$id = wp_insert_post( array( 'post_type' => 'rad_sponsor', 'post_status' => 'publish', 'post_title' => $name ) );
	wp_set_object_terms( $id, array( $tier ), 'rad_tier' );
	if ( $attachment ) {
		set_post_thumbnail( $id, $attachment );
	}
	if ( $url ) {
		update_post_meta( $id, '_rad_sponsor_url', $url );
	}
	return $id;
}
function rad_m_image( $slug, $w, $h, $jpeg = false ) {
	return rad_seed_logo( $slug, $w, $h, function ( $im, $w, $h ) {
		rad_seed_text( $im, 'LOGO', (int) ( $w * 0.3 ), (int) ( $h * 0.35 ), array( 15, 42, 74 ), (int) max( 1, min( 8, $w / 100 ) ) );
	}, $jpeg );
}

foreach ( get_posts( array( 'post_type' => 'rad_sponsor', 'post_status' => 'any', 'numberposts' => -1, 'fields' => 'ids' ) ) as $old ) {
	wp_delete_post( $old, true );
}

// 1. Awkward shapes and sizes.
$wide = rad_m_sponsor( 'Wide Eight To One', 'gold-sponsor', rad_m_image( 'm-wide', 2400, 300 ) );
$tall = rad_m_sponsor( 'Tall One To Eight', 'dinner-sponsor', rad_m_image( 'm-tall', 300, 2400 ) );
$tiny = rad_m_sponsor( 'Tiny Sixty By Thirty', 'silver-sponsor', rad_m_image( 'm-tiny', 60, 30 ) );
$html = rad_m_render();
foreach ( array( 'Wide Eight To One', 'Tall One To Eight', 'Tiny Sixty By Thirty' ) as $name ) {
	$tile = rad_m_tile( $html, $name );
	rad_m_check( "$name: has a tile with a picture that has a size", $tile && preg_match( '#<img [^>]*width="\d+" height="\d+"#', $tile ), $tile ? '' : 'no tile' );
}

rad_m_check( 'a small logo carries its own size so it is not blown up', (bool) preg_match( '#style="[^"]*--lw:\d+;--lh:\d+#', rad_m_tile( $html, 'Tiny Sixty By Thirty' ) ) );

// 2. A file the server cannot read, a very large one, and a WebP.
$dir = wp_upload_dir();
file_put_contents( $dir['path'] . '/corrupt-logo.png', 'this is not really a picture' );
$corrupt = wp_insert_attachment( array( 'post_mime_type' => 'image/png', 'post_title' => 'corrupt', 'post_status' => 'inherit' ), $dir['path'] . '/corrupt-logo.png' );
rad_m_sponsor( 'Unreadable File Ltd', 'collaborators', $corrupt );

$big_file = $dir['path'] . '/huge-logo.png';
$big      = imagecreate( 4200, 3200 ); // Palette image: cheap to make, 13 megapixels.
imagecolorallocate( $big, 255, 255, 255 );
imagepng( $big, $big_file, 9 );
$huge = wp_insert_attachment( array( 'post_mime_type' => 'image/png', 'post_title' => 'huge', 'post_status' => 'inherit' ), $big_file );
require_once ABSPATH . 'wp-admin/includes/image.php';
wp_update_attachment_metadata( $huge, wp_generate_attachment_metadata( $huge, $big_file ) );
rad_m_sponsor( 'Huge File Ltd', 'collaborators', $huge );

$webp = 0;
if ( function_exists( 'imagewebp' ) ) {
	$src = imagecreatetruecolor( 800, 300 );
	imagefilledrectangle( $src, 0, 0, 800, 300, imagecolorallocate( $src, 255, 255, 255 ) );
	rad_seed_text( $src, 'WEBP', 250, 100, array( 15, 42, 74 ), 8 );
	imagewebp( $src, $dir['path'] . '/webp-logo.webp', 80 );
	$webp = wp_insert_attachment( array( 'post_mime_type' => 'image/webp', 'post_title' => 'webp', 'post_status' => 'inherit' ), $dir['path'] . '/webp-logo.webp' );
	wp_update_attachment_metadata( $webp, wp_generate_attachment_metadata( $webp, $dir['path'] . '/webp-logo.webp' ) );
	rad_m_sponsor( 'Webp Format Ltd', 'collaborators', $webp );
}
$html = rad_m_render();
$corrupt_tile = rad_m_tile( $html, 'Unreadable File Ltd' );
rad_m_check( 'an unreadable logo file does not break the page and still shows the name', '' !== $corrupt_tile );
rad_m_check( 'a 13-megapixel logo still gets a tile with a picture', (bool) preg_match( '#<img #', rad_m_tile( $html, 'Huge File Ltd' ) ) );
if ( $webp ) {
	rad_m_check( 'a WebP logo gets a tile with a picture', (bool) preg_match( '#<img [^>]*width="\d+"#', rad_m_tile( $html, 'Webp Format Ltd' ) ) );
}

// 3. Nine in one tier, and a website address.
for ( $i = 1; $i <= 9; $i++ ) {
	rad_m_sponsor( "Bronze Number $i", 'bronze-sponsors', rad_m_image( "m-bronze-$i", 500, 200 ) );
}
rad_m_sponsor( 'Linked Company', 'partners', rad_m_image( 'm-linked', 500, 200 ), 'https://example.com/linked' );
$html = rad_m_render();
$present = 0;
for ( $i = 1; $i <= 9; $i++ ) {
	$present += rad_m_tile( $html, "Bronze Number $i" ) ? 1 : 0;
}
rad_m_check( 'nine sponsors in one tier all get a tile', 9 === $present, "found $present" );
rad_m_check( 'a sponsor with a website is a link that opens in a new tab', (bool) preg_match( '#<a [^>]*href="https://example.com/linked"[^>]*target="_blank"[^>]*rel="[^"]*noopener#', rad_m_tile( $html, 'Linked Company' ) ) );
rad_m_check( 'a sponsor without a website is not a link', ! preg_match( '#<a #', rad_m_tile( $html, 'Bronze Number 1' ) ) );

// 4. A tier is deleted while it still has sponsors in it.
$keep  = wp_insert_term( 'Temporary tier', 'rad_tier' );
$t1    = rad_m_sponsor( 'Orphan One', 'temporary-tier', rad_m_image( 'm-o1', 500, 200 ) );
$t2    = rad_m_sponsor( 'Orphan Two', 'temporary-tier', rad_m_image( 'm-o2', 500, 200 ) );
$before = substr_count( rad_m_render(), '<li class="sponsor ' );
wp_delete_term( $keep['term_id'], 'rad_tier' );
$after_html = rad_m_render();
$in_collaborators = preg_match( '#<h3 class="sponsor-tier-title"[^>]*>Collaborators</h3>(.*?)(?=<section class="sponsor-tier|</div>\s*$)#s', $after_html, $cm ) ? $cm[1] : '';
rad_m_check( 'deleting a tier moves its sponsors to Collaborators, so they are still shown under a heading', str_contains( $in_collaborators, 'Orphan One' ) && str_contains( $in_collaborators, 'Orphan Two' ), 'sponsors before ' . $before . ', tiles after ' . substr_count( $after_html, '<li class="sponsor ' ) );
$moved = get_transient( 'rad_tier_moved_' . get_current_user_id() );
rad_m_check( 'the owner is told where they went', is_array( $moved ) && 2 === (int) $moved['count'] && 'Collaborators' === $moved['to'] );
delete_transient( 'rad_tier_moved_' . get_current_user_id() );

// 5. A picture edited in the Media Library (same picture record, a new file).
$editable = rad_m_image( 'm-edit-before', 800, 200 );
$edited   = rad_m_sponsor( 'Edited Picture Ltd', 'collaborators', $editable );
$one      = rad_sponsor_logo( $edited );
$file     = get_attached_file( $editable );
$new      = dirname( $file ) . '/m-edit-after-e' . time() . '.png';
$img      = imagecreatetruecolor( 200, 600 );
imagesavealpha( $img, true );
imagefill( $img, 0, 0, imagecolorallocatealpha( $img, 0, 0, 0, 127 ) );
imagefilledrectangle( $img, 20, 20, 180, 580, imagecolorallocate( $img, 200, 30, 30 ) );
imagepng( $img, $new );
update_attached_file( $editable, $new );
wp_update_attachment_metadata( $editable, wp_generate_attachment_metadata( $editable, $new ) ); // What WordPress's own image editor does.
clean_post_cache( $edited );
$two = rad_sponsor_logo( $edited );
rad_m_check( 'editing a logo in the Media Library shows the new picture, not the old one', $one && $two && ( $one['width'] !== $two['width'] || $one['height'] !== $two['height'] ) && $two['height'] > $two['width'], 'before ' . json_encode( array( $one['width'] ?? 0, $one['height'] ?? 0 ) ) . ' after ' . json_encode( array( $two['width'] ?? 0, $two['height'] ?? 0 ) ) );

// 6. A picture deleted from the Media Library while a sponsor uses it.
$gone = rad_m_sponsor( 'Picture Removed Ltd', 'collaborators', rad_m_image( 'm-gone', 500, 200 ) );
wp_delete_attachment( get_post_thumbnail_id( $gone ), true );
$html = rad_m_render();
$tile = rad_m_tile( $html, 'Picture Removed Ltd' );
rad_m_check( 'a sponsor whose picture was deleted still shows its name, with no broken picture', '' !== $tile && ! preg_match( '#<img #', $tile ) );

// 7. No sponsors at all: the designed placeholder names come back. (Pass "keep" to leave the sponsors above in place and look at them.)
if ( isset( $args[0] ) && 'keep' === $args[0] ) {
	exit( $failed ? 1 : 0 );
}
foreach ( get_posts( array( 'post_type' => 'rad_sponsor', 'post_status' => 'any', 'numberposts' => -1, 'fields' => 'ids' ) ) as $sid ) {
	wp_delete_post( $sid, true );
}
rad_sponsor_tiers( true );
rad_m_check( 'with no sponsors left, rad_has_sponsors() is false so the designed names return', ! rad_has_sponsors() );

exit( $failed ? 1 : 0 );
