<?php
/**
 * WP Seeds 🌱
 *
 * @package   wp-seeds
 * @link      https://github.com/limikael/wp-seeds
 * @author    Mikael Lindqvist & Niels Lange
 * @copyright 2019 Mikael Lindqvist & Niels Lange
 * @license   GPL v2 or later
 */

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
