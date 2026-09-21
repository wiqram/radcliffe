<?php
/**
 * A site the way it looks after an owner has "done things": every editable
 * picture swapped for one of an awkward shape, every editable piece of text made
 * longer, more menu items, and an article with everything a writer might paste.
 * Used by scripts/test/stress.js. Run with:
 *   scripts/test/wp-env.sh stress-seed [wide|tall|tiny|square|mixed]
 *
 * Nothing here is "wrong" input: these are the things any owner does, such as
 * uploading a portrait photograph where the design has a landscape one.
 */

require_once __DIR__ . '/seed-lib.php';

$mode = isset( $args[0] ) ? $args[0] : ( getenv( 'RAD_STRESS_IMAGES' ) ?: 'mixed' );

/** A picture with a grid, a circle and its own shape written on it, so stretching shows. */
function rad_stress_image( $slug, $w, $h ) {
	return rad_seed_logo(
		'stress-' . $slug,
		$w,
		$h,
		function ( $im, $w, $h ) {
			$bg   = imagecolorallocate( $im, 226, 232, 240 );
			$line = imagecolorallocate( $im, 100, 116, 139 );
			$ink  = imagecolorallocate( $im, 15, 42, 74 );
			imagefilledrectangle( $im, 0, 0, $w, $h, $bg );
			for ( $x = 0; $x < $w; $x += 100 ) {
				imageline( $im, $x, 0, $x, $h, $line );
			}
			for ( $y = 0; $y < $h; $y += 100 ) {
				imageline( $im, 0, $y, $w, $y, $line );
			}
			$r = (int) ( min( $w, $h ) * 0.4 );
			imagefilledellipse( $im, (int) ( $w / 2 ), (int) ( $h / 2 ), $r * 2, $r * 2, imagecolorallocate( $im, 200, 60, 60 ) );
			imagerectangle( $im, 1, 1, $w - 2, $h - 2, $ink );
			rad_seed_text( $im, "{$w}x{$h}", 12, 12, array( 15, 42, 74 ), (int) max( 1, min( 6, $w / 120 ) ) );
		},
		false
	);
}

$shapes = array(
	'wide'   => array( 3000, 500 ),
	'tall'   => array( 500, 3000 ),
	'tiny'   => array( 40, 40 ),
	'square' => array( 1200, 1200 ),
);
$made   = array();
foreach ( $shapes as $name => $size ) {
	$made[ $name ] = rad_stress_image( $name, $size[0], $size[1] );
}

// Every editable picture: the opposite of what the design expects.
$images = array_keys( rad_defaults( 'images' ) );
$cycle  = 'mixed' === $mode ? array_keys( $shapes ) : array( $mode );
$i      = 0;
foreach ( $images as $key ) {
	set_theme_mod( rad_mod_name( $key ), $made[ $cycle[ $i++ % count( $cycle ) ] ] );
}

// Every editable piece of text, longer. One is a single long word (an email address or a web address).
$long = ' and then a good deal more wording than the design allowed for, as owners so often add';
$n    = 0;
foreach ( rad_defaults( 'text' ) as $key => $value ) {
	if ( false !== strpos( $key, 'linkedin' ) ) {
		continue;
	}
	$extra = ( 0 === ( ++$n % 37 ) ) ? ' averyveryverylongunbrokenwordlikeanemailaddressorawebaddress@example-organisation-name.co.uk' : $long;
	set_theme_mod( rad_mod_name( $key ), wp_strip_all_tags( $value ) . $extra );
}

// A menu with more in it than the design has, and long labels.
$menu = wp_get_nav_menu_object( 'Primary menu' );
if ( ! $menu ) {
	$id   = wp_create_nav_menu( 'Primary menu' );
	$menu = wp_get_nav_menu_object( $id );
}
$have = wp_list_pluck( (array) wp_get_nav_menu_items( $menu->term_id ), 'title' );
foreach ( array( 'Chair Advisory', "Who's who", 'The City', 'Summit', 'Agenda', 'Articles', 'Ethics', 'Contact', 'Diversity and Inclusion', 'Testimonials', 'Press and Media Enquiries' ) as $label ) {
	if ( in_array( $label, $have, true ) && 'Summit' !== $label ) {
		continue; // Already added by an earlier run.
	}
	if ( 'Summit' === $label && in_array( 'Press and Media Enquiries', $have, true ) ) {
		continue;
	}
	wp_update_nav_menu_item( $menu->term_id, 0, array( 'menu-item-title' => $label, 'menu-item-url' => home_url( '/' . sanitize_title( $label ) . '/' ), 'menu-item-status' => 'publish', 'menu-item-type' => 'custom' ) );
}
$locations            = get_theme_mod( 'nav_menu_locations', array() );
$locations['primary'] = $menu->term_id;
set_theme_mod( 'nav_menu_locations', $locations );

