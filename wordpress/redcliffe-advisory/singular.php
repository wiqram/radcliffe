<?php
/**
 * A single article: the fallback for any single piece of content that is not
 * one of the nine designed pages — in practice, a WordPress Post added under
 * Posts > Add New (see the Articles page and inc/articles.php).
 *
 * @package Redcliffe_Advisory
 */

defined( 'ABSPATH' ) || exit;
get_header();
?>

<?php while ( have_posts() ) : ?>
	<?php the_post(); ?>
	<section class="page-hero">
		<div class="container">
			<div class="crumb">
				<a href="<?php echo esc_url( home_url( '/' ) ); ?>">Home</a><span class="sep">·</span>
				<a href="<?php echo esc_url( rad_url( 'articles' ) ); ?>">Articles</a><span class="sep">·</span>
				<span><?php echo esc_html( rad_article_kicker() ); ?></span>
			</div>
			<h1><?php the_title(); ?></h1>
			<div class="byline"><?php echo esc_html( rad_article_byline() ); ?></div>
		</div>
	</section>

	<section class="section">
		<div class="container">
			<?php if ( has_post_thumbnail() ) : ?>
				<div class="article-photo"><?php the_post_thumbnail( 'large' ); ?></div>
			<?php endif; ?>

			<article <?php post_class( 'prose' ); ?>>
				<div class="prose-aside"><?php echo esc_html( rad_article_kicker() ); ?></div>
				<div class="prose-body rad-blocks entry-content">
					<?php the_content(); ?>
				</div>
			</article>

			<p style="margin-top: 56px;">
				<a class="btn-link" href="<?php echo esc_url( rad_url( 'articles' ) ); ?>"><span>Back to Articles</span><span class="arr">→</span></a>
			</p>
		</div>
	</section>
<?php endwhile; ?>

<?php get_footer(); ?>
