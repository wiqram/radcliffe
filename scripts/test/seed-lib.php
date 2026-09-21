<?php
/**
 * Helpers shared by the test seeds: draw a logo with GD and add it to the Media Library.
 */

require_once ABSPATH . 'wp-admin/includes/file.php';
require_once ABSPATH . 'wp-admin/includes/media.php';
require_once ABSPATH . 'wp-admin/includes/image.php';

/** Draw a logo with GD and add it to the media library. Returns the attachment ID. */
function rad_seed_logo( $name, $w, $h, $draw, $jpeg = false ) {
	$im = imagecreatetruecolor( $w, $h );
	imagealphablending( $im, false );
	imagesavealpha( $im, true );
	imagefill( $im, 0, 0, imagecolorallocatealpha( $im, 255, 255, 255, $jpeg ? 0 : 127 ) );
	imagealphablending( $im, true );
	$draw( $im, $w, $h );

	$dir  = wp_upload_dir();
	$file = $dir['path'] . '/' . sanitize_file_name( $name ) . ( $jpeg ? '.jpg' : '.png' );
	$jpeg ? imagejpeg( $im, $file, 92 ) : imagepng( $im, $file );

	$id = wp_insert_attachment(
		array(
			'post_mime_type' => $jpeg ? 'image/jpeg' : 'image/png',
			'post_title'     => $name,
			'post_status'    => 'inherit',
		),
		$file
	);
	wp_update_attachment_metadata( $id, wp_generate_attachment_metadata( $id, $file ) );

	return $id;
}

function rad_seed_text( $im, $text, $x, $y, $colour, $scale = 1 ) {
	$c = imagecolorallocate( $im, $colour[0], $colour[1], $colour[2] );
	// GD's built-in font is tiny; enlarge by drawing it small and scaling up.
	$w = imagefontwidth( 5 ) * strlen( $text );
	$h = imagefontheight( 5 );
	$t = imagecreatetruecolor( $w, $h );
	imagealphablending( $t, false );
	imagesavealpha( $t, true );
	imagefill( $t, 0, 0, imagecolorallocatealpha( $t, 0, 0, 0, 127 ) );
	imagealphablending( $t, true );
	imagestring( $t, 5, 0, 0, $text, $c );
	imagealphablending( $im, true );
	imagecopyresized( $im, $t, $x, $y, 0, 0, $w * $scale, $h * $scale, $w, $h );
}