// An article with everything a writer might paste in.
if ( ! get_page_by_path( 'stress-article', OBJECT, 'post' ) ) {
	$wide  = wp_get_attachment_url( $made['wide'] );
	$tall  = wp_get_attachment_url( $made['tall'] );
	$body  = "<!-- wp:paragraph -->\n<p>An opening paragraph with a very long web address that has no spaces in it: https://www.example-organisation.co.uk/a/very/long/path/that/goes/on/and/on/and/on/and/on/and/on/and/on/and/on/index.html</p>\n<!-- /wp:paragraph -->\n";
	$body .= "<!-- wp:heading {\"level\":1} -->\n<h1 class=\"wp-block-heading\">A first-level heading pasted from a document</h1>\n<!-- /wp:heading -->\n";
	$body .= "<!-- wp:heading -->\n<h2 class=\"wp-block-heading\">A second-level heading that is quite long so that it has to wrap onto more than one line on a phone</h2>\n<!-- /wp:heading -->\n";
	$body .= "<!-- wp:heading {\"level\":3} -->\n<h3 class=\"wp-block-heading\">A third-level heading</h3>\n<!-- /wp:heading -->\n";
	$body .= "<!-- wp:image {\"id\":{$made['wide']},\"sizeSlug\":\"full\",\"align\":\"full\"} -->\n<figure class=\"wp-block-image alignfull size-full\"><img src=\"{$wide}\" alt=\"A very wide picture set to full width\" class=\"wp-image-{$made['wide']}\"/></figure>\n<!-- /wp:image -->\n";
	$body .= "<!-- wp:image {\"id\":{$made['tall']},\"sizeSlug\":\"full\"} -->\n<figure class=\"wp-block-image size-full\"><img src=\"{$tall}\" alt=\"A very tall picture\" class=\"wp-image-{$made['tall']}\"/><figcaption class=\"wp-element-caption\">A caption under a tall picture.</figcaption></figure>\n<!-- /wp:image -->\n";
	$body .= "<!-- wp:quote -->\n<blockquote class=\"wp-block-quote\"><p>A quotation long enough to run over several lines, so that the way it is centred and spaced can be seen properly on both a wide screen and a narrow one.</p><cite>Someone Important</cite></blockquote>\n<!-- /wp:quote -->\n";
	$body .= "<!-- wp:pullquote -->\n<figure class=\"wp-block-pullquote\"><blockquote><p>A pull quotation.</p><cite>Someone Else</cite></blockquote></figure>\n<!-- /wp:pullquote -->\n";
	$body .= "<!-- wp:list -->\n<ul class=\"wp-block-list\"><li>A first point</li><li>A second point that is a good deal longer than the first, to wrap</li><li>A third</li></ul>\n<!-- /wp:list -->\n";
	$body .= "<!-- wp:table -->\n<figure class=\"wp-block-table\"><table><thead><tr><th>Organisation</th><th>Role</th><th>Date</th><th>Location</th><th>Notes</th><th>More</th></tr></thead><tbody><tr><td>A long organisation name here</td><td>Speaker</td><td>7 October 2026</td><td>Mansion House, London EC4</td><td>Some notes that run on</td><td>Even more</td></tr></tbody></table></figure>\n<!-- /wp:table -->\n";
	$body .= "<!-- wp:code -->\n<pre class=\"wp-block-code\"><code>a_very_long_line_of_code_that_does_not_wrap = call_something(with, many, arguments, that, go, on, and, on, and, on, and, on)</code></pre>\n<!-- /wp:code -->\n";
	$body .= "<!-- wp:embed {\"url\":\"https://www.youtube.com/watch?v=dQw4w9WgXcQ\",\"type\":\"video\",\"providerNameSlug\":\"youtube\",\"responsive\":true} -->\n<figure class=\"wp-block-embed is-type-video is-provider-youtube wp-block-embed-youtube\"><div class=\"wp-block-embed__wrapper\">\nhttps://www.youtube.com/watch?v=dQw4w9WgXcQ\n</div></figure>\n<!-- /wp:embed -->\n";
	$body .= "<!-- wp:buttons -->\n<div class=\"wp-block-buttons\"><!-- wp:button -->\n<div class=\"wp-block-button\"><a class=\"wp-block-button__link wp-element-button\" href=\"https://example.com/\">A button with a fairly long label on it</a></div>\n<!-- /wp:button --></div>\n<!-- /wp:buttons -->\n";
	$body .= "<!-- wp:gallery {\"linkTo\":\"none\"} -->\n<figure class=\"wp-block-gallery has-nested-images columns-default is-cropped\"><!-- wp:image {\"id\":{$made['tall']}} -->\n<figure class=\"wp-block-image\"><img src=\"{$tall}\" alt=\"\" class=\"wp-image-{$made['tall']}\"/></figure>\n<!-- /wp:image --><!-- wp:image {\"id\":{$made['wide']}} -->\n<figure class=\"wp-block-image\"><img src=\"{$wide}\" alt=\"\" class=\"wp-image-{$made['wide']}\"/></figure>\n<!-- /wp:image --></figure>\n<!-- /wp:gallery -->\n";
	wp_insert_post( array( 'post_type' => 'post', 'post_status' => 'publish', 'post_name' => 'stress-article', 'post_title' => 'An article with a title that is long enough to need several lines on a phone and a lot of them', 'post_content' => $body, 'post_excerpt' => 'An excerpt that is also fairly long, so that the card on the Articles page has to cope with more words than the design expected, on every screen size.' ) );
}

// Article with no featured image and a very short title (the other extreme).
if ( ! get_page_by_path( 'stress-short', OBJECT, 'post' ) ) {
	wp_insert_post( array( 'post_type' => 'post', 'post_status' => 'publish', 'post_name' => 'stress-short', 'post_title' => 'Short', 'post_content' => "<!-- wp:paragraph -->\n<p>Just one line.</p>\n<!-- /wp:paragraph -->" ) );
}

echo "stress state ready: images=$mode, text lengthened, menu extended, articles added\n";
