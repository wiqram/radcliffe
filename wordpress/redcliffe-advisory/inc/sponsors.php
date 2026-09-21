<?php
/**
 * Sponsors and collaborators of the Summit.
 *
 * Under "Sponsors" in the admin menu the owner adds one entry per organisation:
 * a name, a logo and a tier (Gold Sponsor, Dinner Sponsor, Silver Sponsor,
 * Bronze Sponsors, Collaborators, Partners — the tiers can be renamed,
 * re-ordered and added to). The Summit page lays them out by itself, so the
 * owner never has to think about sizes or alignment:
 *
 *  - each logo is trimmed of empty margins and fitted, never cropped or
 *    stretched, into a tile of fixed height, so a tall crest and a wide
 *    wordmark sit comfortably side by side;
 *  - white-on-transparent logos are put on a dark tile and everything else on
 *    a white one, detected from the picture itself (and overridable);
 *  - tiles fill the width of the page, in as few even rows as possible, and
 *    stack one above another on a phone.
 *
 * @package Redcliffe_Advisory
 */

defined( 'ABSPATH' ) || exit;

/** Bump to have every stored logo rebuilt the next time it is shown. */
define( 'RAD_LOGO_VERSION', 1 );

/* -------------------------------------------------------------------------
 * Registration
 * ---------------------------------------------------------------------- */

/**
 * The tiers created on first use. After that they belong to the owner, who can
 * rename, re-order, delete or add to them.
 *
 * @return array[]
 */
function rad_sponsor_tier_defaults() {
	return array(
		array(
			'name'  => 'Gold Sponsor',
			'slug'  => 'gold-sponsor',
			'order' => 10,
			'size'  => 'feature',
			'names' => 0,
		),
		array(
			'name'  => 'Dinner Sponsor',
			'slug'  => 'dinner-sponsor',
			'order' => 20,
			'size'  => 'feature',
			'names' => 0,
		),
		array(
			'name'  => 'Silver Sponsor',
			'slug'  => 'silver-sponsor',
			'order' => 30,
			'size'  => 'large',
			'names' => 0,
		),
		array(
			'name'  => 'Bronze Sponsors',
			'slug'  => 'bronze-sponsors',
			'order' => 40,
			'size'  => 'medium',
			'names' => 0,
		),
		array(
			'name'  => 'Collaborators',
			'slug'  => 'collaborators',
			'order' => 50,
			'size'  => 'small',
			'names' => 0,
		),
		array(
			'name'  => 'Partners',
			'slug'  => 'partners',
			'order' => 60,
			'size'  => 'small',
			'names' => 1,
		),
	);
}

/**
 * The tile sizes a tier can use. `columns` is the most tiles across on a wide
 * screen and on a tablet; on a phone there is always one.
 *
 * @return array[]
 */
function rad_sponsor_sizes() {
	return array(
		'feature' => array(
			'label'   => __( 'Largest — for one or two headline sponsors', 'redcliffe-advisory' ),
			'short'   => __( 'Largest', 'redcliffe-advisory' ),
			'columns' => array( 2, 2 ),
		),
		'large'   => array(
			'label'   => __( 'Large', 'redcliffe-advisory' ),
			'short'   => __( 'Large', 'redcliffe-advisory' ),
			'columns' => array( 4, 2 ),
		),
		'medium'  => array(
			'label'   => __( 'Medium', 'redcliffe-advisory' ),
			'short'   => __( 'Medium', 'redcliffe-advisory' ),
			'columns' => array( 5, 3 ),
		),
		'small'   => array(
			'label'   => __( 'Small — fits the most across the page', 'redcliffe-advisory' ),
			'short'   => __( 'Small', 'redcliffe-advisory' ),
			'columns' => array( 6, 3 ),
		),
	);
}

/**
 * The Sponsors menu and its tiers.
 */
function rad_register_sponsors() {
	register_post_type(
		'rad_sponsor',
		array(
			'labels'              => array(
				'name'                  => __( 'Sponsors', 'redcliffe-advisory' ),
				'singular_name'         => __( 'Sponsor', 'redcliffe-advisory' ),
				'menu_name'             => __( 'Sponsors', 'redcliffe-advisory' ),
				'all_items'             => __( 'All sponsors', 'redcliffe-advisory' ),
				'add_new'               => __( 'Add sponsor', 'redcliffe-advisory' ),
				'add_new_item'          => __( 'Add a sponsor or collaborator', 'redcliffe-advisory' ),
				'edit_item'             => __( 'Edit sponsor', 'redcliffe-advisory' ),
				'new_item'              => __( 'New sponsor', 'redcliffe-advisory' ),
				'search_items'          => __( 'Search sponsors', 'redcliffe-advisory' ),
				'not_found'             => __( 'No sponsors yet.', 'redcliffe-advisory' ),
				'not_found_in_trash'    => __( 'Nothing in the bin.', 'redcliffe-advisory' ),
				'featured_image'        => __( 'Logo', 'redcliffe-advisory' ),
				'set_featured_image'    => __( 'Choose the logo', 'redcliffe-advisory' ),
				'remove_featured_image' => __( 'Remove the logo', 'redcliffe-advisory' ),
				'use_featured_image'    => __( 'Use as the logo', 'redcliffe-advisory' ),
				'attributes'            => __( 'Order in its tier', 'redcliffe-advisory' ),
			),
			'public'              => false,
			'publicly_queryable'  => false,
			'exclude_from_search' => true,
			'show_ui'             => true,
			'show_in_menu'        => true,
			'show_in_rest'        => false, // The plain editor: a name, a logo, a few boxes.
			'show_in_nav_menus'   => false,
			'menu_position'       => 21,
			'menu_icon'           => 'dashicons-awards',
			'supports'            => array( 'title', 'thumbnail', 'page-attributes' ),
			'has_archive'         => false,
			'rewrite'             => false,
			'query_var'           => false,
		)
	);

	register_taxonomy(
		'rad_tier',
		'rad_sponsor',
		array(
			'labels'             => array(
				'name'          => __( 'Sponsor tiers', 'redcliffe-advisory' ),
				'singular_name' => __( 'Tier', 'redcliffe-advisory' ),
				'menu_name'     => __( 'Tiers', 'redcliffe-advisory' ),
				'all_items'     => __( 'All tiers', 'redcliffe-advisory' ),
				'add_new_item'  => __( 'Add a tier', 'redcliffe-advisory' ),
				'edit_item'     => __( 'Edit tier', 'redcliffe-advisory' ),
				'update_item'   => __( 'Update tier', 'redcliffe-advisory' ),
				'search_items'  => __( 'Search tiers', 'redcliffe-advisory' ),
				'not_found'     => __( 'No tiers yet.', 'redcliffe-advisory' ),
			),
			'description'        => __( 'The groups sponsors are shown in on the Summit page, such as Gold Sponsor or Collaborators.', 'redcliffe-advisory' ),
			'public'             => false,
			'publicly_queryable' => false,
			'show_ui'            => true,
			'show_in_menu'       => true,
			'show_in_rest'       => false,
			'show_in_nav_menus'  => false,
			'show_admin_column'  => true,
			'show_in_quick_edit' => false, // Tiers are chosen in the Sponsor details box, from a list.
			'show_tagcloud'      => false,
			'hierarchical'       => false,
			'meta_box_cb'        => false, // The Sponsor details box has its own, simpler picker.
			'rewrite'            => false,
			'query_var'          => false,
		)
	);

	register_term_meta(
		'rad_tier',
		'rad_tier_order',
		array(
			'type'              => 'integer',
			'single'            => true,
			'default'           => 100,
			'sanitize_callback' => 'absint',
		)
	);
	register_term_meta(
		'rad_tier',
		'rad_tier_size',
		array(
			'type'              => 'string',
			'single'            => true,
			'default'           => 'small',
			'sanitize_callback' => 'rad_sanitize_tier_size',
		)
	);
	register_term_meta(
		'rad_tier',
		'rad_tier_names',
		array(
			'type'              => 'integer',
			'single'            => true,
			'default'           => 0,
			'sanitize_callback' => 'absint',
		)
	);
}
add_action( 'init', 'rad_register_sponsors' );

/**
 * A tile size the theme knows, or the smallest.
 *
 * @param string $size Submitted size.
 * @return string
 */
function rad_sanitize_tier_size( $size ) {
	return isset( rad_sponsor_sizes()[ $size ] ) ? $size : 'small';
}

/**
 * Create the six tiers the first time the theme runs. Never again: a tier the
 * owner deletes or renames must stay that way.
 */
function rad_seed_sponsor_tiers() {
	if ( get_option( 'rad_sponsor_tiers_seeded' ) || ! taxonomy_exists( 'rad_tier' ) ) {
		return;
	}

	foreach ( rad_sponsor_tier_defaults() as $tier ) {
		if ( term_exists( $tier['slug'], 'rad_tier' ) ) {
			continue;
		}

		$created = wp_insert_term( $tier['name'], 'rad_tier', array( 'slug' => $tier['slug'] ) );

		if ( is_wp_error( $created ) ) {
			continue;
		}

		update_term_meta( $created['term_id'], 'rad_tier_order', $tier['order'] );
		update_term_meta( $created['term_id'], 'rad_tier_size', $tier['size'] );
		update_term_meta( $created['term_id'], 'rad_tier_names', $tier['names'] );
	}

	update_option( 'rad_sponsor_tiers_seeded', RAD_VERSION, false );
}
add_action( 'init', 'rad_seed_sponsor_tiers', 20 );

