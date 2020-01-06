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
 * Show transaction history for the current user.
 *
 * @param array $args The shortcode args.
 * @return string The rendered shortcode.
 */
function wps_history_sc( $args ) {
	$user = wp_get_current_user();
	if ( ! $user || ! $user->ID ) {
		return __( 'You need to be logged in to view this page', 'wp-seeds' );
	}

	$user_display_by_id = wps_user_display_by_id();
	$vars = array();
	$vars['transactions'] = array();
	$transactions         = Transaction::findAllByQuery(
		'SELECT * ' .
		'FROM   :table ' .
		'WHERE  sender=%s ' .
		'OR     receiver=%s',
		$user->ID,
		$user->ID
	);
	foreach ( $transactions as $transaction ) {
		$view = array();
		$view['id'] = $transaction->transaction_id;

		if ( $user->ID == $transaction->sender ) {
			// If we are the sender, show amount as negative, and the
			// receiver in the to/from field...
			$view['amount'] = -$transaction->amount;
			$view['user'] = $user_display_by_id[ $transaction->receiver ];
		} else {
			// ...otherwise, we are the receiver, so show the amount as
			// positive and the sender in the to/from field.
			$view['amount'] = $transaction->amount;
			$view['user'] = $user_display_by_id[ $transaction->sender ];
		}

		$vars['transactions'][] = $view;
	}
	return render_template( __DIR__ . '/../tpl/wps-history.tpl.php', $vars );
}
add_shortcode( 'seeds_history', 'wps_history_sc' );
