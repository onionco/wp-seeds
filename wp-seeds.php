<?php
/**
 * WP Seeds 🌱
 *
 * @package   wp-seeds
 * @link      https://github.com/limikael/wp-seeds
 * @author    Mikael Lindqvist & Niels Lange
 * @copyright 2019 Mikael Lindqvist & Niels Lange
 * @license   GPL v2 or later

 * Plugin Name:       WP Seeds 🌱
 * Plugin URI:        https://github.com/limikael/wp-seeds
 * Description:       Allows users to hold, send and receive tokens named seeds.
 * Version:           1.0
 * Requires at least: 5.2
 * Requires PHP:      7.3
 * Author:            Mikael Lindqvist & Niels Lange
 * Author URI:        https://github.com/limikael/wp-seeds
 * Text Domain:       wp-seeds
 * License:           GPL v2 or later
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 */

/**
 * Include required classes and files.
 *
 * @since 1.0.0
 */
require_once dirname( __FILE__ ) . '/assets/cmb2/init.php';
require_once dirname( __FILE__ ) . '/inc/lib.php';
require_once dirname( __FILE__ ) . '/inc/transaction.php';
require_once dirname( __FILE__ ) . '/inc/transactions-all.php';
require_once dirname( __FILE__ ) . '/inc/users-all.php';
require_once dirname( __FILE__ ) . '/inc/users-profile.php';
require_once dirname( __FILE__ ) . '/inc/wps-cpt-transaction.php';
require_once dirname( __FILE__ ) . '/inc/wps-metaboxes.php';
require_once dirname( __FILE__ ) . '/inc/wps-roles-and-caps.php';
require_once dirname( __FILE__ ) . '/inc/wps-shortcodes.php';

/**
 * Hide editor for transactions CPT
 *
 * @since 1.0.0
 * @return void
 */
function wps_hide_editor() {
	remove_post_type_support( 'transaction', 'title' );
	remove_post_type_support( 'transaction', 'editor' );
}
add_action( 'admin_init', 'wps_hide_editor' );

/**
 * Save transaction
 *
 * @since 1.0.0
 * @param int $post_id The post ID.
 * @return void
 */
function wps_save_transaction( $post_id ) {
	$post = get_post( $post_id );

	// Return if post status is auto-draft.
	if ( isset( $post->post_status ) && 'auto-draft' === $post->post_status ) {
		return;
	}

	// Return if post status is trash.
	if ( isset( $post->post_status ) && 'trash' === $post->post_status ) {
		return;
	}

	// Return when no transactiuon gets created.
	if ( ! isset( $_GET['create_transaction'] ) ) {
		return;
	}

	$errors = false;

	if ( wps_missing_sender() ) {
		wps_missing_sender_error();
		$errors = true;
	}

	if ( wps_missing_receiver() ) {
		wps_missing_receiver_error();
		$errors = true;
	}

	if ( wps_identical_sender_receiver() ) {
		wps_identical_sender_receiver_error();
		$errors = true;
	}

	if ( wps_missing_amount() ) {
		wps_missing_amount_error();
		$errors = true;
	}

	if ( wps_negative_amount() ) {
		wps_negative_amount_error();
		$errors = true;
	}

	if ( wps_zero_amount() ) {
		wps_zero_amount_error();
		$errors = true;
	}

	if ( wps_insufficient_balance() ) {
		wps_insufficient_balance_error();
		$errors = true;
	}

	if ( $errors ) {

		remove_action( 'save_post', 'wps_save_transaction' );
		$post->post_status = 'draft';
		wp_update_post( $post );
		add_action( 'save_post', 'wps_save_transaction' );
		add_filter( 'redirect_post_location', 'wps_transaction_redirect_filter' );

	} else {

		$amount = $_POST['wps_amount']; // phpcs:ignore

		// // Withdraw amount from sender.
		$sender_id          = $_POST['wps_sender']; // phpcs:ignore
		$sender_balance_old = get_user_meta( $sender_id, 'wps_balance', true );
		$sender_balance_new = (int) $sender_balance_old - (int) $amount;
		update_user_meta( $sender_id, 'wps_balance', $sender_balance_new );

		// // Send amount to receiver.
		$receiver_id          = $_POST['wps_receiver']; // phpcs:ignore
		$receiver_balance_old = get_user_meta( $receiver_id, 'wps_balance', true );
		$receiver_balance_new = (int) $receiver_balance_old + (int) $amount;
		update_user_meta( $receiver_id, 'wps_balance', $receiver_balance_new );

		// Prepare post title.
		$temp[]           = date( 'Y.m.d' );
		$temp[]           = $_POST['wps_sender']; 	// phpcs:ignore
		$temp[]           = $_POST['wps_receiver']; // phpcs:ignore
		$temp[]           = $_POST['wps_amount']; 	// phpcs:ignore
		$temp[]           = time();
		$post->post_title = crypt( implode( '', $temp ) );
	}
}
add_action( 'save_post', 'wps_save_transaction', 10, 1 );

