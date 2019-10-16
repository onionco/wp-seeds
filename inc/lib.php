<?php
/**
 * Display a template by making all data in the $vars
 * array available in the global scope as seen by the
 * template file.
 *
 * @return void
 */
function display_template( $fn, $vars = array() ) {
	foreach ( $vars as $key => $value )
		$$key = $value;

	require $fn;
}

/**
 * The functionality is similar to display_template, but the
 * result is returned rather than output to the browser.
 *
 * @return string
 */
function render_template( $fn, $vars = array() ) {
	ob_start();
	display_template( $fn, $vars );
	return ob_get_clean();
}
