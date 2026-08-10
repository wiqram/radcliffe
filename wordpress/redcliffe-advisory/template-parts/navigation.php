<?php
/**
 * Primary navigation.
 *
 * Uses the WordPress menu when one is assigned, and falls back to the
 * navigation the site was designed with, so the header is never empty.
 *
 * @package Redcliffe_Advisory
 */

defined( 'ABSPATH' ) || exit;

?>
<nav class="nav" id="nav" aria-label="<?php esc_attr_e( 'Primary', 'redcliffe-advisory' ); ?>">
<?php
if ( has_nav_menu( 'primary' ) ) {
	wp_nav_menu(
		array(
			'theme_location' => 'primary',
			'container'      => false,
			'items_wrap'     => '%3$s',
			'depth'          => 1,
			'walker'         => new Rad_Nav_Walker(),
		)
	);
} else {
	$nav = rad_pages();

	foreach ( $nav['nav'] as $item ) {
		printf(
			'<a href="%s"',
			esc_url( rad_url( $item['slug'] ) )
		);
		rad_nav_attributes( $item['slug'], isset( $item['classes'] ) ? $item['classes'] : '' );
		printf( '>%s</a>', esc_html( $item['label'] ) );
	}
}
?>
</nav>
