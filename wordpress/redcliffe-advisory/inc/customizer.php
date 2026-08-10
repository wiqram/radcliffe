<?php
/**
 * The Customizer panel — this theme's content editor.
 *
 * Appearance > Customize > Redcliffe Advisory shows one section per page of the
 * website, each holding that page's editable text, its photographs, and
 * switches for showing or hiding whole sections.
 *
 * @package Redcliffe_Advisory
 */

defined( 'ABSPATH' ) || exit;

/**
 * Allow the inline formatting the design uses, and nothing else.
 *
 * @param string $value Submitted value.
 * @return string
 */
function rad_sanitize_html( $value ) {
	return wp_kses_post( $value );
}

/**
 * Checkbox sanitiser.
 *
 * @param mixed $value Submitted value.
 * @return bool
 */
function rad_sanitize_checkbox( $value ) {
	return (bool) $value;
}

/**
 * Register every content control.
 *
 * @param WP_Customize_Manager $wp_customize Customizer manager.
 */
function rad_customize_register( $wp_customize ) {
	$panels = require get_theme_file_path( 'inc/customizer-fields.php' );

	$wp_customize->add_panel(
		'rad_content',
		array(
			'title'       => __( 'Redcliffe Advisory', 'redcliffe-advisory' ),
			'description' => __( 'The words and pictures on the website. Clear a field to put the original wording back.', 'redcliffe-advisory' ),
			'priority'    => 20,
		)
	);

	$priority = 10;

	foreach ( $panels as $panel ) {
		$section_id = 'rad_section_' . $panel['id'];

		$wp_customize->add_section(
			$section_id,
			array(
				'title'       => $panel['title'],
				'description' => isset( $panel['description'] ) ? $panel['description'] : '',
				'panel'       => 'rad_content',
				'priority'    => $priority,
			)
		);
		$priority += 10;

		foreach ( $panel['text'] as $field ) {
			list( $key, $label ) = $field;
			$setting             = rad_mod_name( $key );
			$defaults            = rad_defaults( 'text' );

			$wp_customize->add_setting(
				$setting,
				array(
					'default'           => isset( $defaults[ $key ] ) ? $defaults[ $key ] : '',
					'sanitize_callback' => 'rad_sanitize_html',
					'transport'         => 'refresh',
				)
			);

			$wp_customize->add_control(
				$setting,
				array(
					'label'       => $label,
					'section'     => $section_id,
					'type'        => 'textarea',
					'description' => __( 'Basic formatting such as <em> and <br /> is allowed.', 'redcliffe-advisory' ),
				)
			);
		}

		if ( isset( $panel['extra'] ) ) {
			foreach ( $panel['extra'] as $field ) {
				$setting = rad_mod_name( $field['key'] );

				$wp_customize->add_setting(
					$setting,
					array(
						'default'           => $field['default'],
						'sanitize_callback' => 'email' === $field['type'] ? 'sanitize_email' : 'sanitize_text_field',
						'transport'         => 'refresh',
					)
				);

				$wp_customize->add_control(
					$setting,
					array(
						'label'       => $field['label'],
						'section'     => $section_id,
						'type'        => $field['type'],
						'description' => isset( $field['description'] ) ? $field['description'] : '',
					)
				);
			}
		}

		foreach ( $panel['images'] as $field ) {
			list( $key, $label ) = $field;
			$setting             = rad_mod_name( $key );

			$wp_customize->add_setting(
				$setting,
				array(
					'default'           => '',
					'sanitize_callback' => 'absint',
					'transport'         => 'refresh',
				)
			);

			$wp_customize->add_control(
				new WP_Customize_Media_Control(
					$wp_customize,
					$setting,
					array(
						'label'       => $label,
						'section'     => $section_id,
						'mime_type'   => 'image',
						'description' => __( 'Leave empty to keep the original photograph.', 'redcliffe-advisory' ),
					)
				)
			);
		}

		foreach ( $panel['toggles'] as $field ) {
			list( $key, $label ) = $field;
			$setting             = rad_mod_name( 'section.' . $key );

			$wp_customize->add_setting(
				$setting,
				array(
					'default'           => true,
					'sanitize_callback' => 'rad_sanitize_checkbox',
					'transport'         => 'refresh',
				)
			);

			$wp_customize->add_control(
				$setting,
				array(
					/* translators: %s: name of a section of the page. */
					'label'   => sprintf( __( 'Show: %s', 'redcliffe-advisory' ), $label ),
					'section' => $section_id,
					'type'    => 'checkbox',
				)
			);
		}
	}
}
add_action( 'customize_register', 'rad_customize_register' );
