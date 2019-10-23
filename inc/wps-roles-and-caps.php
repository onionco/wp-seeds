<?php
/**
 * WP Seeds 🌱
 *
 * Handle roles and caps.
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
 * Add custom user role
 *
 * @since 1.0.0
 * @return void
 */
function wps_custom_roles() {
	add_role( 'gardener', 'Gardener' );
}
add_action( 'init', 'wps_custom_roles' );

/**
 * Add custom user capabilities
 *
 * @since 1.0.0
 * @return void
 */
function wps_custom_caps() {
	$roles_caps = array(
		'administrator' => array(
			'can'    => array( 'edit_transaction', 'edit_transactions', 'edit_other_transactions', 'edit_private_transactions', 'edit_published_transactions', 'publish_transactions', 'read_transaction', 'read_private_transactions' ),
			'cannot' => array( 'delete_transaction', 'delete_transactions', 'delete_published_transactions', 'delete_private_transactions', 'delete_others_transactions' ),
		),
		'gardener'      => array(
			'can'    => array( 'edit_transaction', 'edit_transactions', 'edit_other_transactions', 'edit_private_transactions', 'edit_published_transactions', 'publish_transactions', 'read_transaction', 'read_private_transactions' ),
			'cannot' => array( 'delete_transaction', 'delete_transactions', 'delete_published_transactions', 'delete_private_transactions', 'delete_others_transactions' ),
		),
		'editor'        => array(
			'can'    => array( 'edit_transaction', 'edit_transactions', 'edit_other_transactions', 'edit_private_transactions', 'edit_published_transactions', 'publish_transactions', 'read_transaction', 'read_private_transactions' ),
			'cannot' => array( 'delete_transaction', 'delete_transactions', 'delete_published_transactions', 'delete_private_transactions', 'delete_others_transactions' ),
		),
		'author'        => array(
			'can'    => array( 'edit_transaction', 'edit_transactions', 'edit_other_transactions', 'edit_private_transactions', 'edit_published_transactions', 'publish_transactions', 'read_transaction', 'read_private_transactions' ),
			'cannot' => array( 'delete_transaction', 'delete_transactions', 'delete_published_transactions', 'delete_private_transactions', 'delete_others_transactions' ),
		),
		'contributor'   => array(
			'can'    => array( 'edit_transaction', 'edit_transactions', 'edit_other_transactions', 'edit_private_transactions', 'edit_published_transactions', 'publish_transactions', 'read_transaction', 'read_private_transactions' ),
			'cannot' => array( 'delete_transaction', 'delete_transactions', 'delete_published_transactions', 'delete_private_transactions', 'delete_others_transactions' ),
		),
		'subscriber'    => array(
			'can'    => array( 'edit_transaction', 'edit_transactions', 'edit_other_transactions', 'edit_private_transactions', 'edit_published_transactions', 'publish_transactions', 'read_transaction', 'read_private_transactions' ),
			'cannot' => array( 'delete_transaction', 'delete_transactions', 'delete_published_transactions', 'delete_private_transactions', 'delete_others_transactions' ),
		),
	);

	foreach ( $roles_caps as $k => $v ) {
		$role = get_role( $k );
		foreach ( $v['can'] as $cap ) {
			$role->add_cap( $cap );
		}
		foreach ( $v['cannot'] as $cap ) {
			$role->remove_cap( $cap );
		}
	}

}
add_action( 'init', 'wps_custom_caps' );

/**
 * Show only own transactions to regular users.
 *
 * @since 1.0.0
 * @param object $wp_query The WP_Query object to alter.
 * @return void
 */
function wps_show_only_own_transactions( $wp_query ) {
	if ( current_user_can( 'administrator' ) || current_user_can( 'gardener' ) ) {
		return;
	}

	if ( isset( $_SERVER['REQUEST_URI'] ) && strpos( sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ), '/wp-admin/edit.php?post_type=transaction' ) !== false ) {
		global $current_user;
		$wp_query->set( 'author', $current_user->id );
	}
}
add_filter( 'parse_query', 'wps_show_only_own_transactions' );

/**
 * Custom row actions
 *
 * @since 1.0.0
 * @param array $actions The original array with actions.
 * @return array $actions The updated array with actions.
 */
function wps_transaction_post_row_actions( $actions ) {
	if ( ! current_user_can( 'spread_seeds' ) && get_post_type() === 'transaction' ) {
		unset( $actions['edit'] );
		unset( $actions['view'] );
		unset( $actions['trash'] );
		unset( $actions['inline hide-if-no-js'] );
	}
	return $actions;
}
add_filter( 'post_row_actions', 'wps_transaction_post_row_actions', 10, 1 );
