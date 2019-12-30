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
require_once plugin_dir_path( __FILE__ ) . '/inc/class-cmb2-form-exception.php';

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
 * Uniform display of users.
 * Returns an array with the user id as key and the display as value.
 *
 * @return array All users on the site.
 */
function wps_user_display_by_id() {
	$users = array();

	foreach ( get_users() as $wpuser ) {
		$users[ $wpuser->ID ] = $wpuser->data->user_nicename . ' (' . $wpuser->data->user_email . ')';
	}

	return $users;
}

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
function wps_transactions_page() {
	$table = new Custom_List_Table();

	$table->add_filter(
		array(
			'key'      => 'account',
			'options'  => wps_user_display_by_id(),
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

	$user_display_by_id = wps_user_display_by_id();
	$transaction_views = array();
	foreach ( $transactions as $transaction ) {
		$link               = get_admin_url( null, 'admin.php?page=seeds_transactions&transaction_detail=' . $transaction->id );
		$transaction_views[] = array(
			'id'          => "<a href='$link'>" . $transaction->transaction_id . '</a>',
			'fromAccount' => $user_display_by_id[ $transaction->sender ],
			'toAccount'   => $user_display_by_id[ $transaction->receiver ],
			'amount'      => $transaction->amount,
			'timestamp'   => date( 'Y-m-d H:m:s', $transaction->timestamp ),
		);
	}
	$table->set_title( 'Transactions' );
	$table->set_data( $transaction_views );

	$table->display();
}

/**
 * Handle burning of seeds.
 *
 * @return void
 * @throws CMB2_Form_Exception If there is an error.
 */
function wps_burn_seeds_save() {
	$user = get_user_by( 'id', get_req_str( 'sender' ) );
	if ( ! $user ) {
		throw new CMB2_Form_Exception( 'Please select a user to send the burned seeds.', 'sender' );
	}

	$amount = intval( get_req_str( 'amount' ) );
	if ( $amount <= 0 ) {
		throw new CMB2_Form_Exception( 'Amount needs to be greater than zero', 'amount' );
	}

	$balance = intval( get_user_meta( $user->ID, 'wps_balance', true ) );
	if ( $amount > $balance ) {
		throw new CMB2_Form_Exception( 'Not enough seeds on that account', 'amount' );
	}

	$balance -= $amount;
	update_user_meta( $user->ID, 'wps_balance', $balance );
}

/**
 * Form for burning seeds.
 *
 * @return void
 */
function wps_burn_seeds_form() {
	$cmb = new_cmb2_box(
		array(
			'id'           => 'wps_burn_seeds_form',
			'title'        => esc_html__( 'Burn Seeds', 'wp-seeds' ),
			'object_types' => array( 'options-page' ),
			'option_key'   => 'wps_burn_seeds',
			'parent_slug'  => 'admin.php',
			'save_button'  => esc_html__( 'Burn Seeds', 'wp-seeds' ),
			'save_cb'      => 'wps_burn_seeds_save',
			'save_message' => esc_html__( 'Seeds Burned', 'wp-seeds' ),
		)
	);

	CMB2_Custom_Handler::hookup( $cmb );

	$cmb->add_field(
		array(
			'name'             => esc_html__( 'Sender', 'wp-seeds' ),
			'description'      => esc_html__( 'Where should the seeds be taken from?', 'wp-seeds' ),
			'id'               => 'sender',
			'type'             => 'select',
			'show_option_none' => __( 'Please select', 'wp-seeds' ),
			'options'          => wps_user_display_by_id(),
		)
	);

	$cmb->add_field(
		array(
			'name'    => esc_html__( 'Amount', 'wp-seeds' ),
			'desc'    => esc_html__( 'How many seeds should be burned?', 'wp-seeds' ),
			'id'      => 'amount',
			'type'    => 'text_money',
			'attributes' => array(
				'autocomplete' => 'off',
			),
		)
	);
}
add_action( 'cmb2_admin_init', 'wps_burn_seeds_form' );

/**
 * Handle creation of new seeds.
 *
 * @return void
 * @throws CMB2_Form_Exception If there is an error.
 */
function wps_create_seeds_save() {
	$user = get_user_by( 'id', get_req_str( 'receiver' ) );
	if ( ! $user ) {
		throw new CMB2_Form_Exception( 'Please select a user to receive the created seeds.', 'receiver' );
	}

	$amount = intval( get_req_str( 'amount' ) );
	if ( $amount <= 0 ) {
		throw new CMB2_Form_Exception( 'Amount needs to be greater than zero', 'amount' );
	}

	$balance = intval( get_user_meta( $user->ID, 'wps_balance', true ) );
	$balance += $amount;
	update_user_meta( $user->ID, 'wps_balance', $balance );
}

/**
 * Form for creating seeds.
 *
 * @return void
 */
function wps_create_seeds_form() {
	$cmb = new_cmb2_box(
		array(
			'id'           => 'wps_create_seeds',
			'title'        => esc_html__( 'Create Seeds', 'wp-seeds' ),
			'object_types' => array( 'options-page' ),
			'option_key'   => 'wps_create_seeds',
			'parent_slug'  => 'admin.php',
			'save_button'  => esc_html__( 'Create Seeds', 'wp-seeds' ),
			'save_cb'      => 'wps_create_seeds_save',
			'save_message' => esc_html__( 'Seeds Created', 'wp-seeds' ),
		)
	);

	CMB2_Custom_Handler::hookup( $cmb );

	$cmb->add_field(
		array(
			'name'             => esc_html__( 'Receiver', 'wp-seeds' ),
			'description'      => esc_html__( 'Who will send the new seeds?', 'wp-seeds' ),
			'id'               => 'receiver',
			'type'             => 'select',
			'show_option_none' => __( 'Please select', 'wp-seeds' ),
			'options'          => wps_user_display_by_id(),
		)
	);

	$cmb->add_field(
		array(
			'name'    => esc_html__( 'Amount', 'wp-seeds' ),
			'desc'    => esc_html__( 'How many seeds should be created?', 'wp-seeds' ),
			'id'      => 'amount',
			'type'    => 'text_money',
			'attributes' => array(
				'autocomplete' => 'off',
			),
		)
	);
}
add_action( 'cmb2_admin_init', 'wps_create_seeds_form' );

/**
 * Save the transaction.
 */
function wps_new_transaction_save() {
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
function wps_new_transaction_form() {
	$cmb = new_cmb2_box(
		array(
			'id'           => 'create_transaction',
			'title'        => esc_html__( 'Create Transaction', 'wp-seeds' ),
			'object_types' => array( 'options-page' ),
			'option_key'   => 'wps_new_transaction',
			'parent_slug'  => 'admin.php',
			'save_button'  => esc_html__( 'Create Transaction', 'wp-seeds' ),
			'save_cb'      => 'wps_new_transaction_save',
			'save_message' => esc_html__( 'Transaction Created', 'wp-seeds' ),
		)
	);

	CMB2_Custom_Handler::hookup( $cmb );

	$cmb->add_field(
		array(
			'name'             => esc_html__( 'Sender', 'wp-seeds' ),
			'description'      => esc_html__( 'Who will send the seeds?', 'wp-seeds' ),
			'id'               => 'sender',
			'type'             => 'select',
			'show_option_none' => __( 'Please select', 'wp-seeds' ),
			'options'          => wps_user_display_by_id(),
		)
	);

	$cmb->add_field(
		array(
			'name'             => esc_html__( 'Receiver', 'wp-seeds' ),
			'description'      => esc_html__( 'Who will send the seeds?', 'wp-seeds' ),
			'id'               => 'receiver',
			'type'             => 'select',
			'show_option_none' => __( 'Please select', 'wp-seeds' ),
			'options'          => wps_user_display_by_id(),
		)
	);

	$cmb->add_field(
		array(
			'name'    => esc_html__( 'Amount', 'wp-seeds' ),
			'desc'    => esc_html__( 'What is the amount for the transaction?', 'wp-seeds' ),
			'id'      => 'amount',
			'type'    => 'text_money',
			'attributes' => array(
				'autocomplete' => 'off',
			),
		)
	);
}
add_action( 'cmb2_admin_init', 'wps_new_transaction_form' );

/**
 * Show info on the user profile page.
 *
 * @param WP_User $user The user to show info for.
 * @return void
 */
function wps_show_user_profile( $user ) {
	?>
	<h2>Seeds</h2>
	<table class='form-table'>
		<th>Seeds Balance</th>
		<td><?php echo intval( get_user_meta( $user->ID, 'wps_balance', true ) ); ?></td>
	</table>
	<?php
}
add_action( 'show_user_profile', 'wps_show_user_profile' );

/**
 * Admin menu hook, add menu.
 *
 * @since 1.0.0
 * @return void
 */
function wps_admin_menu() {
	add_menu_page(
		'Seeds',
		'Seeds',
		'manage_options',
		'wps_transactions',
		null,
		'dashicons-money',
		71
	);
	add_submenu_page(
		'wps_transactions',
		'Transactions',
		'Transactions',
		'manage_options',
		'wps_transactions',
		'wps_transactions_page'
	);
	add_submenu_page(
		'wps_transactions',
		'New Transaction',
		'New Transaction',
		'manage_options',
		'wps_new_transaction'
	);
	add_submenu_page(
		'wps_transactions',
		'Create Seeds',
		'Create Seeds',
		'manage_options',
		'wps_create_seeds'
	);
	add_submenu_page(
		'wps_transactions',
		'Burn Seeds',
		'Burn Seeds',
		'manage_options',
		'wps_burn_seeds'
	);
}
add_action( 'admin_menu', 'wps_admin_menu' );

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
