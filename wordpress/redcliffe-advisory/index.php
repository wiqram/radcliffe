<?php
/**
 * Fallback template.
 *
 * The nine designed pages each have their own template. This covers anything
 * else that gets added later — a blog roll, an archive, search results — in the
 * site's own typography.
 *
 * @package Redcliffe_Advisory
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>

<section class="section">
	<div class="container">
		<?php if ( have_posts() ) : ?>
			<?php while ( have_posts() ) : ?>
				<?php the_post(); ?>
				<article <?php post_class( 'prose' ); ?>>
					<h1 class="display"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h1>
					<?php the_excerpt(); ?>
				</article>
			<?php endwhile; ?>

			<?php the_posts_pagination(); ?>
		<?php else : ?>
			<div class="prose">
				<h1 class="display"><?php esc_html_e( 'Nothing here yet', 'redcliffe-advisory' ); ?></h1>
			</div>
		<?php endif; ?>
	</div>
</section>

<?php
get_footer();
