<?php
/**
 * WP Seeds 🌱
 *
 * @package   wp-seeds
 * @link      https://github.com/limikael/wp-seeds
 * @author    Mikael Lindqvist, Niels Lange & Derek Smith
 * @copyright 2019 Mikael Lindqvist, Niels Lange & Derek Smith
 * @license   GPL v2 or later

 * Plugin Name:       WP Seeds
 * Plugin URI:        https://github.com/limikael/wp-seeds
 * Description:       Allows users to hold, send and receive tokens named seeds.
 * Version:           1.0
 * Requires at least: 5.2
 * Requires PHP:      7.2
 * Author:            Mikael Lindqvist, Niels Lange & Derek Smith
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

function wps_enqueue_style() {
	global $post;
	if ( (isset($post->post_content) && has_shortcode( $post->post_content, 'seeds_send')) || (isset($post->post_content) && has_shortcode( $post->post_content, 'seeds_receive')) ) {
        wp_enqueue_style( 'front-styles', plugin_dir_url( __FILE__ ) . '/front-styles.css', null, '1.0', 'screen' );
	}
	if ( (isset($post->post_content) && has_shortcode( $post->post_content, 'seeds_send')) ) {
		wp_enqueue_script( 'qr-generator', plugin_dir_url( __FILE__ ) . 'ext/qrcodejs/qrcode.js', 'jquery', null, false );
	}
}
add_action('wp_enqueue_scripts', 'wps_enqueue_style');


/* Create Seeds Balance User Meta */
function add_user_balance() {
	$users = get_users( ['fields' => ['ID'] ] );
		foreach ( $users as $user ) {
			$user_update = update_user_meta($user->ID, 'seeds_balance', 0);
		}
}
add_action('init','add_user_balance');


/**
 * Show the list of transactions.
 *
 * @return void
 */
