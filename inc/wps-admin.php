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
 * Load admin styles.
 *
 * @since 1.0.0
 * @return void
 */
function wps_admin_style() {
	wp_enqueue_style( 'admin-styles', plugin_dir_url( __FILE__ ) . 'css/admin.css', null, '1.0', 'screen' );
}
add_action( 'admin_enqueue_scripts', 'wps_admin_style' );

/**
 * Show the list of transactions.
 *
 * @return void
 */
function wps_transactions_page() {
	if ( is_req_var( 'transaction_detail' ) ) {
		display_template(
			__DIR__ . '/../tpl/wps-transaction-detail.tpl.php',
			array(
				'transaction' => Transaction::findOne( get_req_str( 'transaction_detail' ) ),
				'user_display_by_id' => wps_user_display_by_id(),
			)
		);
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
			'object_types' => array( 'options-page', 'post' ),
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
add_action( 'cmb2_init', 'wps_new_transaction_form' );

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
