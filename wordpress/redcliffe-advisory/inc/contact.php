<?php
/**
 * The contact form.
 *
 * Replaces the old Node endpoint. Every enquiry is saved in WordPress under
 * "Enquiries" and emailed to the address set in the Customizer. If the email
 * fails, the enquiry is still saved and flagged, so nothing is ever lost.
 *
 * @package Redcliffe_Advisory
 */

defined( 'ABSPATH' ) || exit;

const RAD_ENQUIRY_POST_TYPE = 'rad_enquiry';

/**
 * Register the Enquiries list in the WordPress admin.
 */
function rad_register_enquiry_post_type() {
	register_post_type(
		RAD_ENQUIRY_POST_TYPE,
		array(
			'labels'          => array(
				'name'          => __( 'Enquiries', 'redcliffe-advisory' ),
				'singular_name' => __( 'Enquiry', 'redcliffe-advisory' ),
				'menu_name'     => __( 'Enquiries', 'redcliffe-advisory' ),
				'search_items'  => __( 'Search enquiries', 'redcliffe-advisory' ),
				'not_found'     => __( 'No enquiries yet.', 'redcliffe-advisory' ),
			),
			'public'          => false,
			'show_ui'         => true,
			'show_in_menu'    => true,
			'menu_position'   => 26,
			'menu_icon'       => 'dashicons-email-alt',
			'supports'        => array( 'title', 'editor' ),
			'capability_type' => 'post',
			'map_meta_cap'    => true,
			'capabilities'    => array(
				// Enquiries arrive from the website; they are never written by hand.
				'create_posts' => 'do_not_allow',
			),
		)
	);
}
add_action( 'init', 'rad_register_enquiry_post_type' );

/**
 * Columns for the Enquiries list.
 *
 * @param array $columns Existing columns.
 * @return array
 */
function rad_enquiry_columns( $columns ) {
	return array(
		'cb'            => isset( $columns['cb'] ) ? $columns['cb'] : '',
		'title'         => __( 'From', 'redcliffe-advisory' ),
		'rad_email'     => __( 'Email', 'redcliffe-advisory' ),
		'rad_topic'     => __( 'Topic', 'redcliffe-advisory' ),
		'rad_delivery'  => __( 'Emailed', 'redcliffe-advisory' ),
		'date'          => __( 'Received', 'redcliffe-advisory' ),
	);
}
add_filter( 'manage_' . RAD_ENQUIRY_POST_TYPE . '_posts_columns', 'rad_enquiry_columns' );

/**
 * Fill in the custom columns.
 *
 * @param string $column  Column name.
 * @param int    $post_id Enquiry ID.
 */
function rad_enquiry_column_content( $column, $post_id ) {
	if ( 'rad_email' === $column ) {
		$email = get_post_meta( $post_id, 'rad_email', true );
		if ( $email ) {
			printf( '<a href="mailto:%1$s">%1$s</a>', esc_attr( $email ) );
		}
		return;
	}

	if ( 'rad_topic' === $column ) {
		echo esc_html( get_post_meta( $post_id, 'rad_topic', true ) );
		return;
	}

	if ( 'rad_delivery' === $column ) {
		$status = get_post_meta( $post_id, 'rad_delivery', true );
		echo 'sent' === $status
			? esc_html__( 'Yes', 'redcliffe-advisory' )
			: '<strong>' . esc_html__( 'No — check email settings', 'redcliffe-advisory' ) . '</strong>';
	}
}
add_action( 'manage_' . RAD_ENQUIRY_POST_TYPE . '_posts_custom_column', 'rad_enquiry_column_content', 10, 2 );

/**
 * Show the sender's details above the message.
 */
function rad_enquiry_meta_box() {
	add_meta_box(
		'rad_enquiry_details',
		__( 'Sender', 'redcliffe-advisory' ),
		'rad_render_enquiry_meta_box',
		RAD_ENQUIRY_POST_TYPE,
		'side',
		'high'
	);
}
add_action( 'add_meta_boxes', 'rad_enquiry_meta_box' );