function wps_transactions_page() {
	if ( is_req_var( 'transaction_detail' ) ) {
		$transaction = Transaction::findOne( get_req_str( 'transaction_detail' ) );
		$user_display_by_id = wps_user_display_by_id()
		?>
			<div class='wrap'>
				<h1><?php esc_html_e( 'Transaction', 'wp-seeds' ); ?></h1>
				<table class='form-table'>
					<tr>
						<th><?php esc_html_e( 'Time', 'wp-seeds' ); ?></th>
						<td><?php echo esc_html( date( 'Y-m-d H:m:s', $transaction->timestamp ) ); ?></td>
					</tr>
					<tr>
						<th><?php esc_html_e( 'Transaction ID', 'wp-seeds' ); ?></th>
						<td><?php echo esc_html( $transaction->transaction_id ); ?></td>
					</tr>
					<tr>
						<th><?php esc_html_e( 'From Account', 'wp-seeds' ); ?></th>
						<td><?php echo esc_html( $user_display_by_id[ $transaction->sender ] ); ?></td>
					</tr>
					<tr>
						<th><?php esc_html_e( 'To Account', 'wp-seeds' ); ?></th>
						<td><?php echo esc_html( $user_display_by_id[ $transaction->receiver ] ); ?></td>
					</tr>
					<tr>
						<th><?php esc_html_e( 'Amount', 'wp-seeds' ); ?></th>
						<td><?php echo esc_html( $transaction->amount ); ?></td>
					</tr>
				</table>
			</div>
		<?php
		return;
	}

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
		$link               = get_admin_url( null, 'admin.php?page=wps_transactions&transaction_detail=' . $transaction->id );
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
			'id'           => 'wps_new_transaction',
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
function wps_user_profile( $user ) {
	?>
	<h2>Seeds</h2>
	<table class='form-table'>
		<th>Seeds Balance</th>
		<td><?php echo intval( get_user_meta( $user->ID, 'wps_balance', true ) ); ?></td>
	</table>
	<?php
}
add_action( 'show_user_profile', 'wps_user_profile' );
add_action( 'edit_user_profile', 'wps_user_profile' );

/**
 * Register new column on the user list page.
 *
 * @param array $column The columns.
 * @return array The updated columns.
 */
function wps_manage_users_columns( $column ) {
	$column['wps_balance'] = 'Seeds';
	return $column;
}
add_filter( 'manage_users_columns', 'wps_manage_users_columns' );

/**
 * Show info in the seeds balance column.
 *
 * @param string $val Not sure what it is for.
 * @param string $column_name The column name.
 * @param string $user_id The user id.
 * @return string The value for the column.
 */
function wps_manage_users_custom_column( $val, $column_name, $user_id ) {
	switch ( $column_name ) {
		case 'wps_balance':
			$url = get_admin_url( null, 'admin.php?page=wps_transactions&account=' . $user_id );
			$balance = intval( get_user_meta( $user_id, 'wps_balance', true ) );
			return sprintf(
				'<a href="%s">%s</a>',
				esc_attr( $url ),
				esc_html( $balance )
			);
		default:
			return $val;
	}
}
add_filter( 'manage_users_custom_column', 'wps_manage_users_custom_column', 10, 3 );

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
		'wps_new_transaction',
		'__no_func'
	);
	add_submenu_page(
		'wps_transactions',
		'Create Seeds',
		'Create Seeds',
		'manage_options',
		'wps_create_seeds',
		'__no_func'
	);
	add_submenu_page(
		'wps_transactions',
		'Burn Seeds',
		'Burn Seeds',
		'manage_options',
		'wps_burn_seeds',
		'__no_func'
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
add_filter('cmb2_meta_box_url','wps_cmb2_meta_box_url');


/* Send Seeds Shortcode */
function send_seed_form_shortcode( $atts = array() ) {
	global $post;

	/**
	 * Depending on your setup, check if the user has permissions to edit_posts
	 */
	if ( ! is_user_logged_in() ) {
		return __( 'You do not have permissions to be here.', 'lang_domain' );
	}

	$user_id = get_current_user_id();
	$user_meta = get_user_meta( $user_id );

	$user_info = get_userdata($user_id);
	$user_email = $user_info->user_email;

	$user_first = $user_meta['first_name'][0];
	$user_last = $user_meta['last_name'][0];
	$user_balance = $user_meta['seeds_balance'][0];

	//var_dump($user_meta);
	//var_dump($user_info);
	?>
	<div class="seeds">

		<h2>Welcome <?php echo $user_first;?> <?php echo $user_last ?></h2>

		<div class="seeds-balance">
			<p>Your Current Balance is:</p>
			<p class="CurrSeeds"><?php echo "{$user_balance} Seed".($user_balance == 1 ? "" : "s"); ?></p>
		</div>

		<?php
		$vars    = array();
		$show_qr = false;

		if ( isset( $_REQUEST['do_request'] ) ) {
			if ( ! empty( $_REQUEST['amount'] ) ) {
				$to_user                = (int) get_current_user_id();
				$amount                 = (int) $_REQUEST['amount'];
				$home					= get_site_url();
				$vars['notice_success'] = __( 'Your QR has been created successfully. Please ask the sender to scan this QR code to transfer seeds to you.', 'wp-seeds' );
				$vars['qr_code_url']    = sprintf( '%3$s/transactions?to_user=2&amount=1', $to_user, $amount, $home );
				$show_qr                = true;

			} else {
				$vars['notice_error'] = __( 'Please provide an amount to request.', 'wp-seeds' );
			}
		}

		if ( $show_qr ) {
			display_template( dirname( __FILE__ ) . '/tpl/wps-request-transaction-code.tpl.php', $vars );
		} else {
			display_template( dirname( __FILE__ ) . '/tpl/wps-request-transaction-page.tpl.php', $vars );
		}
		?>


	</div>

	<?php

}
add_shortcode( 'seeds_send', 'send_seed_form_shortcode' );


/* Receive Seeds Shortcode */
function receive_seed_form_shortcode( $atts = array() ) {
	global $post;

	/**
	 * Depending on your setup, check if the user has permissions to edit_posts
	 */
	if ( ! is_user_logged_in() ) {
		return __( 'You do not have permissions to be here.', 'lang_domain' );
	}

	$user_id = get_current_user_id();
	$user_meta = get_user_meta( $user_id );

	$user_info = get_userdata($user_id);
	$user_email = $user_info->user_email;

	$user_first = $user_meta['first_name'][0];
	$user_last = $user_meta['last_name'][0];
	$user_balance = $user_meta['seeds_balance'][0];

	//var_dump($user_meta);
	//var_dump($user_info);

	?>

	<div class="seeds">

		<h2>Welcome <?php echo $user_first;?> <?php echo $user_last ?></h2>

		<div class="seeds-balance">
			<p>Your Current Balance is:</p>
			<p class="CurrSeeds"><?php echo "{$user_balance} Seed".($user_balance == 1 ? "" : "s"); ?></p>
		</div>

	</div>
	
	<?php

}
add_shortcode( 'seeds_receive', 'receive_seed_form_shortcode' );
