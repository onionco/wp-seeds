<?php
/**
 * WPS Validation
 *
 * Custom validation functions.
 *
 * @package WordPress
 * @subpackage WP Seeds
 * @since 1.0.0
 */

if ( ! class_exists( 'WPS_Validation' ) ) {

	/**
	 * Undocumented class
	 */
	class WPS_Validation {

		/**
		 * Check if number is negative
		 *
		 * @param int $data The number to validate.
		 * @return bool
		 */
		public static function is_negative( $data ) {
			return $data < 0 ? true : false;
		}

		/**
		 * Check if balance is sufficient
		 *
		 * @param int $amount The amount to transfer.
		 * @param int $balance The balance to validate.
		 * @return bool
		 */
		public static function is_insufficient_balance( $amount, $balance ) {
			return $amount > $balance ? true : false;
		}
	}
}
