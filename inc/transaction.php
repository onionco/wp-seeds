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
 * Format a user as nicename (email).
 *
 * @param mixed $user A WordPress user object.
 *
 * @return string
 */
function wps_transaction_format_user( $user ) {
	return $user->data->user_nicename . ' (' . $user->data->user_email . ')';
}

/**
 * Perform a transaction by decreasing the sender balance and increase the
 * receiver balance. See this as a double check, all checks should have been
 * performed before calling this function. This function does not deal with
 * showing error messages, that is up to the caller. If it can't be performed,
 * throws an exception. For creation and burning transactions, from_user or
 * to_user should be null, also the meta field seeding_transaction should be
 * true.
 *
 * @param int $post_id The post id of the transaction to perform.
 * @return void
 * @throws Exception If the transaction is invalid in any way.
 */
function wps_process_transaction( $post_id ) {
	$post = get_post( $post_id );

	if ( 'transaction' !== get_post_type( $post_id ) ) {
		throw new Exception( 'This is not a transaction post.' );
	}

	// Prepare variables.
	$amount = (int) get_field( 'amount', $post_id );
	if ( $amount <= 0 ) {
		throw new Exception( 'Zero or negative transaction amount.' );
	}

	// Withdraw amount from sender.
	$sender_id          = get_field( 'from_user', $post_id );
	$sender_balance_old = get_user_meta( $sender_id, 'wps_balance', true );
	$sender_balance_new = (int) $sender_balance_old - (int) $amount;
	if ( $amount < 0 ) {
		throw new Exception( 'Insufficient funds on sender account.' );
	}
	update_user_meta( $sender_id, 'wps_balance', $sender_balance_new );

	// Send amount to receiver.
	$receiver_id          = get_field( 'to_user', $post_id );
	$receiver_balance_old = get_user_meta( $receiver_id, 'wps_balance', true );
	$receiver_balance_new = (int) $receiver_balance_old + (int) $amount;
	update_user_meta( $receiver_id, 'wps_balance', $receiver_balance_new );

	// Prepare post title.
	$temp[]           = date( 'Y.m.d' );
	$temp[]           = get_field( 'from_user' );
	$temp[]           = get_field( 'to_user' );
	$temp[]           = get_field( 'amount' );
	$temp[]           = time();
	$post->post_title = crypt( implode( '', $temp ) );

	// Set post status.
	$post->post_status = 'publish';

	// Save the post.
	wp_update_post( $post );
}
