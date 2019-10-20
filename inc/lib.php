<?php
/**
 * WP Seeds 🌱
 *
 * @package   wp-seeds/inc
 * @link      https://github.com/limikael/wp-seeds
 * @author    Mikael Lindqvist & Niels Lange
 * @copyright 2019 Mikael Lindqvist & Niels Lange
 * @license   GPL v2 or later
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Display a template by making all data in the $vars
 * array available in the global scope as seen by the
 * template file.
 *
 * @param string $fn The file name of the file containing the template.
 * @param array  $vars The variable to make available to the template.
 *
 * @return void
 */
function display_template( $fn, $vars = array() ) {
	foreach ( $vars as $key => $value ) {
		$$key = $value;
	}

	require $fn;
}

/**
 * The functionality is similar to display_template, but the
 * result is returned rather than output to the browser.
 *
 * @param string $fn The file name of the file containing the template.
 * @param array  $vars The variable to make available to the template.
 *
 * @return string
 */
function render_template( $fn, $vars = array() ) {
	ob_start();
	display_template( $fn, $vars );
	return ob_get_clean();
}

/**
 * Render select options.
 *
 * @param array $options Key=>value pairs of options and labels.
 * @param mixed $current The option that should be currently selected.
 *
 * @return void
 */
function display_select_options( $options, $current = null ) {
	foreach ( $options as $key => $label ) {
		printf(
			'<option value="%s" %s>%s</option>',
			esc_attr( $key ),
			( ( $current === $key ) ? 'selected' : '' ),
			esc_html( $label )
		);
	}
}

/**
 * Display notices.
 *
 * @param array $notices The notices to display.
 *
 * @return void
 */
function display_notices( $notices ) {
	foreach ( $notices as $notice_class => $class_notices ) {
		foreach ( $class_notices as $notice_text ) {
			printf(
				'<div class="notice notice-%s">',
				esc_attr( $notice_class )
			);

			printf(
				'<p><strong>%s</strong></p>',
				esc_html( $notice_text )
			);

			echo '</div>';
		}
	}
}
