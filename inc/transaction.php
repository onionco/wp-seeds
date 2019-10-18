<?php
/**
 * WP Seeds 🌱
 *
 * @package   wp-seeds
 * @link      https://github.com/onionco/wp-seeds
 * @author    Mikael Lindqvist & Niels Lange
 * @copyright 2019 Mikael Lindqvist & Niels Lange
 * @license   GPL v2 or later
 */

/**
 * Format a user as nicename (email).
 *
 * @param mixed $user A WordPress user object.
 *
 * @return string
 */
function wps_transaction_format_user( $user ) {
	return $user->data->user_nicename . ' (' . $user->data->user_email . ')';
}
