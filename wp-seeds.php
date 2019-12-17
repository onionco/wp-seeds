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
require_once plugin_dir_path(__FILE__) . '/models/Transaction.php';
require_once plugin_dir_path(__FILE__) . '/inc/ConcreteListTable.php';
require_once plugin_dir_path( __FILE__ ) . '/inc/lib.php';
/*require_once dirname( __FILE__ ) . '/inc/transaction.php';
require_once dirname( __FILE__ ) . '/inc/transactions-all.php';
require_once dirname( __FILE__ ) . '/inc/users-all.php';
require_once dirname( __FILE__ ) . '/inc/users-profile.php';
require_once dirname( __FILE__ ) . '/inc/wps-cpt-transaction.php';
require_once dirname( __FILE__ ) . '/inc/wps-metaboxes.php';
require_once dirname( __FILE__ ) . '/inc/wps-roles-and-caps.php';
require_once dirname( __FILE__ ) . '/inc/wps-shortcodes.php';*/

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

function seeds_transactions_page() {
	$userById = array();
	$userdisplayById = array();
	foreach ( get_users() as $user ) {
		$userById[ $user->ID ] = $user;
		$userdisplayById[ $user->ID ] = 
			$user->data->user_nicename . ' (' . $user->data->user_email . ')';
	}

	$table=new ConcreteListTable();

	$table->addFilter(array(
		'key'      => 'account',
		'options'  => $userdisplayById,
		'allLabel' => 'All Accounts',
	));

	$table->addColumn(array(
		"title"=>"Time",
		"field"=>"timestamp",
		"sortable"=>true
	));

	$table->addColumn(array(
		"title"=>"Transaction ID",
		"field"=>"id"
	));

	$table->addColumn(array(
		"title"=>"From Account",
		"field"=>"fromAccount"
	));

	$table->addColumn(array(
		"title"=>"To Account",
		"field"=>"toAccount"
	));

	$table->addColumn(array(
		"title"=>"Amount",
		"field"=>"amount",
		"sortable"=>true
	));

	if (isset($_REQUEST["orderby"])) {
		$order=esc_sql($_REQUEST["orderby"])." ".esc_sql($_REQUEST["order"]);
	}

	else {
		$order='timestamp desc';
	}

	if ( isset( $_REQUEST['account'] ) && $_REQUEST['account'] ) {
		$transactions = Transaction::findAllByQuery(
			'SELECT   * ' .
			'FROM     :table ' .
			'WHERE    sender=%s ' .
			"OR       receiver=%s ".
			"ORDER BY $order",
			$_REQUEST['account'],
			$_REQUEST['account']
		);
	} else {
		$transactions = Transaction::findAllByQuery(
			'SELECT   * '.
			'FROM      :table '.
			"ORDER BY  $order"
		);
	}

	foreach ( $transactions as $transaction ) {
		$fromUser = $userById[ $transaction->sender ];
		$toUser   = $userById[ $transaction->receiver ];
		$link = get_admin_url( null, 'admin.php?page=seeds_transactions&transaction_detail=' . $transaction->id );
		$transactionViews[] = array(
			'id'          => "<a href='$link'>" . $transaction->transaction_id . '</a>',
			'fromAccount' => $fromUser->data->user_nicename . ' (' . $fromUser->data->user_email . ')',
			'toAccount'   => $toUser->data->user_nicename . ' (' . $toUser->data->user_email . ')',
			'amount'      => $transaction->amount,
			'timestamp'   => date("Y-m-d H:m:s",$transaction->timestamp)
		);
	}
	$table->setTitle("Transactions");
	$table->setData( $transactionViews );

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
		NULL,
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

function wps_register_transaction_form() {
	/**
	 * Registers options page menu item and form.
	 */
	$cmb_group=new_cmb2_box( array(
		'id'           => 'create_transaction',
		'title'        => esc_html__( 'Create Transaction', 'cmb2' ),
		'object_types' => array( 'options-page' ),
		'option_key'      => 'seeds_accounts',
		'parent_slug'     => 'admin.php', 
		'save_button'     => esc_html__( 'Create Transaction', 'cmb2' )
	) );

	$users=array();
	foreach (get_users() as $wpuser)
		$users[$wpuser->ID]=$wpuser->display_name;

	$cmb_group->add_field(array(
			'name'             => esc_html__( 'Sender', 'cmb2' ),
			'description'      => esc_html__( 'Who will send the seeds?', 'cmb2' ),
			'id'               => 'sender',		
			'type'             => 'select',
			'attributes'       => array(
				'required' => 'required',
			),
			'show_option_none' => __( 'Please select', 'wp-seeds' ),
			'options'          => $users,
	));

	$cmb_group->add_field(array(
			'name'             => esc_html__( 'Receiver', 'cmb2' ),
			'description'      => esc_html__( 'Who will send the seeds?', 'cmb2' ),
			'id'               => 'receiver',
			'type'             => 'select',
			'attributes'       => array(
				'required' => 'required',
			),
			'show_option_none' => __( 'Please select', 'wp-seeds' ),
			'options'          => $users,
	));

	$cmb_group->add_field(array(
		'name'    => esc_html__( 'Amount', 'cmb2' ),
		'desc'    => esc_html__( 'What is the amount for the transaction?', 'cmb2' ),
		'id'      => 'amount',
		'type'    => 'text_money',
		'default' => '123123'
	));
}
add_action( 'cmb2_admin_init', 'wps_register_transaction_form' );

function wps_handle_save_transaction() {
	$t = new Transaction();
	$t->sender = $_REQUEST["sender"];
	$t->receiver = $_REQUEST["receiver"];
	$t->amount = $_REQUEST["amount"];
	$t->timestamp = time();
	$t->save();
}
add_action("cmb2_save_options-page_fields_create_transaction","wps_handle_save_transaction");

function wps_activate() {
	Transaction::install();
}
register_activation_hook(__FILE__,'wps_activate');

function wps_deactivate() {
	Transaction::uninstall();
}
register_deactivation_hook(__FILE__,'wps_deactivate');

/**
 * Get the url where to find cmb2 resources. We hook into this function
 * because the original implementation of cmb2 fails if the plugin is inside
 * a sym-linked directory.
 *
 * @since 1.0.0
 * @return string
 */
function wps_cmb2_meta_box_url($url) {
	$new_url=trailingslashit(plugin_dir_url(__FILE__).'ext/cmb2');

	return $new_url;
}
add_filter('cmb2_meta_box_url','wps_cmb2_meta_box_url');