<?php
/**
 * WP Seeds 🌱
 *
 * Custom functionality for transactions overview page.
 *
 * @package   wp-seeds/inc
 * @link      https://github.com/limikael/wp-seeds
 * @author    Mikael Lindqvist & Niels Lange
 * @copyright 2019 Mikael Lindqvist & Niels Lange
 * @license   GPL v2 or later
 */

/**
 * Exception to be thrown in form validation.
 */
class WPS_Form_Exception extends Exception {

	/**
	 * Constructor.
	 *
	 * @param string $message The message for the exception.
	 * @param string $field The field that caused the problem.
	 */
	public function __construct( $message, $field ) {
		parent::__construct( $message );
		$this->field = $field;
	}
}