/**
 * Redirect error message
 *
 * @since 1.0.0
 * @param object $location The original location object.
 * @return object $location The updated location object.
 */
function wps_transaction_redirect_filter( $location ) {
	remove_filter( 'redirect_post_location', __FUNCTION__, 99 );
	$location = add_query_arg( 'message', 99, $location );

	return $location;
}

/**
 * Check if sender is missing
 *
 * @since 1.0.0
 * @return bool Returns true if sender is missing and false otherwise.
 */
function wps_missing_sender() {
		return empty( $_POST['wps_sender'] ); // phpcs:ignore
}

/**
 * Check if receiver is missing
 *
 * @since 1.0.0
 * @return bool Returns true if receiver is missing and false otherwise.
 */
function wps_missing_receiver() {
	return empty( $_POST['wps_receiver'] ); // phpcs:ignore
}

/**
 * Check if sender and receiver are identical
 *
 * @since 1.0.0
 * @return bool Returns true if sender and receiver are identical and false otherwise.
 */
function wps_identical_sender_receiver() {
	return ! empty( $_POST['wps_sender'] ) // phpcs:ignore
			&& ! empty( $_POST['wps_receiver'] ) // phpcs:ignore
			&& $_POST['wps_sender'] === $_POST['wps_receiver']; // phpcs:ignore
}

/**
 * Check if amount is missing
 *
 * @since 1.0.0
 * @return bool Returns true if amount is missing and false otherwise.
 */
function wps_missing_amount() {
	return empty( $_POST['wps_amount'] ); // phpcs:ignore
}

/**
 * Check if amount is negative
 *
 * @since 1.0.0
 * @return bool Returns true if amount is negative and false otherwise.
 */
function wps_negative_amount() {
	return ! empty( $_POST['wps_amount'] ) && 0 > $_POST['wps_amount']; // phpcs:ignore
}

/**
 * Check if amount is zero
 *
 * @since 1.0.0
 * @return bool Returns true if amount is zero and false otherwise.
 */
function wps_zero_amount() {
	return ! empty( $_POST['wps_amount'] ) && 0 === $_POST['wps_amount']; // phpcs:ignore
}

/**
 * Check if balance is insufficient
 *
 * @since 1.0.0
 * @return bool Returns true if balance is insufficient and false otherwise.
 */
function wps_insufficient_balance() {
	if ( wps_missing_sender()
		|| wps_negative_amount()
		|| wps_zero_amount() ) {
		return;
	}

	$balance = get_user_meta( $_POST['wps_sender'], 'wps_balance', true ); // phpcs:ignore

	return ! empty( $_POST['wps_amount'] ) && $balance < $_POST['wps_amount']; // phpcs:ignore
}


/**
 * Prepare unpermitted update error
 *
 * @since 1.0.0
 * @return void
 */
function wps_unpermitted_update_error() {
	add_settings_error(
		'unpermitted_update',
		'unpermitted-update',
		__( 'Completed transactions cannot be updated.', 'wp-seeds' ),
		'warning'
	);

	set_transient( 'settings_errors', get_settings_errors(), 30 );
}

