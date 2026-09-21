<?php
/**
 * One article in a list: category, title, a short summary and the byline.
 * Used inside a WordPress loop by the Articles page and the archives.
 *
 * @package Redcliffe_Advisory
 */

defined( 'ABSPATH' ) || exit;

$rad_external = rad_article_external_url();
?>
<a class="journal-item<?php echo $rad_external ? ' is-external' : ''; ?>" href="<?php echo esc_url( rad_article_url() ); ?>"<?php echo $rad_external ? ' target="_blank" rel="noopener"' : ''; // phpcs:ignore WordPress.Security.EscapeOutput -- fixed string. ?>>
	<div class="kicker"><?php echo esc_html( rad_article_kicker() ); ?></div>
	<h4><?php the_title(); ?></h4>
	<p><?php echo esc_html( wp_trim_words( get_the_excerpt(), 24 ) ); ?></p>
	<div class="byline"><?php echo esc_html( rad_article_byline() ); ?></div>
</a>
