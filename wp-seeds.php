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
require_once dirname( __FILE__ ) . '/classes/class-tgm-plugin-activation.php';
require_once dirname( __FILE__ ) . '/inc/lib.php';
require_once dirname( __FILE__ ) . '/inc/transaction.php';
require_once dirname( __FILE__ ) . '/inc/transactions-all.php';
require_once dirname( __FILE__ ) . '/inc/users-all.php';
require_once dirname( __FILE__ ) . '/inc/users-profile.php';
require_once dirname( __FILE__ ) . '/inc/wps-cpt-transaction.php';
require_once dirname( __FILE__ ) . '/inc/wps-roles-and-caps.php';
require_once dirname( __FILE__ ) . '/inc/wps-shortcodes.php';

/**
 * Register the required plugins for this theme.
 *
 * This function is hooked into tgmpa_init, which is fired within the
 * TGM_Plugin_Activation class constructor.
 *
 * @since 1.0.0
 * @return void
 */
function wps_tgmpa_register() {
	$plugins = array(
		array(
			'name'               => 'Advanced Custom Fields',
			'slug'               => 'advanced-custom-fields',
			'required'           => true,
			'force_activation'   => true,
			'force_deactivation' => true,
		),
	);

	$config = array(
		'id'           => 'wps_tgmpa',
		'default_path' => '',
		'menu'         => 'tgmpa-install-plugins',
		'parent_slug'  => 'plugins.php',
		'capability'   => 'activate_plugins',
		'has_notices'  => true,
		'dismissable'  => true,
		'dismiss_msg'  => '',
		'is_automatic' => true,
		'message'      => '',
		'strings'      => array(
			/* Translators: %s: name of the plugin that needs to be installed */
			'notice_can_install_required' => _n_noop(
				'WP Seeds 🌱 plugin has the following dependency: %1$s.',
				'WP Seeds 🌱 plugin has the following dependencies: %1$s.',
				'wp-seeds'
			),
		),
	);

	tgmpa( $plugins, $config );
}
add_action( 'tgmpa_register', 'wps_tgmpa_register' );

/**
 * Register the required plugins for this theme.
 *
 * @since 1.0.0
 * @return void
 */
if ( function_exists( 'acf_add_local_field_group' ) ) {
	acf_add_local_field_group(
		array(
			'key'                   => 'group_5d6e6eca8fedc',
			'title'                 => 'Transaction',
			'fields'                => array(
				array(
					'key'               => 'field_5d6e6ed3f45ac',
					'label'             => 'From user',
					'name'              => 'from_user',
					'type'              => 'user',
					'instructions'      => '',
					'required'          => 1,
					'conditional_logic' => 0,
					'wrapper'           => array(
						'width' => '',
						'class' => '',
						'id'    => '',
					),
					'role'              => '',
					'allow_null'        => 0,
					'multiple'          => 0,
					'return_format'     => '',
				),
				array(
					'key'               => 'field_5d6e6ef5f45ad',
					'label'             => 'To user',
					'name'              => 'to_user',
					'type'              => 'user',
					'instructions'      => '',
					'required'          => 1,
					'conditional_logic' => 0,
					'wrapper'           => array(
						'width' => '',
						'class' => '',
						'id'    => '',
					),
					'role'              => '',
					'allow_null'        => 0,
					'multiple'          => 0,
					'return_format'     => '',
				),
				array(
					'key'               => 'field_5d6e6efff45ae',
					'label'             => 'Amount',
					'name'              => 'amount',
					'type'              => 'number',
					'instructions'      => '',
					'required'          => 1,
					'conditional_logic' => 0,
					'wrapper'           => array(
						'width' => '',
						'class' => '',
						'id'    => '',
					),
					'default_value'     => '',
					'placeholder'       => '',
					'prepend'           => '',
					'append'            => '',
					'min'               => '',
					'max'               => '',
					'step'              => '',
				),
				array(
					'key'               => 'field_5d6e6f10f45af',
					'label'             => 'Note',
					'name'              => 'note',
					'type'              => 'text',
					'instructions'      => '',
					'required'          => 0,
					'conditional_logic' => 0,
					'wrapper'           => array(
						'width' => '',
						'class' => '',
						'id'    => '',
					),
					'default_value'     => '',
					'placeholder'       => '',
					'prepend'           => '',
					'append'            => '',
					'maxlength'         => '',
				),
			),
			'location'              => array(
				array(
					array(
						'param'    => 'post_type',
						'operator' => '==',
						'value'    => 'transaction',
					),
				),
			),
			'menu_order'            => 0,
			'position'              => 'normal',
			'style'                 => 'default',
			'label_placement'       => 'left',
			'instruction_placement' => 'label',
			'hide_on_screen'        => array(
				0 => 'the_content',
			),
			'active'                => true,
			'description'           => '',
		)
	);
}


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
 * Auto add and update title field
 *
 * @since 1.0.0
 * @param mixed $post_id The post id.
 * @return void
 */