/**
 * Prepare missing sender error
 *
 * @since 1.0.0
 * @return void
 */
function wps_missing_sender_error() {
	add_settings_error(
		'missing_sender',
		'missing-sender',
		__( 'Please select a sender.', 'wp-seeds' ),
		'error'
	);

	set_transient( 'settings_errors', get_settings_errors(), 30 );
}

/**
 * Prepare missing receiver error
 *
 * @since 1.0.0
 * @return void
 */
function wps_missing_receiver_error() {
	add_settings_error(
		'missing_receiver',
		'missing-receiver',
		__( 'Please select a receiver.', 'wp-seeds' ),
		'error'
	);

	set_transient( 'settings_errors', get_settings_errors(), 30 );
}

/**
 * Prepare missing receiver error
 *
 * @since 1.0.0
 * @return void
 */
function wps_identical_sender_receiver_error() {
	add_settings_error(
		'identical_sender_receiver',
		'identical-sender-receiver',
		__( 'The sender and receiver cannot be identical.', 'wp-seeds' ),
		'error'
	);

	set_transient( 'settings_errors', get_settings_errors(), 30 );
}

/**
 * Prepare missing amount error
 *
 * @since 1.0.0
 * @return void
 */
function wps_missing_amount_error() {
	add_settings_error(
		'missing_amount',
		'missing-amount',
		__( 'You have not specified the amount of seeds to transfer.', 'wp-seeds' ),
		'error'
	);

	set_transient( 'settings_errors', get_settings_errors(), 30 );
}

/**
 * Prepare negative receiver error
 *
 * @since 1.0.0
 * @return void
 */
function wps_negative_amount_error() {
	add_settings_error(
		'negative_amount',
		'negative-amount',
		__( 'You cannot provide a negative amount of seeds to transfer.', 'wp-seeds' ),
		'error'
	);

	set_transient( 'settings_errors', get_settings_errors(), 30 );
}

/**
 * Prepare zero receiver error
 *
 * @since 1.0.0
 * @return void
 */
function wps_zero_amount_error() {
	add_settings_error(
		'zero_amount',
		'zero-amount',
		__( 'You cannot provide a zero amount of seeds to transfer.', 'wp-seeds' ),
		'error'
	);

	set_transient( 'settings_errors', get_settings_errors(), 30 );
}

/**
 * Prepare insufficient balance error
 *
 * @since 1.0.0
 * @return void
 */
function wps_insufficient_balance_error() {
	add_settings_error(
		'insufficient_balance',
		'insufficient-balance',
		__( 'You have an insufficient balance.', 'wp-seeds' ),
		'error'
	);

	set_transient( 'settings_errors', get_settings_errors(), 30 );
}

/**
 * Show admin notices if available
 *
 * @since 1.0.0
 * @return void
 */
function wps_transaction_admin_notices() {
	$errors = get_transient( 'settings_errors' );
	if ( ! $errors ) {
		return;
	}

	foreach ( $errors as $error ) {
		printf( '<div class="notice notice-%s is-dismissible"><p>%s</p></div>', esc_html( $error['type'] ), esc_html( $error['message'] ) );
	}

	delete_transient( 'settings_errors' );
	remove_action( 'admin_notices', 'wps_transaction_admin_notices' );
}
add_action( 'admin_notices', 'wps_transaction_admin_notices' );

/**
 * Load admin styles
 *
 * @since 1.0.0
 * @return void
 */
function wps_admin_style() {
	wp_enqueue_style( 'admin-styles', plugin_dir_url( __FILE__ ) . '/admin.css', null, '1.0', 'screen' );
}
add_action( 'admin_enqueue_scripts', 'wps_admin_style' );

/**
 * Admin menu hook, add options page.
 *
 * @since 1.0.0
 * @return void
 */