/**
 * Render the sender details.
 *
 * @param WP_Post $post Enquiry.
 */
function rad_render_enquiry_meta_box( $post ) {
	$rows = array(
		__( 'Name', 'redcliffe-advisory' )         => get_post_meta( $post->ID, 'rad_name', true ),
		__( 'Email', 'redcliffe-advisory' )        => get_post_meta( $post->ID, 'rad_email', true ),
		__( 'Organisation', 'redcliffe-advisory' ) => get_post_meta( $post->ID, 'rad_organisation', true ),
		__( 'Topic', 'redcliffe-advisory' )        => get_post_meta( $post->ID, 'rad_topic', true ),
		__( 'Sent to', 'redcliffe-advisory' )      => get_post_meta( $post->ID, 'rad_recipient', true ),
	);

	echo '<table style="width:100%">';
	foreach ( $rows as $label => $value ) {
		if ( ! $value ) {
			continue;
		}
		printf(
			'<tr><th scope="row" style="text-align:left;padding:4px 8px 4px 0">%s</th><td style="padding:4px 0">%s</td></tr>',
			esc_html( $label ),
			esc_html( $value )
		);
	}
	echo '</table>';
}

/**
 * Where to send the visitor back to, with a message.
 *
 * @param string $status Status code for the form to display.
 * @param string $token  Optional token for restoring what was typed.
 */
function rad_contact_redirect( $status, $token = '' ) {
	$url = wp_get_referer();

	if ( ! $url ) {
		$url = rad_url( 'contact' );
	}

	$url = remove_query_arg( array( 'rad_status', 'rad_token' ), $url );
	$url = add_query_arg( 'rad_status', rawurlencode( $status ), $url );

	if ( $token ) {
		$url = add_query_arg( 'rad_token', rawurlencode( $token ), $url );
	}

	wp_safe_redirect( $url . '#contactForm' );
	exit;
}

/**
 * Handle a contact form submission.
 */
