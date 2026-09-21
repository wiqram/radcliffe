<?php
/**
 * A single article, or any other single page the theme has no template for.
 *
 * Articles are WordPress Posts (Posts > Add New); they appear on the Articles
 * page and carry the author, the month and year, and their tags. See
 * inc/articles.php and assets/css/article.css.
 *
 * @package Redcliffe_Advisory
 */

defined( 'ABSPATH' ) || exit;
get_header();
?>

<?php while ( have_posts() ) : ?>
	<?php
	the_post();
	$rad_is_article = 'post' === get_post_type();
	?>
	<section class="page-hero page-hero--article">
		<div class="container">
			<div class="crumb">
				<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Home', 'redcliffe-advisory' ); ?></a><span class="sep">·</span>
				<?php if ( $rad_is_article ) : ?>
					<a href="<?php echo esc_url( rad_url( 'articles' ) ); ?>"><?php esc_html_e( 'Articles', 'redcliffe-advisory' ); ?></a><span class="sep">·</span>
					<span><?php echo esc_html( rad_article_kicker() ); ?></span>
				<?php else : ?>
					<span><?php the_title(); ?></span>
				<?php endif; ?>
			</div>
			<h1><?php the_title(); ?></h1>
			<?php if ( $rad_is_article ) : ?>
				<div class="byline"><?php echo esc_html( rad_article_byline() ); ?></div>
				<?php rad_article_tags(); ?>
			<?php endif; ?>
		</div>
	</section>

	<section class="section">
		<div class="container">
			<?php if ( has_post_thumbnail() ) : ?>
				<div class="article-photo"><?php the_post_thumbnail( 'large' ); ?></div>
			<?php endif; ?>

			<article <?php post_class( 'prose' ); ?>>
				<div class="prose-aside"><?php echo esc_html( $rad_is_article ? rad_article_kicker() : get_the_title() ); ?></div>
				<div class="prose-body rad-blocks entry-content<?php echo $rad_is_article ? ' rad-article' : ''; ?>">
					<?php the_content(); ?>
				</div>
			</article>

			<?php if ( $rad_is_article ) : ?>
				<p style="margin-top: 56px;">
					<a class="btn-link" href="<?php echo esc_url( rad_url( 'articles' ) ); ?>"><span><?php esc_html_e( 'Back to Articles', 'redcliffe-advisory' ); ?></span><span class="arr">→</span></a>
				</p>
			<?php endif; ?>
		</div>
	</section>
<?php endwhile; ?>

<?php get_footer(); ?>
