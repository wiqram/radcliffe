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
	require_once get_theme_file_path( 'inc/customizer-list-control.php' );

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
			$auto                = ! empty( $field[2] ); // Found by the build, not chosen by hand.

			$wp_customize->add_setting(
				$setting,
				array(
					'default'           => isset( $defaults[ $key ] ) ? $defaults[ $key ] : '',
					'sanitize_callback' => 'rad_sanitize_html',
					'transport'         => 'postMessage',
				)
			);

			$wp_customize->add_control(
				$setting,
				array(
					'label'       => $label,
					'section'     => $section_id,
					'type'        => 'textarea',
					'description' => $auto ? '' : __( 'Basic formatting such as <em> and <br /> is allowed.', 'redcliffe-advisory' ),
				)
			);

			// The pencil icon in the preview: click the words on the page and
			// the matching field opens. Changes show without a page reload.
			if ( isset( $wp_customize->selective_refresh ) ) {
				$wp_customize->selective_refresh->add_partial(
					$setting,
					array(
						'selector'            => '[data-rad="' . $key . '"]',
						'container_inclusive' => false,
						'fallback_refresh'    => true,
						'render_callback'     => function () use ( $key ) {
							return wp_kses_post( rad_get_html( $key ) );
						},
					)
				);
			}
		}

		if ( ! empty( $panel['lists'] ) ) {
			foreach ( $panel['lists'] as $field ) {
				list( $key, $label, $columns ) = $field;
				$setting                       = rad_mod_name( $key );

				$wp_customize->add_setting(
					$setting,
					array(
						'default'           => rad_list_default_text( $key ),
						'sanitize_callback' => 'rad_sanitize_list',
						'transport'         => 'postMessage',
					)
				);

				$wp_customize->add_control(
					new RAD_List_Control(
						$wp_customize,
						$setting,
						array(
							'label'       => $label,
							'section'     => $section_id,
							'list_key'    => $key,
							'columns'     => $columns,
							'description' => __( 'Each line is one row of the list. Change the words in the boxes, use Remove to take a row away, the arrows to move it, and Add a row for a new one. A box can be left empty. Click Publish when you are happy.', 'redcliffe-advisory' ),
						)
					)
				);

				// A pencil on the list; the rows redraw without a page reload.
				if ( isset( $wp_customize->selective_refresh ) ) {
					$wp_customize->selective_refresh->add_partial(
						$setting,
						array(
							'selector'            => '[data-rad-list="' . $key . '"]',
							'container_inclusive' => false,
							'fallback_refresh'    => true,
							'render_callback'     => function () use ( $key ) {
								return rad_get_list_html( $key );
							},
						)
					);
				}
			}
		}

		if ( isset( $panel['extra'] ) ) {
			foreach ( $panel['extra'] as $field ) {
				$setting = rad_mod_name( $field['key'] );

				$wp_customize->add_setting(
					$setting,
					array(
						'default'           => $field['default'],
						'sanitize_callback' => 'email' === $field['type'] ? 'sanitize_email' : ( 'url' === $field['type'] ? 'esc_url_raw' : 'sanitize_text_field' ),
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
					'transport'         => 'postMessage',
				)
			);

			// A pencil on the photograph itself. The whole page reloads after a
			// change (fallback_refresh), which keeps the picture's sizes right.
			if ( isset( $wp_customize->selective_refresh ) ) {
				$wp_customize->selective_refresh->add_partial(
					$setting,
					array(
						'selector'            => '[data-rad-img="' . $key . '"]',
						'container_inclusive' => true,
						'fallback_refresh'    => true,
						'render_callback'     => '__return_false',
					)
				);
			}

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

/**
 * Pencils on the photographs inside the Customizer preview.
 */
function rad_customize_preview_scripts() {
	wp_enqueue_script(
		'rad-customize-preview',
		get_theme_file_uri( 'assets/js/customize-preview.js' ),
		array( 'customize-preview', 'jquery' ),
		RAD_VERSION,
		true
	);
}
add_action( 'customize_preview_init', 'rad_customize_preview_scripts' );

/**
 * The row-by-row list editor, in the Customizer's own pane.
 */
function rad_customize_controls_scripts() {
	wp_enqueue_script(
		'rad-customize-controls',
		get_theme_file_uri( 'assets/js/customize-controls.js' ),
		array( 'customize-controls', 'jquery' ),
		RAD_VERSION,
		true
	);

	$css = '
		.rad-list-rows { margin: 8px 0 0; padding: 0; list-style: none; }
		.rad-list-row { display: flex; flex-wrap: wrap; align-items: center; gap: 4px; padding: 8px 0; border-bottom: 1px solid #dcdcde; }
		.rad-list-cells { display: flex; flex-direction: column; gap: 4px; flex: 1 1 100%; }
		.rad-list-cell input[type="text"] { width: 100%; }
		.rad-list-actions { display: flex; align-items: center; gap: 4px; margin-left: auto; }
		.rad-list-actions .button { min-width: 30px; padding: 0 6px; line-height: 26px; height: 28px; }
		.rad-list-actions .rad-list-remove { margin-left: 4px; }
		.rad-list-buttons { display: flex; align-items: center; gap: 12px; margin: 10px 0 0; }
	';
	wp_add_inline_style( 'customize-controls', $css );
}
add_action( 'customize_controls_enqueue_scripts', 'rad_customize_controls_scripts' );
