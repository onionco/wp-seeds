<?php
/**
 * WP Seeds 🌱
 *
 * Custom functionality for transactions overview page.
 *
 * @package   wp-seeds/inc
 * @link      https://github.com/onionco/wp-seeds
 * @author    Mikael Lindqvist, Niels Lange & Derek Smith
 * @copyright 2020 Mikael Lindqvist, Niels Lange & Derek Smith
 * @license   GPL v2 or later
 */

defined( 'ABSPATH' ) || exit;


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
 * Show the list of transactions.
 *
 * @return void
 */
function wps_transactions_page() {
	if ( is_req_var( 'transaction_detail' ) ) {
		display_template(
			__DIR__ . '/../tpl/wps-transaction-detail.tpl.php',
			array(
				'transaction' => WPS_Transaction::findOne( get_req_var( 'transaction_detail' ) ),
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
		$order = esc_sql( get_req_var( 'orderby' ) ) . ' ' . esc_sql( get_req_var( 'order' ) );
	} else {
		$order = 'timestamp desc';
	}

	if ( is_req_var( 'account' ) && get_req_var( 'account' ) ) {
		$transactions = WPS_Transaction::findAllByQuery(
			'SELECT   * ' .
			'FROM     :table ' .
			'WHERE    sender=%s ' .
			'OR       receiver=%s ' .
			'ORDER BY ' . $order,
			get_req_var( 'account' ),
			get_req_var( 'account' )
		);
	} else {
		$transactions = WPS_Transaction::findAllByQuery(
			'SELECT   * ' .
			'FROM      :table ' .
			"ORDER BY  $order"
		);
	}

	$user_display_by_id = wps_user_display_by_id();
	$transaction_views = array();
	foreach ( $transactions as $transaction ) {
		$link               = get_admin_url(
			null,
			'admin.php?page=wps_transactions&transaction_detail=' . $transaction->id
		);

		$transaction_views[] = array(
			'id'          => "<a href='$link'>" . $transaction->transaction_id . '</a>',
			'fromAccount' => $user_display_by_id[ $transaction->sender ],
			'toAccount'   => $user_display_by_id[ $transaction->receiver ],
			'amount'      => $transaction->amount, // ." ".$icon,
			'timestamp'   => date( 'Y-m-d H:m:s', $transaction->timestamp ),
			'__class'     => $transaction->getType(),
		);
	}
	$table->set_title( 'Transactions' );
	$table->set_data( $transaction_views );

	$table->display();
}


/**
 * Handle creation of new seeds.
 *
 * @return void
 * @throws WPS_Form_Exception If there is an error.
 */
function wps_create_seeds_save() {
	$t = new WPS_Transaction();
	$t->receiver  = get_req_var( 'receiver' );
	$t->amount    = get_req_var( 'amount' );
	$t->performCreate();
}


/**
 * Handle burning of seeds.
 *
 * @return void
 * @throws WPS_Form_Exception If there is an error.
 */
function wps_burn_seeds_save() {
	$t = new WPS_Transaction();
	$t->sender    = get_req_var( 'sender' );
	$t->amount    = get_req_var( 'amount' );
	$t->performBurn();
}


/**
 * Settings.
 */
function wps_settings_page() {

	wps_process_form(
		array(
			'submit_var' => 'submit-create',
			'process_cb' => 'wps_create_seeds_save',
			'success_message' => __( 'Seeds Created.', 'wp-seeds' ),
		)
	);

	wps_process_form(
		array(
			'submit_var' => 'submit-burn',
			'process_cb' => 'wps_burn_seeds_save',
			'success_message' => __( 'Seeds Burned.', 'wp-seeds' ),
		)
	);

	$vars = array(
		'user_display_by_id' => wps_user_display_by_id(),
		'url' => admin_url( 'admin.php?page=wps_settings' ),
		'tab' => get_req_var( 'tab', 'about' ),
	);

	display_template( __DIR__ . '/../tpl/wps-admin-settings.tpl.php', $vars );
}


/**
 * Save the transaction.
 */
function wps_new_transaction_save() {
	$t = new WPS_Transaction();
	$t->sender    = get_req_var( 'sender' );
	$t->receiver  = get_req_var( 'receiver' );
	$t->amount    = get_req_var( 'amount' );
	$t->perform();
}


/**
 * Create the transaction form.
 *
 * @return void
 */
function wps_new_transaction_page() {
	wps_process_form(
		array(
			'process_cb' => 'wps_new_transaction_save',
			'success_message' => __( 'Transaction Created.', 'wp-seeds' ),
		)
	);

	$vars = array(
		'user_display_by_id' => wps_user_display_by_id(),
	);

	display_template( __DIR__ . '/../tpl/wps-admin-new-transaction.tpl.php', $vars );
}


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
 * Register settings. Add the settings section, and settings fields
 */
add_action( 'admin_init', 'wps_settings_init_fn' );


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
		'wps_view_all_transactions',
		'wps_transactions',
		null,
		'dashicons-money',
		71
	);
	add_submenu_page(
		'wps_transactions',
		'Transactions',
		'Transactions',
		'wps_view_all_transactions',
		'wps_transactions',
		'wps_transactions_page'
	);
	add_submenu_page(
		'wps_transactions',
		'New Transaction',
		'New Transaction',
		'wps_transfer_seeds_universally',
		'wps_new_transaction',
		'wps_new_transaction_page'
	);
	add_submenu_page(
		'wps_transactions',
		__( 'Settings', 'wp-seeds' ),
		__( 'Settings', 'wp-seeds' ),
		'wps_create_burn_seeds',
		'wps_settings',
		'wps_settings_page_fn'
	);
}
add_action( 'admin_menu', 'wps_admin_menu' );


