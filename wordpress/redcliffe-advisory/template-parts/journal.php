<?php
/**
 * The "Recent writing" part of the Articles page: real WordPress Posts,
 * styled to match the designed journal section it stands in for. Only
 * called when rad_has_articles() is true (see inc/articles.php).
 *
 * @package Redcliffe_Advisory
 */

defined( 'ABSPATH' ) || exit;

$rad_articles = new WP_Query(
	array(
		'post_type'           => 'post',
		'posts_per_page'      => 7,
		'ignore_sticky_posts' => true,
	)
);

if ( ! $rad_articles->have_posts() ) {
	return;
}

$rad_articles->the_post();
$rad_feature_url    = rad_article_url();
$rad_feature_target = rad_article_external_url() ? ' target="_blank" rel="noopener"' : '';
?>
<article class="journal-feature reveal">
	<div class="photo">
		<a href="<?php echo esc_url( $rad_feature_url ); ?>"<?php echo $rad_feature_target; // phpcs:ignore WordPress.Security.EscapeOutput -- built above from a fixed string. ?>>
			<?php if ( has_post_thumbnail() ) : ?>
				<?php the_post_thumbnail( 'large' ); ?>
			<?php else : ?>
				<img src="<?php echo esc_url( rad_image_url( 'articles.hero.photo' ) ); ?>" alt="<?php echo esc_attr( rad_image_alt( 'articles.hero.photo' ) ); ?>" />
			<?php endif; ?>
		</a>
	</div>
	<div class="meta">
		<div class="kicker"><?php echo esc_html( rad_article_kicker() . ' · ' . get_the_date( 'Y' ) ); ?></div>
		<h3><a href="<?php echo esc_url( $rad_feature_url ); ?>"<?php echo $rad_feature_target; // phpcs:ignore WordPress.Security.EscapeOutput -- built above from a fixed string. ?>><?php the_title(); ?></a></h3>
		<p><?php echo esc_html( wp_trim_words( get_the_excerpt(), 32 ) ); ?></p>
		<div class="byline"><?php echo esc_html( rad_article_byline() ); ?></div>
	</div>
</article>

<?php if ( $rad_articles->post_count > 1 ) : ?>
	<div class="section-head reveal" style="margin-bottom:40px;">
		<div class="label">The index</div>
		<h2>Recent <em>writing</em></h2>
	</div>
	<div class="journal-list reveal">
		<?php
		while ( $rad_articles->have_posts() ) :
			$rad_articles->the_post();
			$rad_target = rad_article_external_url() ? ' target="_blank" rel="noopener"' : '';
			?>
			<a class="journal-item" href="<?php echo esc_url( rad_article_url() ); ?>"<?php echo $rad_target; // phpcs:ignore WordPress.Security.EscapeOutput -- built above from a fixed string. ?>>
				<div class="kicker"><?php echo esc_html( rad_article_kicker() ); ?></div>
				<h4><?php the_title(); ?></h4>
				<p><?php echo esc_html( wp_trim_words( get_the_excerpt(), 18 ) ); ?></p>
				<div class="byline"><?php echo esc_html( rad_article_byline() ); ?></div>
			</a>
		<?php endwhile; ?>
	</div>
<?php endif; ?>
<?php wp_reset_postdata(); ?>
