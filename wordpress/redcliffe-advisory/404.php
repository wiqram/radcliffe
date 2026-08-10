<?php
/**
 * Page not found.
 *
 * @package Redcliffe_Advisory
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>

<section class="section">
	<div class="container">
		<div class="prose">
			<h1 class="display"><?php esc_html_e( 'That page has moved on', 'redcliffe-advisory' ); ?></h1>
			<p><?php esc_html_e( 'The page you were looking for is not here. The rooms of Redcliffe Advisory are all reachable from the homepage.', 'redcliffe-advisory' ); ?></p>
			<p>
				<a class="btn" href="<?php echo esc_url( home_url( '/' ) ); ?>">
					<span><?php esc_html_e( 'Return to Redcliffe Advisory', 'redcliffe-advisory' ); ?></span>
					<span class="arr">&rarr;</span>
				</a>
			</p>
		</div>
	</div>
</section>

<?php
get_footer();