/**
 * Display the admin options page
 */
function wps_settings_page_fn() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
	}

	wps_process_form(
		array(
			'submit_var' => 'submit-create',
			'process_cb' => 'wps_create_seeds_save',
			'success_message' => __( 'Seeds Created.', 'wp-seeds' ),
		)
	);

	wps_process_form(
		array(
			'submit_var' => 'submit-burn',
			'process_cb' => 'wps_burn_seeds_save',
			'success_message' => __( 'Seeds Burned.', 'wp-seeds' ),
		)
	);

	$user_display_by_id = wps_user_display_by_id();
	$url = admin_url( 'admin.php?page=wps_settings' );
	$tab = get_req_var( 'tab', 'about' );

	?>
	
	<div class="wrap">
	<h1><?php esc_html_e( 'Seeds Settings', 'wp-seeds' ); ?></h1>

	<h2 class="nav-tab-wrapper">
		<a href="<?php echo esc_attr( $url . '&tab=about' ); ?>"
				class="nav-tab 
				<?php
				if ( 'about' == $tab ) {
					echo 'nav-tab-active';}
				?>
				">
			Settings
		</a>
		<a href="<?php echo esc_attr( $url . '&tab=create' ); ?>"
				class="nav-tab 
				<?php
				if ( 'create' == $tab ) {
					echo 'nav-tab-active';}
				?>
				">
			Create Seeds
		</a>
		<a href="<?php echo esc_attr( $url . '&tab=burn' ); ?>"
				class="nav-tab 
				<?php
				if ( 'burn' == $tab ) {
					echo 'nav-tab-active';}
				?>
				">
			Burn Seeds
		</a>
	</h2>

	<?php if ( 'about' == $tab ) { ?>

		<?php $options = get_option( 'wps_settings' ); ?>
		
		<p>
			<?php esc_html_e( 'These pages are for creating and burning seeds.', 'wp-seeds' ); ?>
		</p>
		<p>
			<?php esc_html_e( 'This is the equivalent of printing new money, please act responsibly!', 'wp-seeds' ); ?>
		</p>

		<form method="post" action="options.php">
			<?php settings_fields( 'wps_settings_group' ); ?>
			<?php do_settings_sections( 'wps_settings' ); ?>
			<?php submit_button(); ?>
		</form>

	<?php } ?>

	<?php if ( 'create' == $tab ) { ?>
		<form method="post">
			<div class='wps-admin-form'>
				<div class='row'>
					<label for="receiver">Receiver</label>
					<div class='field'>
						<select name="receiver">
							<option value=''><?php esc_html_e( 'Please select', 'wp-seeds' ); ?></option>
							<?php display_select_options( $user_display_by_id, get_req_var( 'receiver', 0 ) ); ?>
						</select>
						<span class="description">
							<?php esc_html_e( 'Who should receive the newly created seeds?', 'wp-seeds' ); ?>
						</span>
					</div>
				</div>
				<div class='row'>
					<label for="receiver">Amount</label>
					<div class='field'>
						<input type="text"
								name="amount"
								value="<?php echo esc_attr( get_req_var( 'amount', '' ) ); ?>"
								class='small-text'
								autocomplete='off'/>
						<span class="description">
							<?php esc_html_e( 'How many seeds should be created?', 'wp-seeds' ); ?>
						</span>
					</div>
				</div>
			</div>
			<input type="submit"
					value="<?php esc_attr_e( 'Create Seeds' ); ?>" 
					name="submit-create"
					class='button button-primary'/>
		</form>
	<?php } ?>

	<?php if ( 'burn' == $tab ) { ?>
		<form method="post">
			<div class='wps-admin-form'>
				<div class='row'>
					<label for="sender">Sender</label>
					<div class='field'>
						<select name="sender">
							<option value=''><?php esc_html_e( 'Please select', 'wp-seeds' ); ?></option>
							<?php display_select_options( $user_display_by_id, get_req_var( 'sender', 0 ) ); ?>
						</select>
						<span class="description">
							<?php esc_html_e( 'Where should the seeds be taken from?', 'wp-seeds' ); ?>
						</span>
					</div>
				</div>
				<div class='row'>
					<label for="amount">Amount</label>
					<div class='field'>
						<input type="text"
								name="amount"
								value="<?php echo esc_attr( get_req_var( 'amount', '' ) ); ?>"
								class='small-text'
								autocomplete='off'/>
						<span class="description">
							<?php esc_html_e( 'How many seeds should be burned?', 'wp-seeds' ); ?>
						</span>
					</div>
				</div>
			</div>
			<input type="submit"
					value="<?php esc_attr_e( 'Burn Seeds' ); ?>" 
					name="submit-burn"
					class='button button-primary'/>
		</form>
	<?php } ?>
</div>

	<?php
}


/**
 * Register settings. Add the settings section, and settings fields
 */
function wps_settings_init_fn() {
	register_setting(
		'wps_settings_group',
		'wps_settings'
	);
	add_settings_section(
		'wps_settings_section',
		'',
		'wps_section_fn',
		'wps_settings'
	);
	add_settings_field(
		'account_page',
		'Select Account Page',
		'account_dropdown_fn',
		'wps_settings',
		'wps_settings_section'
	);
}


/**
 * Section HTML, displayed before the first option
 */
function wps_section_fn() {
	echo '';
}


/**
 * Pages dropdown - Name: wps_settings[account_dropdown_fn]
 */
function account_dropdown_fn() {

	$options = get_option( 'wps_settings' );

	echo "<select name='wps_settings[account_page]' id='account_page'>";
		if( $pages = get_pages() ){
			foreach ( $pages as $page ) {
				$option = '<option value=' . $page->ID . '' . selected( $options['account_page'], $page->ID ) . '>';
				$option .= $page->post_title;
				$option .= '</option>';
				echo $option;
			};
		}
	echo '</select>';
}