function wps_save_post( $post_id ) {
	if ( 'transaction' === get_post_type( $post_id ) ) {
		wps_process_transaction( $post_id );
	}
}
add_action( 'acf/save_post', 'wps_save_post', 20 );

/**
 * Validate amount field
 *
 * @param string $valid The original validation string.
 * @return string $valid The updated validation string.
 */
function wps_validate_value_amount( $valid ) {

	if ( ! $valid ) {
		return $valid;
	}

	if ( ! isset( $_POST['acf']['field_5d6e6ed3f45ac'] )
		|| ! isset( $_POST['acf']['field_5d6e6efff45ae'] ) ) {
		return;
	}

	$from_user = (int) $_POST['acf']['field_5d6e6ed3f45ac'];
	$amount    = (int) $_POST['acf']['field_5d6e6efff45ae'];
	$balance   = get_user_meta( $from_user, 'wps_balance', true );

	if ( $amount < 0 ) {
		$valid = esc_html__( 'Amount cannot be negative.', 'wp-seeds' );
	}

	if ( $amount > $balance ) {
		/* Translators: %1$d is the balance of the current user. */
		$valid = sprintf( esc_html__( 'Insufficient balance. Current balance is %1$d.', 'wp-seeds' ), $balance );
	}

	return $valid;
}
add_filter( 'acf/validate_value/name=amount', 'wps_validate_value_amount', 10 );

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
 * Populate from user field.
 *
 * @since 1.0.0
 * @param array $field The original array with fields.
 * @return array $field The updated array with fields.
 */
function wps_populate_from_user_field( $field ) {

	if ( ! empty( $_REQUEST['action'] ) 
		&& 'request-transaction' === $_REQUEST['action'] ) {
		$user                   = wp_get_current_user();
		$field['default_value'] = $user->ID;
	}

	if ( current_user_can( 'subscriber' ) 
		|| current_user_can( 'contributor' ) 
		|| current_user_can( 'author' ) 
		|| current_user_can( 'editor' ) ) {
		$user                   = wp_get_current_user();
		$field['default_value'] = $user->ID;
	}

	return $field;

}
add_filter( 'acf/load_field/name=from_user', 'wps_populate_from_user_field' );

/**
 * Populate to user field.
 *
 * @since 1.0.0
 * @param array $field The original array with fields.
 * @return array $field The updated array with fields.
 */
function wps_populate_to_user_field( $field ) {

	if ( ! empty( $_REQUEST['uid'] ) 
		&& is_numeric( $_REQUEST['uid'] ) ) {
		$user                   = get_userdata( (int) $_REQUEST['uid'] );
		$field['default_value'] = $user->ID;
	}

	return $field;

}
add_filter( 'acf/load_field/name=to_user', 'wps_populate_to_user_field' );

/**
 * Populate amount field.
 *
 * @param array $field The original array with fields.
 * @return array $field The updated array with fields.
 */
function wps_populate_amount_field( $field ) {

	if ( ! empty( $_REQUEST['amount'] ) 
		&& is_numeric( $_REQUEST['amount'] ) ) {
		$field['default_value'] = (int) $_REQUEST['amount'];
	}

	return $field;

}
add_filter( 'acf/load_field/name=amount', 'wps_populate_amount_field' );

/**
 * Populate note field.
 *
 * @param array $field The original array with fields.
 * @return array $field The updated array with fields.
 */
function wps_populate_note_field( $field ) {

	if ( ! empty( $_REQUEST['note'] ) ) {
		$field['default_value'] = esc_html( $_REQUEST['note'] );
	}

	return $field;

}
add_filter( 'acf/load_field/name=note', 'wps_populate_note_field' );

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

function wps_list_capabilities() {
	global $wp_roles;
	$roles = $wp_roles->roles; 
	echo '<pre>' . print_r( $roles, true ) . '</pre>';
	die();
}
//add_action( 'init', 'wps_list_capabilities' );