function wps_admin_menu() {
	add_submenu_page(
		'edit.php?post_type=transaction',
		'WP Seeds Request Transaction',
		'Request Transaction',
		'read',
		'wps_request_transaction',
		'wps_request_transaction_page'
	);
	add_submenu_page(
		'edit.php?post_type=transaction',
		'WP Seeds Settings',
		'Settings',
		'manage_options',
		'wps_settings',
		'wps_settings_page'
	);
}
add_action( 'admin_menu', 'wps_admin_menu' );

/**
 * WP Seeds request transaction page.
 *
 * @since 1.0.0
 * @return void
 */
function wps_request_transaction_page() {
	$vars    = array();
	$show_qr = false;

	if ( isset( $_REQUEST['do_request'] ) ) {
		if ( ! empty( $_REQUEST['amount'] ) ) {
			$to_user                = (int) get_current_user_id();
			$amount                 = (int) $_REQUEST['amount'];
			$vars['notice_success'] = __( 'QR had been created successfully. Please ask the sender to scan this QR code to transfer seeds to you.', 'wp-seeds' );
			$vars['qr_code_url']    = sprintf( '//wp.test/wp-admin/post-new.php?post_type=transaction&to_user=%d&ammount=%d', $to_user, $amount );
			$show_qr                = true;
		} else {
			$vars['notice_error'] = __( 'Please provide an ammount to request.', 'wp-seeds' );
		}
	}

	if ( $show_qr ) {
		display_template( dirname( __FILE__ ) . '/tpl/wps-request-transaction-code.tpl.php', $vars );
	} else {
		display_template( dirname( __FILE__ ) . '/tpl/wps-request-transaction-page.tpl.php', $vars );
	}
}

/**
 * WP Seeds settings page.
 *
 * @since 1.0.0
 * @return void
 */
function wps_settings_page() {
	$vars               = array();
	$vars['action_url'] = admin_url( 'edit.php?post_type=transaction&page=wps_settings' );
	$vars['users']      = array();
	foreach ( get_users() as $user ) {
		$vars['users'][ $user->ID ] = wps_transaction_format_user( $user );
	}

	$create_fv         = new WPS_Form_Validator();
	$vars['create_fv'] = $create_fv;
	$create_fv->check_wp_user_id( 'create_user_id' );
	$create_fv->check_positive_number( 'create_amount' );
	if ( $create_fv->is_valid_submission() ) {
		$post_id = wp_insert_post( array( 'post_type' => 'transaction' ) );
		update_post_meta( $post_id, 'amount', (int) $create_fv->get_checked( 'create_amount' ) );
		update_post_meta( $post_id, 'to_user', $create_fv->get_checked( 'create_user_id' ) );
		update_post_meta( $post_id, 'seeding_transaction', true );

		try {
			wps_process_transaction( $post_id );
			$create_fv->done( __( 'The seeds have been created.', 'wp-seeds' ) );
		} catch ( Exception $e ) {
			$create_fv->trigger( $e->getMessage() );
			wp_delete_post( $post_id, true );
		}
	}

	$burn_fv         = new WPS_Form_Validator();
	$vars['burn_fv'] = $burn_fv;
	$burn_fv->check_wp_user_id( 'burn_user_id' );
	$burn_fv->check_positive_number( 'burn_amount' );
	if ( $burn_fv->is_valid_submission() ) {
		$post_id = wp_insert_post( array( 'post_type' => 'transaction' ) );
		update_post_meta( $post_id, 'amount', (int) $burn_fv->get_checked( 'burn_amount' ) );
		update_post_meta( $post_id, 'from_user', $burn_fv->get_checked( 'burn_user_id' ) );
		update_post_meta( $post_id, 'seeding_transaction', true );

		try {
			wps_process_transaction( $post_id );
			$burn_fv->done( __( 'The seeds have been burned.', 'wp-seeds' ) );
		} catch ( Exception $e ) {
			$burn_fv->trigger( $e->getMessage() );
			wp_delete_post( $post_id, true );
		}
	}

	display_template( __DIR__ . '/tpl/wps-settings-page.tpl.php', $vars );
}
