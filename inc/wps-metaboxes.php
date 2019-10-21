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
			'options'          => wps_get_senders(),
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
			'options'          => wps_get_receivers(),
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
		)
	);

	$cmb->add_field(
		array(
			'name'        => esc_html__( 'Note', 'cmb2' ),
			'description' => esc_html__( 'Here you can add a transaction note.', 'cmb2' ),
			'id'          => $prefix . 'note',
			'type'        => 'textarea_small',
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
function wps_get_senders() {
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
function wps_get_receivers() {
	$users = wps_get_users();

	if ( ! current_user_can( 'spread_seeds' ) ) {
			unset( $users[ get_current_user_id() ] );
	}

	return $users;
}
