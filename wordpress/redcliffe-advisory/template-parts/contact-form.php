<?php
/**
 * The contact form.
 *
 * Same markup and styling as the original, submitted the WordPress way: no
 * JavaScript required, protected by a nonce and a honeypot, and it hands back
 * what was typed if anything needs correcting.
 *
 * @package Redcliffe_Advisory
 */

defined( 'ABSPATH' ) || exit;

$rad_status = rad_contact_status();
$rad_old    = rad_contact_old_input();

$rad_topics = array(
	'Advisory enquiry',
	'Summit enquiry',
	'Media request',
	'Private introduction',
	'Other',
);
?>
<form class="contact-form" id="contactForm" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
	<input type="hidden" name="action" value="rad_contact" />
	<?php wp_nonce_field( 'rad_contact', 'rad_contact_nonce' ); ?>

	<div style="position:absolute;left:-9999px;top:auto;width:1px;height:1px;overflow:hidden" aria-hidden="true">
		<label><?php esc_html_e( 'Leave this field empty', 'redcliffe-advisory' ); ?>
			<input type="text" name="rad_website" tabindex="-1" autocomplete="off" value="" />
		</label>
	</div>

	<div class="field-row">
		<label><span><?php esc_html_e( 'Name', 'redcliffe-advisory' ); ?></span><input name="name" autocomplete="name" required value="<?php echo esc_attr( $rad_old['name'] ); ?>" /></label>
		<label><span><?php esc_html_e( 'Email', 'redcliffe-advisory' ); ?></span><input name="email" type="email" autocomplete="email" required value="<?php echo esc_attr( $rad_old['email'] ); ?>" /></label>
	</div>
	<div class="field-row">
		<label><span><?php esc_html_e( 'Organisation', 'redcliffe-advisory' ); ?></span><input name="organisation" autocomplete="organization" value="<?php echo esc_attr( $rad_old['organisation'] ); ?>" /></label>
		<label>
			<span><?php esc_html_e( 'Topic', 'redcliffe-advisory' ); ?></span>
			<select name="topic">
				<?php foreach ( $rad_topics as $rad_topic ) : ?>
					<option value="<?php echo esc_attr( $rad_topic ); ?>"<?php selected( $rad_old['topic'], $rad_topic ); ?>><?php echo esc_html( $rad_topic ); ?></option>
				<?php endforeach; ?>
			</select>
		</label>
	</div>
	<label><span><?php esc_html_e( 'Message', 'redcliffe-advisory' ); ?></span><textarea name="message" rows="8" required><?php echo esc_textarea( $rad_old['message'] ); ?></textarea></label>
	<div class="form-actions">
		<p id="contactStatus" role="status"<?php echo ( $rad_status && $rad_status['is_error'] ) ? ' class="is-error"' : ''; ?>><?php echo $rad_status ? esc_html( $rad_status['message'] ) : ''; ?></p>
		<button class="btn" type="submit"><span><?php esc_html_e( 'Send message', 'redcliffe-advisory' ); ?></span><span class="arr">&rarr;</span></button>
	</div>
</form>
