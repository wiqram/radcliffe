<?php
/**
 * The "Recent writing" part of the Articles page: real WordPress Posts,
 * styled to match the designed journal section it stands in for. Only
 * called when rad_has_articles() is true (see inc/articles.php).
 *
 * The newest article is featured on the first page; the rest follow as a
 * list, ten to a page, with links to the older ones underneath and to each
 * publication or topic the articles are filed under.
 *
 * @package Redcliffe_Advisory
 */

defined( 'ABSPATH' ) || exit;

$rad_paged = max( 1, (int) get_query_var( 'page' ), (int) get_query_var( 'paged' ) );

$rad_articles = new WP_Query(
	array(
		'post_type'           => 'post',
		'posts_per_page'      => 10,
		'paged'               => $rad_paged,
		'ignore_sticky_posts' => true,
	)
);

if ( ! $rad_articles->have_posts() ) {
	return;
}

// The newest article gets the large card, on the first page only.
if ( 1 === $rad_paged ) {
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
		<div class="kicker"><?php echo esc_html( rad_article_kicker() . ' · ' . rad_article_month() ); ?></div>
		<h3><a href="<?php echo esc_url( $rad_feature_url ); ?>"<?php echo $rad_feature_target; // phpcs:ignore WordPress.Security.EscapeOutput -- built above from a fixed string. ?>><?php the_title(); ?></a></h3>
		<p><?php echo esc_html( wp_trim_words( get_the_excerpt(), 32 ) ); ?></p>
		<div class="byline"><?php echo esc_html( rad_article_byline() ); ?></div>
	</div>
</article>
	<?php
}

$rad_categories = rad_article_categories();
?>
<?php if ( $rad_articles->have_posts() ) : ?>
	<div class="section-head reveal" style="margin-bottom:40px;">
		<div class="label"><?php esc_html_e( 'The index', 'redcliffe-advisory' ); ?></div>
		<h2><?php echo $rad_paged > 1 ? wp_kses_post( __( 'Earlier <em>writing</em>', 'redcliffe-advisory' ) ) : wp_kses_post( __( 'Recent <em>writing</em>', 'redcliffe-advisory' ) ); ?></h2>
	</div>

	<?php if ( count( $rad_categories ) > 1 ) : ?>
		<nav class="filter-chips reveal" aria-label="<?php esc_attr_e( 'Browse articles', 'redcliffe-advisory' ); ?>">
			<a class="is-current" href="<?php echo esc_url( rad_url( 'articles' ) ); ?>"><?php esc_html_e( 'All', 'redcliffe-advisory' ); ?></a>
			<?php foreach ( $rad_categories as $rad_category ) : ?>
				<a href="<?php echo esc_url( get_category_link( $rad_category ) ); ?>"><?php echo esc_html( $rad_category->name ); ?></a>
			<?php endforeach; ?>
		</nav>
	<?php endif; ?>

	<div class="journal-list reveal">
		<?php
		while ( $rad_articles->have_posts() ) :
			$rad_articles->the_post();
			get_template_part( 'template-parts/article-card' );
		endwhile;
		?>
	</div>
<?php endif; ?>

<?php
$rad_pagination = paginate_links(
	array(
		'base'      => trailingslashit( get_permalink( get_queried_object_id() ) ) . '%_%',
		'format'    => 'page/%#%/',
		'current'   => $rad_paged,
		'total'     => $rad_articles->max_num_pages,
		'prev_text' => __( '← Newer', 'redcliffe-advisory' ),
		'next_text' => __( 'Older →', 'redcliffe-advisory' ),
	)
);

if ( $rad_pagination ) {
	echo '<nav class="navigation pagination" aria-label="' . esc_attr__( 'More articles', 'redcliffe-advisory' ) . '"><div class="nav-links">' . $rad_pagination . '</div></nav>'; // phpcs:ignore WordPress.Security.EscapeOutput -- built by WordPress.
}

wp_reset_postdata();
