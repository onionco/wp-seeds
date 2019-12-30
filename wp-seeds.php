<?php
/**
 * WP Seeds 🌱
 *
 * @package   wp-seeds
 * @link      https://github.com/limikael/wp-seeds
 * @author    Mikael Lindqvist & Niels Lange
 * @copyright 2019 Mikael Lindqvist & Niels Lange
 * @license   GPL v2 or later

 * Plugin Name:       WP Seeds
 * Plugin URI:        https://github.com/limikael/wp-seeds
 * Description:       Allows users to hold, send and receive tokens named seeds.
 * Version:           1.0
 * Requires at least: 5.2
 * Requires PHP:      7.2
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
require_once plugin_dir_path( __FILE__ ) . '/ext/cmb2/init.php';
require_once plugin_dir_path( __FILE__ ) . '/models/class-transaction.php';
require_once plugin_dir_path( __FILE__ ) . '/inc/class-custom-list-table.php';
require_once plugin_dir_path( __FILE__ ) . '/inc/lib.php';
require_once plugin_dir_path( __FILE__ ) . '/inc/class-cmb2-custom-handler.php';

/*
Old stuff

require_once dirname( __FILE__ ) . '/inc/transaction.php';
require_once dirname( __FILE__ ) . '/inc/transactions-all.php';
require_once dirname( __FILE__ ) . '/inc/users-all.php';
require_once dirname( __FILE__ ) . '/inc/users-profile.php';
require_once dirname( __FILE__ ) . '/inc/wps-cpt-transaction.php';
require_once dirname( __FILE__ ) . '/inc/wps-metaboxes.php';
require_once dirname( __FILE__ ) . '/inc/wps-roles-and-caps.php';
require_once dirname( __FILE__ ) . '/inc/wps-shortcodes.php';
*/

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
 * Show the list of transactions.
 *
 * @return void
 */
function seeds_transactions_page() {
	$user_by_id        = array();
	$userdisplay_by_id = array();
	foreach ( get_users() as $user ) {
		$user_by_id[ $user->ID ]        = $user;
		$userdisplay_by_id[ $user->ID ] =
			$user->data->user_nicename . ' (' . $user->data->user_email . ')';
	}

	$table = new Custom_List_Table();

	$table->add_filter(
		array(
			'key'      => 'account',
			'options'  => $userdisplay_by_id,
			'allLabel' => 'All Accounts',
		)
	);

	$table->add_column(
		array(
			'title'    => 'Time',
			'field'    => 'timestamp',
			'sortable' => true,
		)
	);

	$table->add_column(
		array(
			'title' => 'Transaction ID',
			'field' => 'id',
		)
	);

	$table->add_column(
		array(
			'title' => 'From Account',
			'field' => 'fromAccount',
		)
	);

	$table->add_column(
		array(
			'title' => 'To Account',
			'field' => 'toAccount',
		)
	);

	$table->add_column(
		array(
			'title'    => 'Amount',
			'field'    => 'amount',
			'sortable' => true,
		)
	);

	if ( is_req_var( 'orderby' ) ) {
		$order = esc_sql( get_req_str( 'orderby' ) ) . ' ' . esc_sql( get_req_str( 'order' ) );
	} else {
		$order = 'timestamp desc';
	}

	if ( is_req_var( 'account' ) && get_req_str( 'account' ) ) {
		$transactions = Transaction::findAllByQuery(
			'SELECT   * ' .
			'FROM     :table ' .
			'WHERE    sender=%s ' .
			'OR       receiver=%s ' .
			'ORDER BY ' . $order,
			get_req_str( 'account' ),
			get_req_str( 'account' )
		);
	} else {
		$transactions = Transaction::findAllByQuery(
			'SELECT   * ' .
			'FROM      :table ' .
			"ORDER BY  $order"
		);
	}

	$transaction_views = array();
	foreach ( $transactions as $transaction ) {
		$from_user          = $user_by_id[ $transaction->sender ];
		$to_user            = $user_by_id[ $transaction->receiver ];
		$link               = get_admin_url( null, 'admin.php?page=seeds_transactions&transaction_detail=' . $transaction->id );
		$transaction_views[] = array(
			'id'          => "<a href='$link'>" . $transaction->transaction_id . '</a>',
			'fromAccount' => $userdisplay_by_id[ $transaction->sender ],
			'toAccount'   => $userdisplay_by_id[ $transaction->receiver ],
			'amount'      => $transaction->amount,
			'timestamp'   => date( 'Y-m-d H:m:s', $transaction->timestamp ),
		);
	}
	$table->set_title( 'Transactions' );
	$table->set_data( $transaction_views );

	$table->display();
}

