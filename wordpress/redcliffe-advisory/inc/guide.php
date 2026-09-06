<?php
/**
 * The owner's guide, inside the dashboard.
 *
 * Whoever looks after the site should never need to find a document on a
 * disk: the guide in docs/WEBSITE-GUIDE.md is turned into inc/guide-content.php
 * at build time and shown under "Website guide" in the admin menu, with a
 * dashboard box of quick links.
 *
 * @package Redcliffe_Advisory
 */

defined( 'ABSPATH' ) || exit;

/**
 * "Website guide" in the admin menu.
 */
function rad_guide_menu() {
	add_menu_page(
		__( 'Website guide', 'redcliffe-advisory' ),
		__( 'Website guide', 'redcliffe-advisory' ),
		'edit_pages',
		'rad-guide',
		'rad_guide_page',
		'dashicons-book-alt',
		3
	);
}
add_action( 'admin_menu', 'rad_guide_menu' );

/**
 * Render the guide.
 */
function rad_guide_page() {
	$file = get_theme_file_path( 'inc/guide-content.php' );
	$html = file_exists( $file ) ? require $file : '';
	?>
	<div class="wrap rad-guide">
		<div class="rad-guide-body">
			<?php echo wp_kses_post( $html ); ?>
		</div>
	</div>
	<?php
}

/**
 * Make the guide comfortable to read.
 *
 * @param string $hook Current admin page.
 */
function rad_guide_styles( $hook ) {
	if ( 'toplevel_page_rad-guide' !== $hook ) {
		return;
	}

	$css = '
	.rad-guide-body { max-width: 860px; background: #fff; padding: 32px 40px 48px; margin-top: 16px; border: 1px solid #dcdcde; font-size: 15px; line-height: 1.65; color: #1d2327; }
	.rad-guide-body h1 { font-size: 28px; line-height: 1.2; margin: 0 0 12px; }
	.rad-guide-body h2 { font-size: 21px; margin: 40px 0 12px; padding-top: 24px; border-top: 1px solid #dcdcde; }
	.rad-guide-body h3 { font-size: 16px; margin: 24px 0 8px; }
	.rad-guide-body p, .rad-guide-body li { font-size: 15px; }
	.rad-guide-body ul, .rad-guide-body ol { margin-left: 24px; }
	.rad-guide-body ul { list-style: disc; }
	.rad-guide-body li { margin-bottom: 6px; }
	.rad-guide-body table { border-collapse: collapse; width: 100%; margin: 12px 0 20px; }
	.rad-guide-body th, .rad-guide-body td { border: 1px solid #dcdcde; padding: 8px 10px; text-align: left; vertical-align: top; }
	.rad-guide-body th { background: #f6f7f7; }
	.rad-guide-body code { background: #f0f0f1; padding: 1px 5px; font-size: 13px; }
	.rad-guide-body hr { border: 0; border-top: 1px solid #dcdcde; margin: 24px 0; }
	.rad-guide-body blockquote { border-left: 4px solid #2E6CA6; margin: 12px 0; padding: 4px 16px; background: #f6f9fc; }
	';
	wp_add_inline_style( 'wp-admin', $css );
}
add_action( 'admin_enqueue_scripts', 'rad_guide_styles' );

/**
 * Quick links on the dashboard home screen.
 */
function rad_dashboard_widget() {
	wp_add_dashboard_widget(
		'rad_quick_links',
		__( 'Looking after the website', 'redcliffe-advisory' ),
		'rad_dashboard_widget_render'
	);

	// Put it first.
	global $wp_meta_boxes;
	if ( isset( $wp_meta_boxes['dashboard']['normal']['core']['rad_quick_links'] ) ) {
		$widget = $wp_meta_boxes['dashboard']['normal']['core']['rad_quick_links'];
		unset( $wp_meta_boxes['dashboard']['normal']['core']['rad_quick_links'] );
		$wp_meta_boxes['dashboard']['normal']['core'] = array_merge( // phpcs:ignore WordPress.WP.GlobalVariablesOverride
			array( 'rad_quick_links' => $widget ),
			$wp_meta_boxes['dashboard']['normal']['core']
		);
	}
}
add_action( 'wp_dashboard_setup', 'rad_dashboard_widget' );

/**
 * The widget's contents.
 */
function rad_dashboard_widget_render() {
	$links = array(
		array(
			admin_url( 'customize.php' ),
			__( 'Change the words and photographs already on the site', 'redcliffe-advisory' ),
			__( 'Appearance → Customize', 'redcliffe-advisory' ),
		),
		array(
			admin_url( 'edit.php?post_type=page' ),
			__( 'Add a new section, photograph or gallery to a page', 'redcliffe-advisory' ),
			__( 'Pages → Edit', 'redcliffe-advisory' ),
		),
		array(
			admin_url( 'edit.php?post_type=rad_enquiry' ),
			__( 'Read messages from the contact form', 'redcliffe-advisory' ),
			__( 'Enquiries', 'redcliffe-advisory' ),
		),
		array(
			admin_url( 'admin.php?page=rad-guide' ),
			__( 'Read the step-by-step guide', 'redcliffe-advisory' ),
			__( 'Website guide', 'redcliffe-advisory' ),
		),
	);

	echo '<ul style="margin:0">';
	foreach ( $links as $link ) {
		printf(
			'<li style="margin-bottom:10px"><a href="%s" style="font-weight:600">%s</a><br /><span style="color:#646970">%s</span></li>',
			esc_url( $link[0] ),
			esc_html( $link[1] ),
			esc_html( $link[2] )
		);
	}
	echo '</ul>';
}
