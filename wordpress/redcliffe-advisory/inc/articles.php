<?php
/**
 * Real WordPress Posts on the Articles page.
 *
 * The Articles page shows ordinary Posts once there are any (see
 * template-parts/journal.php), so the owner's "Add Post" workflow is what
 * drives the page. A post can carry an external web address, which sends its
 * card — and the post itself — straight out to that address instead of to a
 * page on this site, for pieces written for someone else's publication.
 *
 * @package Redcliffe_Advisory
 */

defined( 'ABSPATH' ) || exit;

/**
 * Whether there is at least one published post to show on the Articles page.
 *
 * @return bool
 */
function rad_has_articles() {
	static $has = null;

	if ( null === $has ) {
		$query = new WP_Query(
			array(
				'post_type'      => 'post',
				'posts_per_page' => 1,
				'fields'         => 'ids',
				'no_found_rows'  => true,
			)
		);
		$has = $query->have_posts();
	}

	return $has;
}

/**
 * The external web address saved on a post, if any.
 *
 * @param int|WP_Post|null $post Post ID or object; defaults to the current post.
 * @return string
 */
function rad_article_external_url( $post = null ) {
	$post = get_post( $post );

	return $post ? trim( (string) get_post_meta( $post->ID, '_rad_external_url', true ) ) : '';
}

/**
 * The address a card for this post should link to: its external address when
 * set, otherwise its own permalink.
 *
 * @param int|WP_Post|null $post Post ID or object; defaults to the current post.
 * @return string
 */
function rad_article_url( $post = null ) {
	$post     = get_post( $post );
	$external = rad_article_external_url( $post );

	return $external ? $external : get_permalink( $post );
}

/**
 * A short "kicker" label for a post: its first real category, or "Article".
 *
 * @param int|WP_Post|null $post Post ID or object; defaults to the current post.
 * @return string
 */
function rad_article_kicker( $post = null ) {
	$post = get_post( $post );

	if ( ! $post ) {
		return __( 'Article', 'redcliffe-advisory' );
	}

	foreach ( get_the_category( $post->ID ) as $category ) {
		if ( 'uncategorized' !== $category->slug ) {
			return $category->name;
		}
	}

	return __( 'Article', 'redcliffe-advisory' );
}

/**
 * "Author name · year" for a post, or a byline typed in by hand.
 *
 * @param int|WP_Post|null $post Post ID or object; defaults to the current post.
 * @return string
 */
function rad_article_byline( $post = null ) {
	$post = get_post( $post );

	if ( ! $post ) {
		return '';
	}

	$custom = trim( (string) get_post_meta( $post->ID, '_rad_byline', true ) );

	if ( $custom ) {
		return $custom;
	}

	return get_the_author_meta( 'display_name', $post->post_author ) . ' · ' . get_the_date( 'Y', $post );
}

/**
 * The meta box where the owner marks a post as linking out, or types a byline.
 */
function rad_register_article_meta_box() {
	add_meta_box(
		'rad_article_meta',
		__( 'Where this article appears', 'redcliffe-advisory' ),
		'rad_render_article_meta_box',
		'post',
		'side',
		'default'
	);
}
add_action( 'add_meta_boxes', 'rad_register_article_meta_box' );

/**
 * Render the meta box.
 *
 * @param WP_Post $post The post being edited.
 */
function rad_render_article_meta_box( $post ) {
	wp_nonce_field( 'rad_save_article_meta', 'rad_article_meta_nonce' );
	$external = rad_article_external_url( $post );
	$byline   = get_post_meta( $post->ID, '_rad_byline', true );
	$default_byline = get_the_author_meta( 'display_name', $post->post_author ) . ' · ' . get_the_date( 'Y', $post );
	?>
	<p>
		<label for="rad_external_url"><strong><?php esc_html_e( 'External web address', 'redcliffe-advisory' ); ?></strong></label><br />
		<input type="url" id="rad_external_url" name="rad_external_url" class="widefat" placeholder="https://…" value="<?php echo esc_attr( $external ); ?>" />
	</p>
	<p class="description">
		<?php esc_html_e( 'On the Articles page, this card links straight here instead of to the article on this site — for a piece written for someone else’s publication. Leave empty for an ordinary article people read here.', 'redcliffe-advisory' ); ?>
	</p>
	<p>
		<label for="rad_byline"><strong><?php esc_html_e( 'Byline (optional)', 'redcliffe-advisory' ); ?></strong></label><br />
		<input type="text" id="rad_byline" name="rad_byline" class="widefat" placeholder="<?php echo esc_attr( $default_byline ); ?>" value="<?php echo esc_attr( $byline ); ?>" />
	</p>
	<p class="description">
		<?php esc_html_e( 'Leave empty to show the author’s name and the year.', 'redcliffe-advisory' ); ?>
	</p>
	<?php
}

/**
 * Save the meta box.
 *
 * @param int $post_id Post ID.
 */
function rad_save_article_meta( $post_id ) {
	if ( ! isset( $_POST['rad_article_meta_nonce'] ) || ! wp_verify_nonce( wp_unslash( $_POST['rad_article_meta_nonce'] ), 'rad_save_article_meta' ) ) {
		return;
	}

	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}

	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	if ( isset( $_POST['rad_external_url'] ) ) {
		update_post_meta( $post_id, '_rad_external_url', esc_url_raw( wp_unslash( $_POST['rad_external_url'] ) ) );
	}

	if ( isset( $_POST['rad_byline'] ) ) {
		update_post_meta( $post_id, '_rad_byline', sanitize_text_field( wp_unslash( $_POST['rad_byline'] ) ) );
	}
}
add_action( 'save_post_post', 'rad_save_article_meta' );

/**
 * Send visitors straight to the external address for a post that has one, so
 * the WordPress copy is never seen as a separate destination.
 *
 * The address comes only from post meta set by someone who can edit posts
 * (via the meta box above), never from the request, so this is not an open
 * redirect.
 */
function rad_redirect_external_article() {
	if ( ! is_singular( 'post' ) ) {
		return;
	}

	$external = rad_article_external_url( get_queried_object() );

	if ( $external ) {
		wp_redirect( $external, 302 ); // phpcs:ignore WordPress.Security.SafeRedirect -- deliberate cross-domain redirect, address set by a trusted editor, not the request.
		exit;
	}
}
add_action( 'template_redirect', 'rad_redirect_external_article' );