function rad_handle_contact_form() {
	$nonce = isset( $_POST['rad_contact_nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['rad_contact_nonce'] ) ) : '';

	if ( ! wp_verify_nonce( $nonce, 'rad_contact' ) ) {
		rad_contact_redirect( 'expired' );
	}

	// Honeypot: real people never fill this in. Answer as though all is well so
	// the sender learns nothing about why it did not arrive.
	if ( ! empty( $_POST['rad_website'] ) ) {
		rad_contact_redirect( 'sent' );
	}

	$fields = array(
		'name'         => isset( $_POST['name'] ) ? sanitize_text_field( wp_unslash( $_POST['name'] ) ) : '',
		'email'        => isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '',
		'organisation' => isset( $_POST['organisation'] ) ? sanitize_text_field( wp_unslash( $_POST['organisation'] ) ) : '',
		'topic'        => isset( $_POST['topic'] ) ? sanitize_text_field( wp_unslash( $_POST['topic'] ) ) : '',
		'message'      => isset( $_POST['message'] ) ? sanitize_textarea_field( wp_unslash( $_POST['message'] ) ) : '',
	);

	// Hold on to what was typed, so a mistake never costs the visitor their message.
	$token = wp_generate_password( 12, false );
	set_transient( 'rad_contact_' . $token, $fields, 10 * MINUTE_IN_SECONDS );

	if ( '' === $fields['name'] || '' === $fields['email'] || '' === $fields['message'] ) {
		rad_contact_redirect( 'incomplete', $token );
	}

	if ( ! is_email( $fields['email'] ) ) {
		rad_contact_redirect( 'invalid-email', $token );
	}

	$recipient = get_theme_mod(
		rad_mod_name( 'contact.recipient.email' ),
		'karina.robinson@redcliffeadvisory.com'
	);

	if ( ! is_email( $recipient ) ) {
		$recipient = get_option( 'admin_email' );
	}

	$sent = rad_send_enquiry_email( $recipient, $fields );

	wp_insert_post(
		array(
			'post_type'    => RAD_ENQUIRY_POST_TYPE,
			'post_status'  => 'publish',
			'post_title'   => $fields['name'] ? $fields['name'] : __( 'Website enquiry', 'redcliffe-advisory' ),
			'post_content' => $fields['message'],
			'meta_input'   => array(
				'rad_name'         => $fields['name'],
				'rad_email'        => $fields['email'],
				'rad_organisation' => $fields['organisation'],
				'rad_topic'        => $fields['topic'],
				'rad_recipient'    => $recipient,
				'rad_delivery'     => $sent ? 'sent' : 'not_sent',
			),
		)
	);

	delete_transient( 'rad_contact_' . $token );

	rad_contact_redirect( 'sent' );
}
add_action( 'admin_post_nopriv_rad_contact', 'rad_handle_contact_form' );
add_action( 'admin_post_rad_contact', 'rad_handle_contact_form' );

/**
 * Email one enquiry.
 *
 * Replies go to the enquirer, so answering is a matter of pressing Reply.
 *
 * @param string $recipient Address to notify.
 * @param array  $fields    Submitted values.
 * @return bool Whether WordPress accepted the message for delivery.
 */
function rad_send_enquiry_email( $recipient, $fields ) {
	$subject = sprintf(
		/* translators: %s: enquiry topic or sender name. */
		__( 'Redcliffe website enquiry: %s', 'redcliffe-advisory' ),
		$fields['topic'] ? $fields['topic'] : $fields['name']
	);

	$body = implode(
		"\n",
		array(
			sprintf( 'Name: %s', $fields['name'] ),
			sprintf( 'Email: %s', $fields['email'] ),
			sprintf( 'Organisation: %s', $fields['organisation'] ? $fields['organisation'] : '-' ),
			sprintf( 'Topic: %s', $fields['topic'] ? $fields['topic'] : '-' ),
			'',
			$fields['message'],
			'',
			sprintf( '— Sent from %s', home_url( '/' ) ),
		)
	);

	$headers = array(
		sprintf( 'Reply-To: %s <%s>', $fields['name'], $fields['email'] ),
	);

	add_filter( 'wp_mail_from_name', 'rad_mail_from_name' );
	$sent = wp_mail( $recipient, $subject, $body, $headers );
	remove_filter( 'wp_mail_from_name', 'rad_mail_from_name' );

	return (bool) $sent;
}

/**
 * Send enquiry notifications under the site's own name.
 *
 * @return string
 */
function rad_mail_from_name() {
	return wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES );
}

/**
 * The message shown above the form after a submission.
 *
 * @return array{message:string,is_error:bool}|null
 */
function rad_contact_status() {
	if ( ! isset( $_GET['rad_status'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		return null;
	}

	$status = sanitize_key( wp_unslash( $_GET['rad_status'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended

	$messages = array(
		'sent'          => array( __( 'Thank you. Your message has been received.', 'redcliffe-advisory' ), false ),
		'incomplete'    => array( __( 'Name, email, and message are required.', 'redcliffe-advisory' ), true ),
		'invalid-email' => array( __( 'Enter a valid email address.', 'redcliffe-advisory' ), true ),
		'expired'       => array( __( 'That form had been open a while. Please send it again.', 'redcliffe-advisory' ), true ),
	);

	if ( ! isset( $messages[ $status ] ) ) {
		return null;
	}

	return array(
		'message'  => $messages[ $status ][0],
		'is_error' => $messages[ $status ][1],
	);
}

/**
 * What the visitor typed, when a submission bounced back with an error.
 *
 * @return array
 */
function rad_contact_old_input() {
	$empty = array(
		'name'         => '',
		'email'        => '',
		'organisation' => '',
		'topic'        => '',
		'message'      => '',
	);

	if ( ! isset( $_GET['rad_token'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		return $empty;
	}

	$token = sanitize_text_field( wp_unslash( $_GET['rad_token'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$saved = get_transient( 'rad_contact_' . $token );

	return is_array( $saved ) ? array_merge( $empty, $saved ) : $empty;
}
