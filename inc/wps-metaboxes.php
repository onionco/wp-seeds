<?php
/**
 * WP Seeds 🌱
 *
 * Create CPT transaction.
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
 * Register custom metabox
 *
 * @since 1.0.0
 * @return void
 */
function wps_custom_metabox() {
	$prefix = 'wps_';

	$cmb = new_cmb2_box(
		array(
			'id'           => $prefix . 'transaction',
			'title'        => esc_html__( 'Transaction', 'cmb2' ),
			'object_types' => array( 'transaction' ),
		)
	);

	$cmb->add_field(
		array(
			'name'             => esc_html__( 'Sender', 'cmb2' ),
			'description'      => esc_html__( 'Who will send the seeds?', 'cmb2' ),
			'id'               => $prefix . 'sender',
			'type'             => 'select',
			'attributes'       => array(
				'required' => 'required',
			),
			'show_option_none' => __( 'Please select', 'wp-seeds' ),
			'default'          => wps_sender_default(),
			'options'          => wps_sender_options(),
		)
	);

	$cmb->add_field(
		array(
			'name'             => esc_html__( 'Receiver', 'cmb2' ),
			'description'      => esc_html__( 'Who will receive the seeds?', 'cmb2' ),
			'id'               => $prefix . 'receiver',
			'type'             => 'select',
			'attributes'       => array(
				'required' => 'required',
			),
			'show_option_none' => __( 'Please select', 'wp-seeds' ),
			'default'          => wps_receiver_default(),
			'options'          => wps_receiver_options(),
		)
	);

	$cmb->add_field(
		array(
			'name'        => esc_html__( 'Amount', 'cmb2' ),
			'description' => esc_html__( 'How many seeds should be send?', 'cmb2' ),
			'id'          => $prefix . 'amount',
			'type'        => 'text_small',
			'attributes'  => array(
				'required' => 'required',
			),
			'default'     => wps_amount_default(),
		)
	);

	$cmb->add_field(
		array(
			'name'        => esc_html__( 'Note', 'cmb2' ),
			'description' => esc_html__( 'Here you can add a transaction note.', 'cmb2' ),
			'id'          => $prefix . 'note',
			'type'        => 'textarea_small',
			'default'     => wps_note_default(),
		)
	);
}
add_action( 'cmb2_admin_init', 'wps_custom_metabox' );

/**
 * Get users
 *
 * @since 1.0.0
 * @return array $users Array with all users.
 */
function wps_get_users() {
	$users = array();

	foreach ( get_users() as $user ) {
		$users[ $user->ID ] = $user->display_name;
	}

	return $users;
}

/**
 * Get senders
 *
 * @since 1.0.0
 * @return array $users Array with all senders.
 */
function wps_sender_options() {
	$users = wps_get_users();

	if ( ! current_user_can( 'spread_seeds' ) ) {
		$current_user = wp_get_current_user();
		return array( $current_user->ID => $current_user->display_name );
	}

	return $users;
}

/**
 * Get receivers
 *
 * @since 1.0.0
 * @return array $users Array with all receivers.
 */
function wps_receiver_options() {
	$users = wps_get_users();

	if ( ! current_user_can( 'spread_seeds' ) ) {
			unset( $users[ get_current_user_id() ] );
	}

	return $users;
}

/**
 * Get sender default value
 *
 * @since 1.0.0
 * @return int The provided user ID of the sender, if available, otherwise the current user ID.
 */
function wps_sender_default() {
	$user = get_userdata( get_current_user_id() );

	if ( isset( $_GET['sender'] ) && is_numeric( $_GET['sender'] ) ) {
		$result = get_userdata( (int) $_GET['sender'] );
		if ( $result ) {
			$user = $result;
		}
	}

	return $user->ID;
}

/**
 * Get receiver default value
 *
 * @since 1.0.0
 * @return int The provided user ID of the receiver, if available.
 */
function wps_receiver_default() {
	if ( isset( $_GET['sender'] ) && is_numeric( $_GET['sender'] ) ) {
		$result = get_userdata( (int) $_GET['sender'] );
		if ( $result ) {
			return $result->ID;
		}
	}
}

/**
 * Get amount default value
 *
 * @since 1.0.0
 * @return int The provided amount, if available.
 */
function wps_amount_default() {
	if ( isset( $_GET['amount'] ) && is_numeric( $_GET['amount'] ) ) {
		return (int) $_GET['amount'];
	}
}

/**
 * Get note default value
 *
 * @since 1.0.0
 * @return int The provided amount, if available.
 */
function wps_note_default() {
	if ( isset( $_GET['note'] ) ) {
		return sanitize_text_field( wp_unslash( $_GET['note'] ) );
	}
}
