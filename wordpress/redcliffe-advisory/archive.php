<?php
/**
 * Lists of articles: everything in one category, with one tag, by one author
 * or from one month. Shown in the site's own style, like the Articles page.
 *
 * @package Redcliffe_Advisory
 */

defined( 'ABSPATH' ) || exit;

get_header();

$rad_kind = is_tag() ? __( 'Tagged', 'redcliffe-advisory' ) : ( is_category() ? __( 'Filed under', 'redcliffe-advisory' ) : __( 'Archive', 'redcliffe-advisory' ) );
?>

<section class="page-hero page-hero--article">
	<div class="container">
		<div class="crumb">
			<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Home', 'redcliffe-advisory' ); ?></a><span class="sep">·</span>
			<a href="<?php echo esc_url( rad_url( 'articles' ) ); ?>"><?php esc_html_e( 'Articles', 'redcliffe-advisory' ); ?></a><span class="sep">·</span>
			<span><?php echo esc_html( $rad_kind ); ?></span>
		</div>
		<h1><?php echo esc_html( wp_strip_all_tags( get_the_archive_title() ) ); ?></h1>
		<?php if ( get_the_archive_description() ) : ?>
			<div class="page-lede"><?php echo wp_kses_post( get_the_archive_description() ); ?></div>
		<?php endif; ?>
	</div>
</section>

<section class="section">
	<div class="container">
		<?php if ( have_posts() ) : ?>
			<div class="journal-list">
				<?php
				while ( have_posts() ) :
					the_post();
					get_template_part( 'template-parts/article-card' );
				endwhile;
				?>
			</div>
			<?php
			the_posts_pagination(
				array(
					'prev_text' => __( '← Newer', 'redcliffe-advisory' ),
					'next_text' => __( 'Older →', 'redcliffe-advisory' ),
				)
			);
			?>
		<?php else : ?>
			<p class="page-lede"><?php esc_html_e( 'Nothing has been filed here yet.', 'redcliffe-advisory' ); ?></p>
		<?php endif; ?>

		<p style="margin-top: 56px;">
			<a class="btn-link" href="<?php echo esc_url( rad_url( 'articles' ) ); ?>"><span><?php esc_html_e( 'All articles', 'redcliffe-advisory' ); ?></span><span class="arr">→</span></a>
		</p>
	</div>
</section>

<?php
get_footer();
