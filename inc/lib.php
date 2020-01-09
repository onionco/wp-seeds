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

defined( 'ABSPATH' ) || exit;

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
	if ( ! $options ) {
		return;
	}

	foreach ( $options as $key => $label ) {
		printf(
			'<option value="%s" %s>%s</option>',
			esc_attr( $key ),
			( ( strval( $current ) === strval( $key ) ) ? 'selected' : '' ),
			esc_html( $label )
		);
	}
}

/**
 * Is this a $_REQUEST variable?
 *
 * @param string $name The variable name.
 *
 * @return boolean Weather or not the variable exists.
 */
function is_req_var( $name ) {
	return isset( $_REQUEST[ $name ] );
}

/**
 * Get and unslash $_REQUEST variable.
 * You can specify a default value that should be returned if the request
 * variable doesn't exist. If no default value is specified and the variable
 * doesn't exist, an exception will be thrown.
 *
 * @param string $name The variable name.
 * @param string $default The default value.
 * @throws Exception If the variable doesn't exist, and no default value is provided.
 * @return string The string value for the variable.
 */
function get_req_var( $name, $default = null ) {
	if ( ! isset( $_REQUEST[ $name ] ) ) {
		if ( null !== $default ) {
			return $default;
		}

		throw new Exception( 'Expected request variable: ' . $name );
	}

	return sanitize_text_field( wp_unslash( $_REQUEST[ $name ] ) );
}

/**
 * Process a form using the following scheme:
 *
 * - Check if the form is submitted, by checking if a request variable with
 *   the name specified in the submit_var exists.
 * - If the form is submitted, call the function specified by process_cb.
 * - The the processing function runs successfully, the form is considered
 *   to be successfuly submitted. Show success message and hide the form.
 * - If the processing function throws an error, use the message from the
 *   exception as error message. This exception can be a WPS_Form_Exception
 *   in shich a css style is outputted to highlight the offending field.
 *
 * Options:
 *
 *   submit_var       - The variable to use to check for form submission.
 *   success_message  - This message will be shown if the form could be
 *                      successfully processed.
 *   process_cb       - The function to call to process the form.
 *   form_class       - Specify the class for the form. This is used to hide
 *                      the form on successful submission.
 *   return_output    - If true, output of the function will be returned.
 *                      Otherwise, it will be outputted to the browser.
 *
 * @param array $options The form options.
 * @throws Exception If any options are missing.
 * @return string The output of the processing if return_output is specified, otherwise void.
 */
function wps_process_form( $options ) {
	if ( ! array_key_exists( 'success_message', $options ) ) {
		$options['success_message'] = __( 'Form Processed.', 'wp-seeds' );
	}

	if ( ! array_key_exists( 'submit_var', $options ) ) {
		$options['submit_var'] = 'submit';
	}

	if ( ! array_key_exists( 'process_cb', $options ) ) {
		throw new Exception( 'Need a process_cb to process the form.' );
	}

	if ( is_req_var( $options['submit_var'] ) ) {
		$vars = array();

		if ( array_key_exists( 'form_class', $options ) ) {
			$vars['form_css_selector'] = 'form.' . esc_html( $options['form_class'] );
		} else {
			$vars['form_css_selector'] = 'form';
		}

		try {
			call_user_func( $options['process_cb'] );
			$vars['success'] = $options['success_message'];
		} catch ( Exception $e ) {
			$vars['error'] = $e;
		}

		if ( array_key_exists( 'return_output', $options ) && $options['return_output'] ) {
			return render_template( __DIR__ . '/../tpl/wps-admin-form-result.tpl.php', $vars );
		} else {
			display_template( __DIR__ . '/../tpl/wps-admin-form-result.tpl.php', $vars );
		}
	}
}