/* -------------------------------------------------------------------------
 * Tiers: order, size, names — the extra boxes on the tier screens
 * ---------------------------------------------------------------------- */

/**
 * The three tier settings, as form controls.
 *
 * @param int $order Current order.
 * @param string $size Current size.
 * @param int $names Whether names are shown.
 * @param bool $table True for the edit screen's table rows, false for the add form.
 */
function rad_render_tier_fields( $order, $size, $names, $table ) {
	wp_nonce_field( 'rad_save_tier', 'rad_tier_nonce' );

	$rows = array(
		array(
			'label' => __( 'Position on the page', 'redcliffe-advisory' ),
			'field' => sprintf( '<input type="number" name="rad_tier_order" value="%d" min="0" step="10" class="small-text" />', (int) $order ),
			'help'  => __( 'A number. Tiers with smaller numbers come first (Gold is 10, Dinner 20, and so on). Leave gaps, such as 10, 20, 30, so a new tier can be slipped in between.', 'redcliffe-advisory' ),
		),
		array(
			'label' => __( 'Logo size', 'redcliffe-advisory' ),
			'field' => rad_tier_size_select( $size ),
			'help'  => __( 'How big each logo tile is. The site fits every logo inside its tile without stretching or cutting it, whatever shape the logo is.', 'redcliffe-advisory' ),
		),
		array(
			'label' => __( 'Show names', 'redcliffe-advisory' ),
			'field' => sprintf( '<label><input type="checkbox" name="rad_tier_names" value="1" %s /> %s</label>', checked( (int) $names, 1, false ), esc_html__( 'Write each organisation’s name under its logo', 'redcliffe-advisory' ) ),
			'help'  => __( 'Useful for partners, where the name matters as much as the logo.', 'redcliffe-advisory' ),
		),
	);

	foreach ( $rows as $row ) {
		if ( $table ) {
			printf(
				'<tr class="form-field"><th scope="row">%1$s</th><td>%2$s<p class="description">%3$s</p></td></tr>',
				esc_html( $row['label'] ),
				$row['field'], // phpcs:ignore WordPress.Security.EscapeOutput -- built above from escaped parts.
				esc_html( $row['help'] )
			);
		} else {
			printf(
				'<div class="form-field"><label>%1$s</label>%2$s<p>%3$s</p></div>',
				esc_html( $row['label'] ),
				$row['field'], // phpcs:ignore WordPress.Security.EscapeOutput -- built above from escaped parts.
				esc_html( $row['help'] )
			);
		}
	}
}

/**
 * The size <select>.
 *
 * @param string $current Selected size.
 * @return string
 */
function rad_tier_size_select( $current ) {
	$html = '<select name="rad_tier_size">';
	foreach ( rad_sponsor_sizes() as $key => $size ) {
		$html .= sprintf( '<option value="%s"%s>%s</option>', esc_attr( $key ), selected( $current, $key, false ), esc_html( $size['label'] ) );
	}
	return $html . '</select>';
}

/**
 * Settings on the "Add a tier" form.
 */
function rad_tier_add_fields() {
	rad_render_tier_fields( 100, 'small', 0, false );
}
add_action( 'rad_tier_add_form_fields', 'rad_tier_add_fields' );

/**
 * Settings on a tier's edit screen.
 *
 * @param WP_Term $term The tier.
 */
function rad_tier_edit_fields( $term ) {
	rad_render_tier_fields(
		(int) get_term_meta( $term->term_id, 'rad_tier_order', true ),
		(string) get_term_meta( $term->term_id, 'rad_tier_size', true ),
		(int) get_term_meta( $term->term_id, 'rad_tier_names', true ),
		true
	);
}
add_action( 'rad_tier_edit_form_fields', 'rad_tier_edit_fields' );

/**
 * Save the tier settings.
 *
 * @param int $term_id Tier ID.
 */
