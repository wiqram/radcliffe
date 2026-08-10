<?php
/**
 * Single posts and any page that is not one of the nine designed pages.
 *
 * @package Redcliffe_Advisory
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>

<section class="section">
	<div class="container">
		<?php while ( have_posts() ) : ?>
			<?php the_post(); ?>
			<article <?php post_class( 'prose' ); ?>>
				<h1 class="display"><?php the_title(); ?></h1>
				<?php the_content(); ?>
			</article>
		<?php endwhile; ?>
	</div>
</section>

<?php
get_footer();