/**
 * Admin menu hook, add options page.
 *
 * @since 1.0.0
 * @return void
 */
function wps_admin_menu() {
	add_menu_page(
		'Seeds',
		'Seeds',
		'manage_options',
		'seeds_accounts',
		null,
		'dashicons-money',
		71
	);
	add_submenu_page(
		'seeds_accounts',
		'Seed Accounts',
		'New Transaction',
		'manage_options',
		'seeds_accounts'
	);
	add_submenu_page(
		'seeds_accounts',
		'Seed Transactions',
		'Transactions',
		'manage_options',
		'seeds_transactions',
		'seeds_transactions_page'
	);
}
add_action( 'admin_menu', 'wps_admin_menu' );

/**
 * Save the transaction.
 */
function wps_handle_save_transaction() {
	$t = new Transaction();
	$t->sender    = get_req_str( 'sender' );
	$t->receiver  = get_req_str( 'receiver' );
	$t->amount    = get_req_str( 'amount' );
	$t->timestamp = time();
	$t->perform();
}

/**
 * Create the transaction form.
 *
 * @return void
 */
function wps_register_transaction_form() {
	$cmb_group = new_cmb2_box(
		array(
			'id'           => 'create_transaction',
			'title'        => esc_html__( 'Create Transaction', 'wp-seeds' ),
			'object_types' => array( 'options-page' ),
			'option_key'   => 'seeds_accounts',
			'parent_slug'  => 'admin.php',
			'save_button'  => esc_html__( 'Create Transaction', 'wp-seeds' ),
			'save_cb'      => 'wps_handle_save_transaction',
			'save_message' => esc_html__( 'Transaction Created', 'wp-seeds' ),
		)
	);

	CMB2_Custom_Handler::hookup( $cmb_group );

	$users = array();
	foreach ( get_users() as $wpuser ) {
		$users[ $wpuser->ID ] = $wpuser->display_name;
	}

	$cmb_group->add_field(
		array(
			'name'             => esc_html__( 'Sender', 'wp-seeds' ),
			'description'      => esc_html__( 'Who will send the seeds?', 'wp-seeds' ),
			'id'               => 'sender',
			'type'             => 'select',
			'show_option_none' => __( 'Please select', 'wp-seeds' ),
			'options'          => $users,
		)
	);

	$cmb_group->add_field(
		array(
			'name'             => esc_html__( 'Receiver', 'seeds' ),
			'description'      => esc_html__( 'Who will send the seeds?', 'seeds' ),
			'id'               => 'receiver',
			'type'             => 'select',
			'show_option_none' => __( 'Please select', 'wp-seeds' ),
			'options'          => $users,
		)
	);

	$cmb_group->add_field(
		array(
			'name'    => esc_html__( 'Amount', 'cmb2' ),
			'desc'    => esc_html__( 'What is the amount for the transaction?', 'cmb2' ),
			'id'      => 'amount',
			'type'    => 'text_money',
			'attributes' => array(
				'autocomplete' => 'off',
			),
		)
	);
}
add_action( 'cmb2_admin_init', 'wps_register_transaction_form' );

/**
 * Handle plugin activation.
 *
 * @return void
 */
function wps_activate() {
	Transaction::install();
}
register_activation_hook( __FILE__, 'wps_activate' );

/**
 * Handle plugin deactivation.
 *
 * @return void
 */
function wps_deactivate() {
	Transaction::uninstall();
}
register_deactivation_hook( __FILE__, 'wps_deactivate' );

/**
 * Get the url where to find cmb2 resources. We hook into this function
 * because the original implementation of cmb2 fails if the plugin is inside
 * a sym-linked directory.
 *
 * @param string $url The auto generated url.
 * @since 1.0.0
 * @return string
 */
function wps_cmb2_meta_box_url( $url ) {
	$new_url = trailingslashit( plugin_dir_url( __FILE__ ) . 'ext/cmb2' );

	return $new_url;
}
add_filter( 'cmb2_meta_box_url', 'wps_cmb2_meta_box_url' );