function rad_save_tier_fields( $term_id ) {
	if ( ! isset( $_POST['rad_tier_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['rad_tier_nonce'] ) ), 'rad_save_tier' ) ) {
		return;
	}

	if ( ! current_user_can( 'edit_term', $term_id ) ) {
		return;
	}

	$order = isset( $_POST['rad_tier_order'] ) ? absint( wp_unslash( $_POST['rad_tier_order'] ) ) : 100;
	$size  = isset( $_POST['rad_tier_size'] ) ? rad_sanitize_tier_size( sanitize_key( wp_unslash( $_POST['rad_tier_size'] ) ) ) : 'small';

	update_term_meta( $term_id, 'rad_tier_order', $order );
	update_term_meta( $term_id, 'rad_tier_size', $size );
	update_term_meta( $term_id, 'rad_tier_names', empty( $_POST['rad_tier_names'] ) ? 0 : 1 );
}
add_action( 'created_rad_tier', 'rad_save_tier_fields' );
add_action( 'edited_rad_tier', 'rad_save_tier_fields' );

/**
 * Order, size and names as columns in the list of tiers.
 *
 * @param array $columns Columns.
 * @return array
 */
function rad_tier_columns( $columns ) {
	$columns['rad_order'] = __( 'Position', 'redcliffe-advisory' );
	$columns['rad_size']  = __( 'Logo size', 'redcliffe-advisory' );
	$columns['rad_names'] = __( 'Names shown', 'redcliffe-advisory' );
	unset( $columns['slug'] );
	return $columns;
}
add_filter( 'manage_edit-rad_tier_columns', 'rad_tier_columns' );

/**
 * A cell in the list of tiers.
 *
 * @param string $content Existing content.
 * @param string $column  Column key.
 * @param int    $term_id Tier ID.
 * @return string
 */
function rad_tier_column_content( $content, $column, $term_id ) {
	if ( 'rad_order' === $column ) {
		return (string) (int) get_term_meta( $term_id, 'rad_tier_order', true );
	}
	if ( 'rad_size' === $column ) {
		$sizes = rad_sponsor_sizes();
		$size  = rad_sanitize_tier_size( (string) get_term_meta( $term_id, 'rad_tier_size', true ) );
		return esc_html( $sizes[ $size ]['short'] );
	}
	if ( 'rad_names' === $column ) {
		return get_term_meta( $term_id, 'rad_tier_names', true ) ? esc_html__( 'Yes', 'redcliffe-advisory' ) : '—';
	}
	return $content;
}
add_filter( 'manage_rad_tier_custom_column', 'rad_tier_column_content', 10, 3 );

/**
 * List the tiers in the order they appear on the page, not alphabetically.
 *
 * @param array    $terms      Terms.
 * @param string[] $taxonomies Taxonomies.
 * @return array
 */
function rad_sort_tiers_in_admin( $terms, $taxonomies ) {
	if ( ! is_admin() || ! is_array( $taxonomies ) || array( 'rad_tier' ) !== array_values( $taxonomies ) ) {
		return $terms;
	}

	if ( ! is_array( $terms ) || ! $terms || ! reset( $terms ) instanceof WP_Term ) {
		return $terms; // A count, a list of IDs or names — nothing to sort.
	}

	usort( $terms, 'rad_compare_tiers' );

	return $terms;
}
add_filter( 'get_terms', 'rad_sort_tiers_in_admin', 10, 2 );

/**
 * Sort helper: tier order, then name.
 *
 * @param WP_Term $a First tier.
 * @param WP_Term $b Second tier.
 * @return int
 */
function rad_compare_tiers( $a, $b ) {
	$order_a = (int) get_term_meta( $a->term_id, 'rad_tier_order', true );
	$order_b = (int) get_term_meta( $b->term_id, 'rad_tier_order', true );

	if ( $order_a !== $order_b ) {
		return $order_a < $order_b ? -1 : 1;
	}

	return strcasecmp( $a->name, $b->name );
}

/* -------------------------------------------------------------------------
 * The sponsor screen: name, logo (the featured image), and a few details
 * ---------------------------------------------------------------------- */

/**
 * Placeholder for the title box.
 *
 * @param string  $title Placeholder.
 * @param WP_Post $post  Post.
 * @return string
 */
function rad_sponsor_title_placeholder( $title, $post ) {
	return 'rad_sponsor' === $post->post_type ? __( 'The organisation’s name', 'redcliffe-advisory' ) : $title;
}
add_filter( 'enter_title_here', 'rad_sponsor_title_placeholder', 10, 2 );

/**
 * Advice under the logo picker.
 *
 * @param string $content Box content.
 * @param int    $post_id Post ID.
 * @return string
 */
function rad_sponsor_logo_help( $content, $post_id ) {
	if ( 'rad_sponsor' !== get_post_type( $post_id ) ) {
		return $content;
	}

	return $content . '<p class="description">' . esc_html__( 'Any size will do: the website trims empty margins and fits the logo into its tile without stretching it. A PNG with a transparent background is best; a picture with a white background works too. Aim for at least 600 pixels wide.', 'redcliffe-advisory' ) . '</p>';
}
add_filter( 'admin_post_thumbnail_html', 'rad_sponsor_logo_help', 10, 2 );

/**
 * Register the details box.
 */
function rad_register_sponsor_meta_box() {
	add_meta_box(
		'rad_sponsor_details',
		__( 'About this sponsor', 'redcliffe-advisory' ),
		'rad_render_sponsor_meta_box',
		'rad_sponsor',
		'normal',
		'high'
	);
}
add_action( 'add_meta_boxes_rad_sponsor', 'rad_register_sponsor_meta_box' );

/**
 * The tier a sponsor belongs to (its ID), or the default for a new one.
 *
 * @param int $post_id Sponsor ID.
 * @return int
 */
function rad_sponsor_tier_id( $post_id ) {
	$terms = wp_get_object_terms( $post_id, 'rad_tier', array( 'fields' => 'ids' ) );

	if ( ! is_wp_error( $terms ) && $terms ) {
		return (int) $terms[0];
	}

	$default = get_term_by( 'slug', 'collaborators', 'rad_tier' );

	return $default ? (int) $default->term_id : 0;
}

/**
 * All tiers, in page order.
 *
 * @return WP_Term[]
 */
function rad_all_tiers() {
	$terms = get_terms(
		array(
			'taxonomy'   => 'rad_tier',
			'hide_empty' => false,
		)
	);

	if ( is_wp_error( $terms ) ) {
		return array();
	}

	usort( $terms, 'rad_compare_tiers' );

	return $terms;
}

/**
 * The details box.
 *
 * @param WP_Post $post The sponsor.
 */
function rad_render_sponsor_meta_box( $post ) {
	wp_nonce_field( 'rad_save_sponsor', 'rad_sponsor_nonce' );

	$tier = rad_sponsor_tier_id( $post->ID );
	$url  = (string) get_post_meta( $post->ID, '_rad_sponsor_url', true );
	$role = (string) get_post_meta( $post->ID, '_rad_sponsor_role', true );
	$card = (string) get_post_meta( $post->ID, '_rad_logo_card', true );
	$logo = $post->ID && 'auto-draft' !== $post->post_status ? rad_sponsor_logo( $post->ID ) : null;
	?>
	<table class="form-table rad-sponsor-fields" role="presentation">
		<tr>
			<th scope="row"><label for="rad_sponsor_tier"><?php esc_html_e( 'Tier', 'redcliffe-advisory' ); ?></label></th>
			<td>
				<select id="rad_sponsor_tier" name="rad_sponsor_tier">
					<?php foreach ( rad_all_tiers() as $term ) : ?>
						<option value="<?php echo esc_attr( $term->term_id ); ?>" <?php selected( $tier, $term->term_id ); ?>><?php echo esc_html( $term->name ); ?></option>
					<?php endforeach; ?>
				</select>
				<p class="description"><?php esc_html_e( 'Which group this organisation is shown in on the Summit page. To add, rename or re-order the groups, use Sponsors → Tiers.', 'redcliffe-advisory' ); ?></p>
			</td>
		</tr>
		<tr>
			<th scope="row"><label for="rad_sponsor_url"><?php esc_html_e( 'Website address', 'redcliffe-advisory' ); ?></label></th>
			<td>
				<input type="url" id="rad_sponsor_url" name="rad_sponsor_url" class="large-text" placeholder="https://…" value="<?php echo esc_attr( $url ); ?>" />
				<p class="description"><?php esc_html_e( 'Optional. When filled in, clicking the logo opens this address in a new tab.', 'redcliffe-advisory' ); ?></p>
			</td>
		</tr>
		<tr>
			<th scope="row"><label for="rad_sponsor_role"><?php esc_html_e( 'A few words about the role', 'redcliffe-advisory' ); ?></label></th>
			<td>
				<input type="text" id="rad_sponsor_role" name="rad_sponsor_role" class="large-text" placeholder="<?php esc_attr_e( 'Communications partner', 'redcliffe-advisory' ); ?>" value="<?php echo esc_attr( $role ); ?>" />
				<p class="description"><?php esc_html_e( 'Optional. Shown under the name in tiers that show names, such as Partners.', 'redcliffe-advisory' ); ?></p>
			</td>
		</tr>
		<tr>
			<th scope="row"><label for="rad_logo_card"><?php esc_html_e( 'Tile colour', 'redcliffe-advisory' ); ?></label></th>
			<td>
				<select id="rad_logo_card" name="rad_logo_card">
					<option value="" <?php selected( $card, '' ); ?>><?php esc_html_e( 'Automatic (recommended)', 'redcliffe-advisory' ); ?></option>
					<option value="light" <?php selected( $card, 'light' ); ?>><?php esc_html_e( 'White tile — for dark or coloured logos', 'redcliffe-advisory' ); ?></option>
					<option value="dark" <?php selected( $card, 'dark' ); ?>><?php esc_html_e( 'Dark blue tile — for white logos', 'redcliffe-advisory' ); ?></option>
				</select>
				<p class="description"><?php esc_html_e( 'The website looks at the logo and picks the tile that shows it best. Change this only if a logo looks wrong.', 'redcliffe-advisory' ); ?></p>
			</td>
		</tr>
	</table>

	<?php if ( $logo ) : ?>
		<div class="rad-sponsor-preview">
			<strong><?php esc_html_e( 'How the logo will look', 'redcliffe-advisory' ); ?></strong>
			<div class="rad-sponsor-preview__tile is-card-<?php echo esc_attr( $logo['card'] ); ?>"<?php echo rad_tile_style( $logo ); // phpcs:ignore WordPress.Security.EscapeOutput ?>>
				<img src="<?php echo esc_url( $logo['src'] ); ?>" alt="" />
			</div>
			<span class="description">
				<?php
				echo esc_html(
					'dark' === $logo['card']
						? __( 'On a dark blue tile, because the logo is light-coloured.', 'redcliffe-advisory' )
						: __( 'On a white tile.', 'redcliffe-advisory' )
				);
				?>
			</span>
		</div>
	<?php else : ?>
		<p class="description"><?php esc_html_e( 'Choose the logo in the “Logo” box on the right, then click Publish (or Update) to see how it will look. Without a logo, the name is shown on its own.', 'redcliffe-advisory' ); ?></p>
	<?php endif; ?>
	<?php
}

/**
 * Save the details box.
 *
 * @param int $post_id Sponsor ID.
 */
function rad_save_sponsor( $post_id ) {
	if ( ! isset( $_POST['rad_sponsor_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['rad_sponsor_nonce'] ) ), 'rad_save_sponsor' ) ) {
		return;
	}

	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}

	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	$url = isset( $_POST['rad_sponsor_url'] ) ? trim( wp_unslash( $_POST['rad_sponsor_url'] ) ) : '';
	// A bare "example.com" is what people type; make it an address.
	if ( $url && ! preg_match( '#^[a-z][a-z0-9+.-]*://#i', $url ) ) {
		$url = 'https://' . ltrim( $url, '/' );
	}
	update_post_meta( $post_id, '_rad_sponsor_url', esc_url_raw( $url ) );
	update_post_meta( $post_id, '_rad_sponsor_role', isset( $_POST['rad_sponsor_role'] ) ? sanitize_text_field( wp_unslash( $_POST['rad_sponsor_role'] ) ) : '' );

	$card = isset( $_POST['rad_logo_card'] ) ? sanitize_key( wp_unslash( $_POST['rad_logo_card'] ) ) : '';
	update_post_meta( $post_id, '_rad_logo_card', in_array( $card, array( 'light', 'dark' ), true ) ? $card : '' );

	$tier = isset( $_POST['rad_sponsor_tier'] ) ? absint( wp_unslash( $_POST['rad_sponsor_tier'] ) ) : 0;
	if ( $tier && term_exists( $tier, 'rad_tier' ) ) {
		wp_set_object_terms( $post_id, array( $tier ), 'rad_tier' );
	}
}
add_action( 'save_post_rad_sponsor', 'rad_save_sponsor' );

/**
 * A sponsor that has no tier goes into the last one, so it is never lost.
 *
 * @param int $post_id Sponsor ID.
 */
function rad_sponsor_ensure_tier( $post_id ) {
	if ( wp_is_post_revision( $post_id ) || 'rad_sponsor' !== get_post_type( $post_id ) ) {
		return;
	}

	if ( has_term( '', 'rad_tier', $post_id ) ) {
		return;
	}

	$tier = rad_sponsor_tier_id( $post_id );

	if ( $tier ) {
		wp_set_object_terms( $post_id, array( $tier ), 'rad_tier' );
	}
}
add_action( 'save_post_rad_sponsor', 'rad_sponsor_ensure_tier', 20 );

/**
 * Rebuild the tile picture as soon as a sponsor is saved, so the first visitor
 * never waits for it. Runs after the logo has been stored.
 *
 * @param int $post_id Sponsor ID.
 */
function rad_sponsor_warm_logo( $post_id ) {
	if ( wp_is_post_revision( $post_id ) || 'rad_sponsor' !== get_post_type( $post_id ) || ! has_post_thumbnail( $post_id ) ) {
		return;
	}

	rad_sponsor_logo( $post_id );
}
add_action( 'wp_after_insert_post', 'rad_sponsor_warm_logo', 20 );

/**
 * Remove the stored tile picture when a sponsor is deleted for good.
 *
 * @param int $post_id Post ID.
 */
function rad_sponsor_cleanup_logo( $post_id ) {
	if ( 'rad_sponsor' !== get_post_type( $post_id ) ) {
		return;
	}

	$dir = rad_logo_dir();

	foreach ( (array) glob( $dir['path'] . '/sponsor-' . (int) $post_id . '-*.png' ) as $file ) {
		wp_delete_file( $file );
	}
}
add_action( 'before_delete_post', 'rad_sponsor_cleanup_logo' );

/* -------------------------------------------------------------------------
 * The list of sponsors
 * ---------------------------------------------------------------------- */

/**
 * Columns: logo, name, tier, website, position.
 *
 * @param array $columns Columns.
 * @return array
 */
function rad_sponsor_columns( $columns ) {
	return array(
		'cb'                => isset( $columns['cb'] ) ? $columns['cb'] : '',
		'rad_logo'          => __( 'Logo', 'redcliffe-advisory' ),
		'title'             => __( 'Name', 'redcliffe-advisory' ),
		'taxonomy-rad_tier' => __( 'Tier', 'redcliffe-advisory' ),
		'rad_link'          => __( 'Website', 'redcliffe-advisory' ),
		'rad_order'         => __( 'Order', 'redcliffe-advisory' ),
	);
}
add_filter( 'manage_rad_sponsor_posts_columns', 'rad_sponsor_columns' );

/**
 * A cell in the list of sponsors.
 *
 * @param string $column  Column key.
 * @param int    $post_id Sponsor ID.
 */
function rad_sponsor_column_content( $column, $post_id ) {
	if ( 'rad_logo' === $column ) {
		$logo = rad_sponsor_logo( $post_id );
		if ( $logo ) {
			printf( '<span class="rad-sponsor-thumb is-card-%s"%s><img src="%s" alt="" /></span>', esc_attr( $logo['card'] ), rad_tile_style( $logo ), esc_url( $logo['src'] ) ); // phpcs:ignore WordPress.Security.EscapeOutput -- escaped in rad_tile_style().
		} else {
			echo '<span class="rad-sponsor-thumb is-empty">' . esc_html__( 'No logo', 'redcliffe-advisory' ) . '</span>';
		}
	} elseif ( 'rad_link' === $column ) {
		$url = get_post_meta( $post_id, '_rad_sponsor_url', true );
		echo $url ? '<a href="' . esc_url( $url ) . '" target="_blank" rel="noopener">' . esc_html( preg_replace( '#^https?://(www\.)?#', '', untrailingslashit( $url ) ) ) . '</a>' : '—';
	} elseif ( 'rad_order' === $column ) {
		echo (int) get_post_field( 'menu_order', $post_id );
	}
}
add_action( 'manage_rad_sponsor_posts_custom_column', 'rad_sponsor_column_content', 10, 2 );

/**
 * Let the order column be sorted.
 *
 * @param array $columns Sortable columns.
 * @return array
 */
function rad_sponsor_sortable_columns( $columns ) {
	$columns['rad_order'] = 'menu_order';
	return $columns;
}
add_filter( 'manage_edit-rad_sponsor_sortable_columns', 'rad_sponsor_sortable_columns' );

/**
 * List sponsors in the order they appear on the page — tier by tier, then by
 * the order number, then by name — unless a column has been clicked to sort.
 *
 * @param WP_Query $query The query.
 */
function rad_sponsor_admin_order( $query ) {
	if ( ! is_admin() || ! $query->is_main_query() || 'rad_sponsor' !== $query->get( 'post_type' ) ) {
		return;
	}

	if ( ! $query->get( 'orderby' ) ) {
		$query->set( 'rad_page_order', true );
	}
}
add_action( 'pre_get_posts', 'rad_sponsor_admin_order' );

/**
 * The SQL behind that ordering: each sponsor's tier position comes from the tier's settings.
 *
 * @param array    $clauses Query clauses.
 * @param WP_Query $query   The query.
 * @return array
 */
function rad_sponsor_admin_clauses( $clauses, $query ) {
	if ( ! $query->get( 'rad_page_order' ) ) {
		return $clauses;
	}

	global $wpdb;

	$clauses['join']   .= " LEFT JOIN {$wpdb->term_relationships} rad_tr ON ({$wpdb->posts}.ID = rad_tr.object_id)"
		. " LEFT JOIN {$wpdb->term_taxonomy} rad_tt ON (rad_tr.term_taxonomy_id = rad_tt.term_taxonomy_id AND rad_tt.taxonomy = 'rad_tier')"
		. " LEFT JOIN {$wpdb->termmeta} rad_tm ON (rad_tt.term_id = rad_tm.term_id AND rad_tm.meta_key = 'rad_tier_order')";
	$clauses['groupby'] = "{$wpdb->posts}.ID";
	$clauses['orderby'] = "CAST(COALESCE(rad_tm.meta_value, 100) AS UNSIGNED) ASC, {$wpdb->posts}.menu_order ASC, {$wpdb->posts}.post_title ASC";

	return $clauses;
}
add_filter( 'posts_clauses', 'rad_sponsor_admin_clauses', 10, 2 );

/**
 * Say what the Order box means.
 *
 * @param WP_Post $post The sponsor being edited.
 */
function rad_sponsor_order_help( $post ) {
	if ( 'rad_sponsor' === $post->post_type ) {
		echo '<p class="description">' . esc_html__( 'Within its tier, smaller numbers come first (10, 20, 30…). Sponsors with the same number go in alphabetical order.', 'redcliffe-advisory' ) . '</p>';
	}
}
add_action( 'page_attributes_misc_attributes', 'rad_sponsor_order_help' );

/**
 * A short welcome above the list, and a note once there are sponsors.
 */
function rad_sponsor_list_notice() {
	$screen = get_current_screen();

	if ( ! $screen || 'edit-rad_sponsor' !== $screen->id ) {
		return;
	}

	$counts = wp_count_posts( 'rad_sponsor' );
	$count  = isset( $counts->publish ) ? (int) $counts->publish : 0;
	$import = admin_url( 'edit.php?post_type=rad_sponsor&page=rad-sponsor-import' );

	echo '<div class="notice notice-info"><p>';
	if ( $count ) {
		echo wp_kses(
			sprintf(
				/* translators: 1: link to the Summit page, 2: link to the media library importer. */
				__( 'These appear on the <a href="%1$s" target="_blank" rel="noopener">Summit page</a>, grouped by tier, in the order below. Already uploaded more logos to the Media Library? <a href="%2$s">Add several at once</a>.', 'redcliffe-advisory' ),
				esc_url( rad_url( 'summit' ) . '#collaborators' ),
				esc_url( $import )
			),
			array(
				'a' => array(
					'href'   => array(),
					'target' => array(),
					'rel'    => array(),
				),
			)
		);
	} else {
		echo wp_kses(
			sprintf(
				/* translators: %s: link to the media library importer. */
				__( 'Add each sponsor, collaborator or partner here with <strong>Add sponsor</strong>: a name, a logo and a tier. The Summit page arranges them by itself. Already uploaded the logos to the Media Library? <a href="%s">Add several at once</a>. Until the first sponsor is added, the Summit page shows its original placeholder names.', 'redcliffe-advisory' ),
				esc_url( $import )
			),
			array(
				'a'      => array( 'href' => array() ),
				'strong' => array(),
			)
		);
	}
	echo '</p></div>';
}
add_action( 'admin_notices', 'rad_sponsor_list_notice' );

/**
 * The admin stylesheet, only where it is needed.
 */
function rad_sponsor_admin_assets() {
	$screen = get_current_screen();

	if ( ! $screen || 'rad_sponsor' !== $screen->post_type ) {
		return;
	}

	wp_enqueue_style( 'rad-admin', get_theme_file_uri( 'assets/css/admin.css' ), array(), RAD_VERSION );
}
add_action( 'admin_enqueue_scripts', 'rad_sponsor_admin_assets' );

/* -------------------------------------------------------------------------
 * Adding many at once: from the logos already placed on the Summit page, and
 * from the Media Library
 * ---------------------------------------------------------------------- */

/**
 * The importer's menu entry.
 */
function rad_sponsor_import_menu() {
	add_submenu_page(
		'edit.php?post_type=rad_sponsor',
		__( 'Add several logos', 'redcliffe-advisory' ),
		__( 'Add several logos', 'redcliffe-advisory' ),
		'edit_posts',
		'rad-sponsor-import',
		'rad_render_sponsor_import'
	);
}
add_action( 'admin_menu', 'rad_sponsor_import_menu' );

/**
 * A readable name from a file name: "multiverse-logo_2x.png" becomes "Multiverse".
 *
 * @param string $file Attachment title or file name.
 * @return string
 */
function rad_name_from_filename( $file ) {
	$name = preg_replace( '/\.[a-z0-9]{2,4}$/i', '', $file );
	$name = preg_replace( '/[-_.]+/', ' ', $name );
	$name = preg_replace( '/\b(logo|logos|rgb|cmyk|png|jpe?g|webp|new|final|secondary|primary|black|white|all|whiteplusred)\b/i', ' ', $name );
	$name = preg_replace( '/\b\d+(x\d+)?\b/', ' ', $name );
	$name = trim( preg_replace( '/\s+/', ' ', $name ) );

	return '' === $name ? trim( preg_replace( '/[-_.]+/', ' ', $file ) ) : ucwords( $name );
}

/**
 * A heading or label reduced to what identifies a tier: "Bronze Sponsors:" and
 * "bronze sponsor" are the same.
 *
 * @param string $text Heading text or markup.
 * @return string
 */
function rad_tier_key( $text ) {
	$text = strtolower( trim( wp_strip_all_tags( html_entity_decode( (string) $text ) ) ) );
	$text = preg_replace( '/[\s:\x{2013}\x{2014}.-]+$/u', '', $text );
	$text = preg_replace( '/\s+/', ' ', $text );

	return rtrim( $text, 's' );
}

/**
 * The tier a heading or label names, or 0.
 *
 * @param string $text Heading text or markup.
 * @return int Tier ID.
 */
function rad_tier_by_label( $text ) {
	$key = rad_tier_key( $text );

	if ( '' === $key ) {
		return 0;
	}

	foreach ( rad_all_tiers() as $term ) {
		if ( rad_tier_key( $term->name ) === $key ) {
			return (int) $term->term_id;
		}
	}

	return 0;
}

/**
 * The attachment a picture block points at.
 *
 * @param array $block Parsed core/image block.
 * @return int
 */
function rad_block_image_id( $block ) {
	if ( ! empty( $block['attrs']['id'] ) ) {
		return (int) $block['attrs']['id'];
	}

	return preg_match( '/wp-image-(\d+)/', (string) $block['innerHTML'], $m ) ? (int) $m[1] : 0;
}

/**
 * Every attachment a gallery or picture block shows.
 *
 * @param array $block Parsed block.
 * @return int[]
 */
function rad_block_image_ids( $block ) {
	if ( 'core/image' === $block['blockName'] ) {
		$id = rad_block_image_id( $block );
		return $id ? array( $id ) : array();
	}

	$ids = array();

	foreach ( (array) $block['innerBlocks'] as $inner ) {
		$ids = array_merge( $ids, rad_block_image_ids( $inner ) );
	}

	if ( ! empty( $block['attrs']['ids'] ) ) { // Galleries saved before WordPress 5.9.
		$ids = array_merge( $ids, array_map( 'intval', (array) $block['attrs']['ids'] ) );
	}

	return array_values( array_unique( array_filter( $ids ) ) );
}

/**
 * Read the pictures placed by hand on the Summit page: each is filed under the
 * last heading or label above it that names a tier ("Gold Sponsor:"). A
 * picture that appears under two headings keeps the later one; pictures above
 * any tier heading are not logos and are left alone.
 *
 * @param array[] $blocks Parsed blocks.
 * @param int     $tier   The tier heading in force (by reference).
 * @param array   $found  Attachment ID => tier ID (by reference).
 */
function rad_collect_page_logos( $blocks, &$tier, &$found ) {
	foreach ( $blocks as $block ) {
		$name = $block['blockName'];

		if ( 'core/heading' === $name || 'core/paragraph' === $name ) {
			$label = rad_tier_by_label( $block['innerHTML'] );
			if ( $label ) {
				$tier = $label;
			}
		} elseif ( $tier && ( 'core/image' === $name || 'core/gallery' === $name ) ) {
			foreach ( rad_block_image_ids( $block ) as $id ) {
				$found[ $id ] = $tier;
			}
		} elseif ( $name && ! empty( $block['innerBlocks'] ) ) {
			rad_collect_page_logos( $block['innerBlocks'], $tier, $found );
		}
	}
}

/**
 * The logos on the Summit page that are not sponsors yet.
 *
 * @return array[] Each: id (attachment), tier (ID).
 */
function rad_find_page_logos() {
	$page = get_page_by_path( 'summit' );

	if ( ! $page instanceof WP_Post || '' === trim( $page->post_content ) ) {
		return array();
	}

	$tier  = 0;
	$found = array();
	rad_collect_page_logos( parse_blocks( $page->post_content ), $tier, $found );

	$used = array();
	foreach ( get_posts( array( 'post_type' => 'rad_sponsor', 'post_status' => 'any', 'posts_per_page' => -1, 'fields' => 'ids', 'no_found_rows' => true ) ) as $sponsor_id ) {
		$used[] = (int) get_post_thumbnail_id( $sponsor_id );
	}

	$logos = array();
	foreach ( $found as $id => $tier_id ) {
		if ( ! in_array( (int) $id, $used, true ) && 'attachment' === get_post_type( $id ) ) {
			$logos[] = array(
				'id'   => (int) $id,
				'tier' => (int) $tier_id,
			);
		}
	}

	return $logos;
}

/**
 * Take the logos already made into sponsors, and the tier headings above them,
 * off the Summit page. Everything else on the page stays. WordPress keeps the
 * earlier version of the page as a revision.
 *
 * @param int[] $moved Attachment IDs that are now sponsors.
 * @return int Number of blocks removed.
 */
function rad_strip_page_logos( $moved ) {
	$page = get_page_by_path( 'summit' );

	if ( ! $page instanceof WP_Post ) {
		return 0;
	}

	$kept    = array();
	$removed = 0;

	foreach ( parse_blocks( $page->post_content ) as $block ) {
		$name = $block['blockName'];

		if ( null === $name ) {
			if ( '' !== trim( $block['innerHTML'] ) ) {
				$kept[] = $block;
			}
			continue;
		}

		if ( 'core/heading' === $name || 'core/paragraph' === $name ) {
			$text = trim( wp_strip_all_tags( $block['innerHTML'] ) );
			if ( '' === $text || rad_tier_by_label( $text ) ) {
				$removed++;
				continue;
			}
		} elseif ( 'core/image' === $name || 'core/gallery' === $name ) {
			$ids = rad_block_image_ids( $block );
			if ( $ids && ! array_diff( $ids, $moved ) ) {
				$removed++;
				continue;
			}
		}

		$kept[] = $block;
	}

	if ( ! $removed ) {
		return 0;
	}

	kses_remove_filters();
	wp_update_post(
		array(
			'ID'           => $page->ID,
			'post_content' => serialize_blocks( $kept ),
		)
	);
	kses_init_filters();

	return $removed;
}

/**
 * Make one sponsor from a picture.
 *
 * @param int    $attachment_id The logo.
 * @param string $name          The organisation's name.
 * @param int    $tier          Tier ID.
 * @param int    $order         Order within its tier.
 * @return int Sponsor ID, or 0.
 */
function rad_create_sponsor( $attachment_id, $name, $tier, $order ) {
	if ( ! $attachment_id || 'attachment' !== get_post_type( $attachment_id ) ) {
		return 0;
	}

	$name = '' !== trim( $name ) ? sanitize_text_field( $name ) : rad_name_from_filename( get_the_title( $attachment_id ) );
	$post = wp_insert_post(
		array(
			'post_type'   => 'rad_sponsor',
			'post_status' => 'publish',
			'post_title'  => $name,
			'menu_order'  => (int) $order,
		)
	);

	if ( ! $post || is_wp_error( $post ) ) {
		return 0;
	}

	set_post_thumbnail( $post, $attachment_id );

	if ( $tier && term_exists( $tier, 'rad_tier' ) ) {
		wp_set_object_terms( $post, array( (int) $tier ), 'rad_tier' );
	}

	return (int) $post;
}

/**
 * The tier <select>, with one option preselected.
 *
 * @param string $name     Field name.
 * @param int    $selected Tier ID.
 * @return string
 */
function rad_tier_select( $name, $selected = 0 ) {
	$html = '<select name="' . esc_attr( $name ) . '">';
	foreach ( rad_all_tiers() as $term ) {
		$html .= sprintf( '<option value="%d"%s>%s</option>', (int) $term->term_id, selected( (int) $selected, (int) $term->term_id, false ), esc_html( $term->name ) );
	}
	return $html . '</select>';
}

/**
 * The importer screen.
 */
function rad_render_sponsor_import() {
	if ( ! current_user_can( 'edit_posts' ) ) {
		return;
	}

	$page_logos = rad_find_page_logos();
	$in_page    = wp_list_pluck( $page_logos, 'id' );

	$used = get_posts(
		array(
			'post_type'      => 'rad_sponsor',
			'post_status'    => 'any',
			'posts_per_page' => -1,
			'fields'         => 'ids',
			'no_found_rows'  => true,
		)
	);

	$exclude = array_filter( array_merge( array_map( 'intval', array_map( 'get_post_thumbnail_id', $used ) ), $in_page ) );

	$images = get_posts(
		array(
			'post_type'      => 'attachment',
			'post_status'    => 'inherit',
			'post_mime_type' => 'image',
			'posts_per_page' => 120,
			'orderby'        => 'date',
			'order'          => 'DESC',
			'post__not_in'   => $exclude,
			'no_found_rows'  => true,
		)
	);

	wp_enqueue_style( 'rad-admin', get_theme_file_uri( 'assets/css/admin.css' ), array(), RAD_VERSION );
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Add several logos at once', 'redcliffe-advisory' ); ?></h1>

		<?php if ( $page_logos ) : ?>
			<h2><?php esc_html_e( 'Logos already on your Summit page', 'redcliffe-advisory' ); ?></h2>
			<p><?php esc_html_e( 'You placed these on the Summit page yourself. Each is listed under the heading you gave it. Check the names and tiers, then move them into Sponsors, where the website fits and arranges them for you.', 'redcliffe-advisory' ); ?></p>
			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
				<input type="hidden" name="action" value="rad_import_page_logos" />
				<?php wp_nonce_field( 'rad_import_page_logos', 'rad_import_nonce' ); ?>
				<table class="widefat striped rad-import-page">
					<thead><tr><th class="check-column"></th><th><?php esc_html_e( 'Logo', 'redcliffe-advisory' ); ?></th><th><?php esc_html_e( 'Name', 'redcliffe-advisory' ); ?></th><th><?php esc_html_e( 'Tier', 'redcliffe-advisory' ); ?></th></tr></thead>
					<tbody>
					<?php foreach ( $page_logos as $logo ) : ?>
						<tr>
							<th class="check-column"><input type="checkbox" name="rad_page_ids[]" value="<?php echo esc_attr( $logo['id'] ); ?>" checked /></th>
							<td class="rad-import-thumb"><?php echo wp_get_attachment_image( $logo['id'], 'medium' ); ?></td>
							<td><input type="text" class="regular-text" name="rad_page_names[<?php echo esc_attr( $logo['id'] ); ?>]" value="<?php echo esc_attr( rad_name_from_filename( get_the_title( $logo['id'] ) ) ); ?>" /></td>
							<td><?php echo rad_tier_select( 'rad_page_tiers[' . $logo['id'] . ']', $logo['tier'] ); // phpcs:ignore WordPress.Security.EscapeOutput -- built from escaped parts. ?></td>
						</tr>
					<?php endforeach; ?>
					</tbody>
				</table>
				<p>
					<label><input type="checkbox" name="rad_strip_page" value="1" checked />
					<?php esc_html_e( 'Then take these logos and their headings off the Summit page, so they are not shown twice. WordPress keeps the previous version of the page, which can be restored under Pages → Summit → Revisions.', 'redcliffe-advisory' ); ?></label>
				</p>
				<?php submit_button( __( 'Move these logos into Sponsors', 'redcliffe-advisory' ) ); ?>
			</form>
			<hr />
		<?php endif; ?>

		<h2><?php esc_html_e( 'Other pictures in the Media Library', 'redcliffe-advisory' ); ?></h2>
		<?php if ( ! $images ) : ?>
			<p><?php esc_html_e( 'There are no other unused pictures in the Media Library. Upload logos under Media → Add New, or add a sponsor one at a time.', 'redcliffe-advisory' ); ?>
				<a class="button" href="<?php echo esc_url( admin_url( 'post-new.php?post_type=rad_sponsor' ) ); ?>"><?php esc_html_e( 'Add sponsor', 'redcliffe-advisory' ); ?></a></p>
		<?php else : ?>
			<p><?php esc_html_e( 'Tick the logos you have uploaded, choose their tier, check the names and click the button. Each becomes a sponsor you can still edit afterwards.', 'redcliffe-advisory' ); ?></p>
			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
				<input type="hidden" name="action" value="rad_import_sponsors" />
				<?php wp_nonce_field( 'rad_import_sponsors', 'rad_import_nonce' ); ?>

				<p>
					<label for="rad_import_tier"><strong><?php esc_html_e( 'Put the ticked logos in this tier:', 'redcliffe-advisory' ); ?></strong></label>
					<?php echo rad_tier_select( 'rad_import_tier' ); // phpcs:ignore WordPress.Security.EscapeOutput -- built from escaped parts. ?>
				</p>

				<div class="rad-import-grid">
					<?php foreach ( $images as $image ) : ?>
						<label class="rad-import-item">
							<input type="checkbox" name="rad_import_ids[]" value="<?php echo esc_attr( $image->ID ); ?>" />
							<span class="rad-import-thumb"><?php echo wp_get_attachment_image( $image->ID, 'medium' ); ?></span>
							<input type="text" name="rad_import_names[<?php echo esc_attr( $image->ID ); ?>]" value="<?php echo esc_attr( rad_name_from_filename( $image->post_title ) ); ?>" aria-label="<?php esc_attr_e( 'Name', 'redcliffe-advisory' ); ?>" />
						</label>
					<?php endforeach; ?>
				</div>

				<?php submit_button( __( 'Add the ticked logos as sponsors', 'redcliffe-advisory' ) ); ?>
			</form>
		<?php endif; ?>
	</div>
	<?php
}

/**
 * Check the importer form's nonce and permission.
 *
 * @param string $action Nonce action.
 */
function rad_import_guard( $action ) {
	if ( ! current_user_can( 'edit_posts' ) || ! isset( $_POST['rad_import_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['rad_import_nonce'] ) ), $action ) ) {
		wp_die( esc_html__( 'That did not work — please go back and try again.', 'redcliffe-advisory' ) );
	}
}

/**
 * Create a sponsor for each ticked logo from the Media Library.
 */
function rad_handle_sponsor_import() {
	rad_import_guard( 'rad_import_sponsors' );

	$tier  = isset( $_POST['rad_import_tier'] ) ? absint( wp_unslash( $_POST['rad_import_tier'] ) ) : 0;
	$ids   = isset( $_POST['rad_import_ids'] ) ? array_map( 'absint', (array) wp_unslash( $_POST['rad_import_ids'] ) ) : array();
	$names = isset( $_POST['rad_import_names'] ) ? (array) wp_unslash( $_POST['rad_import_names'] ) : array();
	$added = 0;

	foreach ( $ids as $attachment_id ) {
		$name = isset( $names[ $attachment_id ] ) ? (string) $names[ $attachment_id ] : '';

		if ( rad_create_sponsor( $attachment_id, $name, $tier, ( $added + 1 ) * 10 ) ) {
			$added++;
		}
	}

	wp_safe_redirect( add_query_arg( 'rad_added', $added, admin_url( 'edit.php?post_type=rad_sponsor' ) ) );
	exit;
}
add_action( 'admin_post_rad_import_sponsors', 'rad_handle_sponsor_import' );

/**
 * Move the logos found on the Summit page into Sponsors, and optionally take
 * them off the page.
 */
function rad_handle_page_logo_import() {
	rad_import_guard( 'rad_import_page_logos' );

	$ids   = isset( $_POST['rad_page_ids'] ) ? array_map( 'absint', (array) wp_unslash( $_POST['rad_page_ids'] ) ) : array();
	$names = isset( $_POST['rad_page_names'] ) ? (array) wp_unslash( $_POST['rad_page_names'] ) : array();
	$tiers = isset( $_POST['rad_page_tiers'] ) ? array_map( 'absint', (array) wp_unslash( $_POST['rad_page_tiers'] ) ) : array();
	$moved = array();
	$count = array();

	foreach ( $ids as $attachment_id ) {
		$tier         = isset( $tiers[ $attachment_id ] ) ? $tiers[ $attachment_id ] : 0;
		$count[ $tier ] = isset( $count[ $tier ] ) ? $count[ $tier ] + 1 : 1;
		$name         = isset( $names[ $attachment_id ] ) ? (string) $names[ $attachment_id ] : '';

		if ( rad_create_sponsor( $attachment_id, $name, $tier, $count[ $tier ] * 10 ) ) {
			$moved[] = $attachment_id;
		}
	}

	$stripped = ( $moved && ! empty( $_POST['rad_strip_page'] ) ) ? rad_strip_page_logos( $moved ) : 0;

	wp_safe_redirect(
		add_query_arg(
			array(
				'rad_added'    => count( $moved ),
				'rad_stripped' => $stripped ? 1 : 0,
			),
			admin_url( 'edit.php?post_type=rad_sponsor' )
		)
	);
	exit;
}
add_action( 'admin_post_rad_import_page_logos', 'rad_handle_page_logo_import' );

/**
 * "3 sponsors added" after an import.
 */
function rad_sponsor_import_notice() {
	if ( ! isset( $_GET['rad_added'] ) || ! current_user_can( 'edit_posts' ) ) { // phpcs:ignore WordPress.Security.NonceVerification -- display only.
		return;
	}

	$count = absint( $_GET['rad_added'] ); // phpcs:ignore WordPress.Security.NonceVerification
	$note  = ! empty( $_GET['rad_stripped'] ) ? ' ' . __( 'They have been taken off the Summit page.', 'redcliffe-advisory' ) : ''; // phpcs:ignore WordPress.Security.NonceVerification

	printf(
		'<div class="notice notice-success is-dismissible"><p>%s</p></div>',
		esc_html(
			sprintf(
				/* translators: %d: number of sponsors added. */
				_n( '%d sponsor added. Check its name and tier below, then look at the Summit page.', '%d sponsors added. Check their names and tiers below, then look at the Summit page.', $count, 'redcliffe-advisory' ),
				$count
			) . $note
		)
	);
}
add_action( 'admin_notices', 'rad_sponsor_import_notice' );

/**
 * On the Dashboard and the Sponsors list: point out logos already sitting on
 * the Summit page that could be moved into Sponsors.
 */
function rad_sponsor_move_notice() {
	$screen = get_current_screen();

	if ( ! $screen || ! in_array( $screen->id, array( 'dashboard', 'edit-rad_sponsor' ), true ) || ! current_user_can( 'edit_posts' ) ) {
		return;
	}

	$logos = rad_find_page_logos();

	if ( ! $logos ) {
		return;
	}

	printf(
		'<div class="notice notice-warning"><p><strong>%1$s</strong> %2$s <a class="button button-primary" href="%3$s">%4$s</a></p></div>',
		esc_html__( 'Your Summit page has logos that were placed by hand.', 'redcliffe-advisory' ),
		esc_html(
			sprintf(
				/* translators: %d: number of logos. */
				_n( '%d logo could be managed under Sponsors instead, where the website fits and arranges it by itself.', '%d logos could be managed under Sponsors instead, where the website fits and arranges them by itself.', count( $logos ), 'redcliffe-advisory' ),
				count( $logos )
			)
		),
		esc_url( admin_url( 'edit.php?post_type=rad_sponsor&page=rad-sponsor-import' ) ),
		esc_html__( 'Review and move them', 'redcliffe-advisory' )
	);
}
add_action( 'admin_notices', 'rad_sponsor_move_notice' );

/* -------------------------------------------------------------------------
 * Logos: trimming, fitting and choosing a tile colour
 * ---------------------------------------------------------------------- */

/**
 * Where the trimmed logos are kept.
 *
 * @return array { path, url }
 */
function rad_logo_dir() {
	$uploads = wp_upload_dir( null, false );

	return array(
		'path' => $uploads['basedir'] . '/rad-logos',
		'url'  => set_url_scheme( $uploads['baseurl'] ) . '/rad-logos',
	);
}

/**
 * A blank, transparent true-colour canvas.
 *
 * @param int $width  Width in pixels.
 * @param int $height Height in pixels.
 * @return GdImage|resource
 */
function rad_logo_canvas( $width, $height ) {
	$canvas = imagecreatetruecolor( $width, $height );
	imagealphablending( $canvas, false );
	imagesavealpha( $canvas, true );
	imagefill( $canvas, 0, 0, imagecolorallocatealpha( $canvas, 0, 0, 0, 127 ) );

	return $canvas;
}

/**
 * Look at a logo: where the artwork is (so empty margins can be trimmed) and
 * whether it is light on transparent (so it needs a dark tile).
 *
 * @param string $file Path to the image.
 * @return array|null { card, crop: [x, y, width, height]|null, width, height } or null if it cannot be read.
 */
function rad_analyse_logo( $file ) {
	if ( ! function_exists( 'imagecreatefromstring' ) || ! function_exists( 'imagecopyresampled' ) || ! is_readable( $file ) ) {
		return null;
	}

	$info = @getimagesize( $file ); // phpcs:ignore WordPress.PHP.NoSilencedErrors
	if ( ! $info || $info[0] < 1 || $info[1] < 1 || $info[0] * $info[1] > 12000000 ) {
		return null; // Too large to study safely: the picture is used as it is.
	}

	wp_raise_memory_limit( 'image' );

	$data   = @file_get_contents( $file ); // phpcs:ignore WordPress.PHP.NoSilencedErrors, WordPress.WP.AlternativeFunctions
	$source = $data ? @imagecreatefromstring( $data ) : false; // phpcs:ignore WordPress.PHP.NoSilencedErrors
	if ( ! $source ) {
		return null;
	}

	$width  = imagesx( $source );
	$height = imagesy( $source );

	// Study a small copy: quick, and enough to find the artwork.
	$scale  = min( 1, 240 / max( $width, $height ) );
	$sw     = max( 1, (int) round( $width * $scale ) );
	$sh     = max( 1, (int) round( $height * $scale ) );
	$sample = rad_logo_canvas( $sw, $sh );
	imagecopyresampled( $sample, $source, 0, 0, 0, 0, $sw, $sh, $width, $height );

	$pixels      = array();
	$transparent = 0;

	for ( $y = 0; $y < $sh; $y++ ) {
		for ( $x = 0; $x < $sw; $x++ ) {
			$c = imagecolorat( $sample, $x, $y );
			if ( ( ( $c >> 24 ) & 0x7F ) > 100 ) {
				$transparent++;
			}
			$pixels[ $y * $sw + $x ] = $c;
		}
	}

	$has_alpha = $transparent / ( $sw * $sh ) > 0.015;
	$is_art    = null;
	$bg_light  = true;
	$bg_hex    = '';

	if ( $has_alpha ) {
		// Artwork is whatever is not see-through.
		$is_art = function ( $c ) {
			return ( ( $c >> 24 ) & 0x7F ) <= 90;
		};
	} else {
		// A flat backdrop: the colour the four corners agree on.
		$corners = array( array( 0, 0 ), array( $sw - 1, 0 ), array( 0, $sh - 1 ), array( $sw - 1, $sh - 1 ) );
		$sum     = array( 0, 0, 0 );
		$colours = array();

		foreach ( $corners as $corner ) {
			$c         = $pixels[ $corner[1] * $sw + $corner[0] ];
			$colours[] = array( ( $c >> 16 ) & 0xFF, ( $c >> 8 ) & 0xFF, $c & 0xFF );
		}
		foreach ( $colours as $colour ) {
			$sum[0] += $colour[0];
			$sum[1] += $colour[1];
			$sum[2] += $colour[2];
		}
		$bg      = array( $sum[0] / 4, $sum[1] / 4, $sum[2] / 4 );
		$uniform = true;
		foreach ( $colours as $colour ) {
			if ( abs( $colour[0] - $bg[0] ) + abs( $colour[1] - $bg[1] ) + abs( $colour[2] - $bg[2] ) > 60 ) {
				$uniform = false;
			}
		}

		if ( $uniform ) {
			$bg_light = ( 0.2126 * $bg[0] + 0.7152 * $bg[1] + 0.0722 * $bg[2] ) / 255 >= 0.35;
			// The tile takes the picture's own backdrop, so there is no visible box
			// (white stays exactly white).
			$bg_hex = min( $bg ) >= 248 ? '#ffffff' : sprintf( '#%02x%02x%02x', round( $bg[0] ), round( $bg[1] ), round( $bg[2] ) );
			$is_art   = function ( $c ) use ( $bg ) {
				return abs( ( ( $c >> 16 ) & 0xFF ) - $bg[0] ) + abs( ( ( $c >> 8 ) & 0xFF ) - $bg[1] ) + abs( ( $c & 0xFF ) - $bg[2] ) > 48;
			};
		}
	}

	$result = array(
		'card'   => $bg_light ? 'light' : 'dark',
		'bg'     => $bg_hex,
		'crop'   => null,
		'width'  => $width,
		'height' => $height,
	);

	if ( $is_art ) {
		$min_x = $sw;
		$min_y = $sh;
		$max_x = -1;
		$max_y = -1;
		$count = 0;
		$lum   = 0;

		foreach ( $pixels as $i => $c ) {
			if ( ! $is_art( $c ) ) {
				continue;
			}
			$x = $i % $sw;
			$y = (int) ( $i / $sw );
			$min_x = min( $min_x, $x );
			$max_x = max( $max_x, $x );
			$min_y = min( $min_y, $y );
			$max_y = max( $max_y, $y );
			$count++;
			$lum += 0.2126 * ( ( $c >> 16 ) & 0xFF ) + 0.7152 * ( ( $c >> 8 ) & 0xFF ) + 0.0722 * ( $c & 0xFF );
		}

		if ( $count > $sw * $sh * 0.002 ) {
			if ( $has_alpha ) {
				// Light artwork on nothing needs a dark tile; everything else, a white one.
				$result['card'] = ( $lum / $count ) / 255 >= 0.72 ? 'dark' : 'light';
			}

			$pad   = max( 2, (int) round( max( $max_x - $min_x, $max_y - $min_y ) * 0.03 ) );
			$x0    = max( 0, $min_x - $pad );
			$y0    = max( 0, $min_y - $pad );
			$x1    = min( $sw - 1, $max_x + $pad );
			$y1    = min( $sh - 1, $max_y + $pad );
			$rx    = $width / $sw;
			$ry    = $height / $sh;
			$crop  = array(
				(int) floor( $x0 * $rx ),
				(int) floor( $y0 * $ry ),
				(int) min( $width - floor( $x0 * $rx ), ceil( ( $x1 - $x0 + 1 ) * $rx ) ),
				(int) min( $height - floor( $y0 * $ry ), ceil( ( $y1 - $y0 + 1 ) * $ry ) ),
			);

			// Only worth trimming when a real margin goes.
			if ( $crop[2] * $crop[3] < $width * $height * 0.94 ) {
				$result['crop'] = $crop;
			}
		}
	}

	$result['source'] = $source;

	return $result;
}

/**
 * Trim a logo and store the result, working out which tile suits it.
 *
 * @param int $post_id       Sponsor ID.
 * @param int $attachment_id Logo attachment ID.
 * @return array The stored description: att, ver, file, width, height, card.
 */
function rad_build_logo( $post_id, $attachment_id ) {
	$data = array(
		'att'    => (int) $attachment_id,
		'ver'    => RAD_LOGO_VERSION,
		'file'   => '',
		'width'  => 0,
		'height' => 0,
		'card'   => 'light',
		'bg'     => '',
	);

	$file = get_attached_file( $attachment_id );
	$mime = get_post_mime_type( $attachment_id );

	// Vector artwork scales perfectly already.
	if ( $file && 'image/svg+xml' !== $mime ) {
		$analysis = rad_analyse_logo( $file );

		if ( $analysis ) {
			$data['card'] = $analysis['card'];
			$data['bg']   = $analysis['bg'];

			if ( $analysis['crop'] ) {
				list( $cx, $cy, $cw, $ch ) = $analysis['crop'];

				$k  = min( 1, 900 / max( $cw, $ch ) );
				$tw = max( 1, (int) round( $cw * $k ) );
				$th = max( 1, (int) round( $ch * $k ) );
				$out = rad_logo_canvas( $tw, $th );
				imagecopyresampled( $out, $analysis['source'], 0, 0, $cx, $cy, $tw, $th, $cw, $ch );

				$dir = rad_logo_dir();
				if ( wp_mkdir_p( $dir['path'] ) ) {
					foreach ( (array) glob( $dir['path'] . '/sponsor-' . (int) $post_id . '-*.png' ) as $old ) {
						wp_delete_file( $old );
					}

					$name = sprintf( 'sponsor-%d-%d-v%d.png', $post_id, $attachment_id, RAD_LOGO_VERSION );

					if ( imagepng( $out, $dir['path'] . '/' . $name, 7 ) ) {
						$data['file']   = $name;
						$data['width']  = $tw;
						$data['height'] = $th;
					}
				}
			}
		}
	}

	update_post_meta( $post_id, '_rad_logo_norm', $data );

	return $data;
}

/**
 * Everything needed to draw a sponsor's logo: the picture to use, its size,
 * and which tile colour to put it on. Builds (and remembers) the trimmed
 * picture the first time.
 *
 * @param int $post_id Sponsor ID.
 * @return array|null { src, width, height, card, bg } or null when there is no logo.
 */
function rad_sponsor_logo( $post_id ) {
	$attachment_id = (int) get_post_thumbnail_id( $post_id );

	if ( ! $attachment_id ) {
		return null;
	}

	$dir  = rad_logo_dir();
	$data = get_post_meta( $post_id, '_rad_logo_norm', true );

	$fresh = is_array( $data )
		&& isset( $data['att'], $data['ver'], $data['file'] )
		&& array_key_exists( 'bg', $data )
		&& (int) $data['att'] === $attachment_id
		&& (int) $data['ver'] === RAD_LOGO_VERSION
		&& ( '' === $data['file'] || file_exists( $dir['path'] . '/' . $data['file'] ) );

	if ( ! $fresh ) {
		$data = rad_build_logo( $post_id, $attachment_id );
	}

	$card = get_post_meta( $post_id, '_rad_logo_card', true );
	$auto = ! in_array( $card, array( 'light', 'dark' ), true );
	$card = $auto ? $data['card'] : $card;
	$bg   = $auto && preg_match( '/^#[0-9a-f]{6}$/', (string) $data['bg'] ) ? $data['bg'] : '';

	if ( $data['file'] ) {
		return array(
			'src'    => $dir['url'] . '/' . $data['file'],
			'width'  => (int) $data['width'],
			'height' => (int) $data['height'],
			'card'   => $card,
			'bg'     => $bg,
		);
	}

	$image = wp_get_attachment_image_src( $attachment_id, 'large' );

	if ( ! $image ) {
		return null;
	}

	return array(
		'src'    => $image[0],
		'width'  => (int) $image[1],
		'height' => (int) $image[2],
		'card'   => $card,
		'bg'     => $bg,
	);
}

/* -------------------------------------------------------------------------
 * Showing the sponsors
 * ---------------------------------------------------------------------- */

/**
 * The tiers that have sponsors, in page order, each with its sponsors.
 *
 * @return array[] Each: name, slug, size, names (bool), sponsors (WP_Post[]).
 */
function rad_sponsor_tiers() {
	static $cache = null;

	if ( null !== $cache ) {
		return $cache;
	}

	$sponsors = get_posts(
		array(
			'post_type'        => 'rad_sponsor',
			'post_status'      => 'publish',
			'posts_per_page'   => -1,
			'orderby'          => array(
				'menu_order' => 'ASC',
				'title'      => 'ASC',
			),
			'no_found_rows'    => true,
			'suppress_filters' => false,
		)
	);

	$grouped = array();
	$loose   = array();

	foreach ( $sponsors as $sponsor ) {
		$terms = wp_get_object_terms( $sponsor->ID, 'rad_tier', array( 'fields' => 'ids' ) );

		if ( is_wp_error( $terms ) || ! $terms ) {
			$loose[] = $sponsor;
			continue;
		}

		$grouped[ (int) $terms[0] ][] = $sponsor;
	}

	$cache = array();

	foreach ( rad_all_tiers() as $term ) {
		if ( empty( $grouped[ $term->term_id ] ) ) {
			continue;
		}

		$cache[] = array(
			'name'     => $term->name,
			'slug'     => $term->slug,
			'size'     => rad_sanitize_tier_size( (string) get_term_meta( $term->term_id, 'rad_tier_size', true ) ),
			'names'    => (bool) get_term_meta( $term->term_id, 'rad_tier_names', true ),
			'sponsors' => $grouped[ $term->term_id ],
		);
	}

	if ( $loose ) {
		$cache[] = array(
			'name'     => '',
			'slug'     => 'other',
			'size'     => 'small',
			'names'    => false,
			'sponsors' => $loose,
		);
	}

	return $cache;
}

/**
 * Whether there is anything to show, so the Summit page knows whether to use
 * the sponsors or fall back to the original design.
 *
 * @return bool
 */
function rad_has_sponsors() {
	return (bool) rad_sponsor_tiers();
}

/**
 * How many tiles per row give the most even rows: 7 tiles that could fit 6
 * across become 4 and 3, not 6 and 1.
 *
 * @param int $count   Tiles in the tier.
 * @param int $maximum Most that fit across.
 * @return int
 */
function rad_balanced_columns( $count, $maximum ) {
	if ( $count <= $maximum ) {
		return max( 1, (int) $count );
	}

	return (int) ceil( $count / ceil( $count / $maximum ) );
}

/**
 * The inline style that gives a tile its logo's own backdrop colour, or ''.
 *
 * @param array|null $logo From rad_sponsor_logo().
 * @return string
 */
function rad_tile_style( $logo ) {
	return $logo && ! empty( $logo['bg'] ) ? ' style="--tile-bg:' . esc_attr( $logo['bg'] ) . '"' : '';
}

/**
 * One tile.
 *
 * @param WP_Post $sponsor    The sponsor.
 * @param bool    $show_names Whether the tier writes names under logos.
 */
function rad_render_sponsor_tile( $sponsor, $show_names ) {
	$name = wp_strip_all_tags( $sponsor->post_title );
	$url  = (string) get_post_meta( $sponsor->ID, '_rad_sponsor_url', true );
	$role = (string) get_post_meta( $sponsor->ID, '_rad_sponsor_role', true );
	$logo = rad_sponsor_logo( $sponsor->ID );
	$card = $logo ? $logo['card'] : 'dark';
	$text = $show_names || ! $logo;

	$open  = $url
		? sprintf( '<a class="sponsor-card" href="%s" target="_blank" rel="noopener">', esc_url( $url ) )
		: '<div class="sponsor-card">';
	$close = $url ? '</a>' : '</div>';

	printf( '<li class="sponsor is-card-%s%s"%s>', esc_attr( $card ), $logo ? '' : ' is-name-only', rad_tile_style( $logo ) ); // phpcs:ignore WordPress.Security.EscapeOutput -- escaped in rad_tile_style().
	echo $open; // phpcs:ignore WordPress.Security.EscapeOutput -- built above from escaped parts.

	if ( $logo ) {
		printf(
			'<span class="sponsor-logo"><img src="%1$s" width="%2$d" height="%3$d" alt="%4$s" loading="lazy" decoding="async" /></span>',
			esc_url( $logo['src'] ),
			(int) $logo['width'],
			(int) $logo['height'],
			esc_attr( $text ? '' : $name )
		);
	}

	if ( $text ) {
		printf( '<span class="sponsor-name">%s</span>', esc_html( $name ) );
		if ( $role && $show_names ) {
			printf( '<span class="sponsor-role">%s</span>', esc_html( $role ) );
		}
	}

	echo $close; // phpcs:ignore WordPress.Security.EscapeOutput -- fixed strings.
	echo "</li>\n";
}

/**
 * The whole sponsor wall, tier by tier.
 */
function rad_render_sponsors() {
	$sizes = rad_sponsor_sizes();

	echo '<div class="sponsor-wall">' . "\n";

	foreach ( rad_sponsor_tiers() as $tier ) {
		$count = count( $tier['sponsors'] );
		$max   = $sizes[ $tier['size'] ]['columns'];
		$id    = 'sponsors-' . sanitize_html_class( $tier['slug'] );

		printf(
			'<section class="sponsor-tier size-%1$s"%2$s>' . "\n",
			esc_attr( $tier['size'] ),
			$tier['name'] ? ' aria-labelledby="' . esc_attr( $id ) . '"' : ''
		);

		if ( $tier['name'] ) {
			printf( '<h3 class="sponsor-tier-title" id="%s">%s</h3>' . "\n", esc_attr( $id ), esc_html( $tier['name'] ) );
		}

		printf(
			'<ul class="sponsor-grid" style="--cols:%d;--cols-md:%d">' . "\n",
			(int) rad_balanced_columns( $count, $max[0] ),
			(int) rad_balanced_columns( $count, $max[1] )
		);

		foreach ( $tier['sponsors'] as $sponsor ) {
			rad_render_sponsor_tile( $sponsor, $tier['names'] );
		}

		echo "</ul>\n</section>\n";
	}

	echo "</div>\n";
}
