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
require_once dirname( __FILE__ ) . '/classes/class-wps-validation.php';
require_once dirname( __FILE__ ) . '/inc/lib.php';
require_once dirname( __FILE__ ) . '/inc/transaction.php';
require_once dirname( __FILE__ ) . '/inc/transactions-all.php';
require_once dirname( __FILE__ ) . '/inc/users-all.php';
require_once dirname( __FILE__ ) . '/inc/users-profile.php';
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
 * Register Custom Post Type
 *
 * @since 1.0.0
 * @return void
 */
function wps_register_cpt() {

	$labels = array(
		'name'               => _x( 'Transactions', 'Post Type General Name', 'wp-seeds' ),
		'singular_name'      => _x( 'Transaction', 'Post Type Singular Name', 'wp-seeds' ),
		'menu_name'          => __( 'Transactions', 'wp-seeds' ),
		'parent_item_colon'  => __( 'Parent Item:', 'wp-seeds' ),
		'all_items'          => __( 'All Transactions', 'wp-seeds' ),
		'view_item'          => __( 'View Transaction', 'wp-seeds' ),
		'add_new_item'       => __( 'Create Transaction', 'wp-seeds' ),
		'add_new'            => __( 'Create Transaction', 'wp-seeds' ),
		'edit_item'          => __( 'Edit Transaction', 'wp-seeds' ),
		'update_item'        => __( 'Update Transaction', 'wp-seeds' ),
		'search_items'       => __( 'Search Transaction', 'wp-seeds' ),
		'not_found'          => __( 'Not found', 'wp-seeds' ),
		'not_found_in_trash' => __( 'Not found in Trash', 'wp-seeds' ),
	);
	$args   = array(
		'label'               => __( 'Transactions', 'wp-seeds' ),
		'description'         => __( 'Transfer seeds from one user to another', 'wp-seeds' ),
		'labels'              => $labels,
		'supports'            => array(),
		'taxonomies'          => array(),
		'hierarchical'        => false,
		'public'              => true,
		'show_ui'             => true,
		'show_in_menu'        => true,
		'show_in_nav_menus'   => true,
		'show_in_admin_bar'   => true,
		'show_in_rest'        => true,
		'menu_position'       => 2,
		'menu_icon'           => 'dashicons-money',
		'can_export'          => true,
		'has_archive'         => true,
		'exclude_from_search' => false,
		'publicly_queryable'  => true,
		'capability_type'     => 'transaction',
		'capabilities'        => array(
			'edit_post'          => 'edit_transaction',
			'edit_posts'         => 'edit_transactions',
			'edit_others_posts'  => 'edit_other_transactions',
			'publish_posts'      => 'publish_transactions',
			'read_post'          => 'read_transaction',
			'read_private_posts' => 'read_private_transactions',
			'delete_post'        => 'delete_transaction',
		),
		'map_meta_cap'        => true,
	);
	register_post_type( 'transaction', $args );

}
add_action( 'init', 'wps_register_cpt', 0 );

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
	$post = get_post( $post_id );
	$temp = array();

	if ( 'transaction' === get_post_type( $post_id ) ) {
		$temp[] = date( 'Y.m.d' );
		$temp[] = get_field( 'from_user' );
		$temp[] = get_field( 'to_user' );
		$temp[] = get_field( 'amount' );
		$temp[] = time();

		$post->post_title = crypt( implode( '', $temp ) );
	}

	wp_update_post( $post );
}
add_action( 'acf/validate_save_post', 'wps_save_post', 20 );

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

	if ( wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['my_nonce'] ) ) ) ) {
		return;
	}

	$from_user = (int) $_POST['acf']['field_5d6e6ed3f45ac'];
	$amount    = (int) $_POST['acf']['field_5d6e6efff45ae'];
	$balance   = get_user_meta( $from_user, 'wps_balance', true );

	if ( WPS_Validation::is_negative( $amount ) ) {
		$valid = esc_html__( 'Amount cannot be negative.', 'wp-seeds' );
	}

	if ( WPS_Validation::is_insufficient_balance( $amount, $balance ) ) {
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
 * Add custom user role
 *
 * @since 1.0.0
 * @return void
 */
function add_roles_on_plugin_activation() {
	add_role(
		'gardener',
		'Gardener',
		array(
			'edit_transaction'          => true,
			'edit_transactions'         => true,
			'edit_other_transactions'   => true,
			'publish_transactions'      => true,
			'read_transaction'          => true,
			'read_private_transactions' => true,
			'delete_transaction'        => true,
		)
	);
}
add_action( 'init', 'add_roles_on_plugin_activation' );

/**
 * Add custom user capabilities
 *
 * @since 1.0.0
 * @return void
 */
function add_theme_caps() {
	$role = get_role( 'gardener' );
	$role->add_cap( 'edit_transactions' );
	$role->add_cap( 'edit_other_transactions' );
	$role->add_cap( 'publish_transactions' );
	$role->add_cap( 'read_transaction' );
	$role->add_cap( 'read_private_transactions' );
	$role->add_cap( 'delete_transaction' );

	$role = get_role( 'administrator' );
	$role->add_cap( 'edit_transactions' );
	$role->add_cap( 'edit_other_transactions' );
	$role->add_cap( 'publish_transactions' );
	$role->add_cap( 'read_transaction' );
	$role->add_cap( 'read_private_transactions' );
	$role->add_cap( 'delete_transaction' );
}
add_action( 'init', 'add_theme_caps' );

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
		'manage_options',
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
 * @param array $field The original array with fields.
 * @return array $field The updated array with fields.
 */
function wps_populate_from_user_field( $field ) {

	if ( ! empty( $_REQUEST['action'] ) && 'request-transaction' === $_REQUEST['action'] ) {
		$user                   = wp_get_current_user();
		$field['default_value'] = $user->ID;
	}

	return $field;

}
add_filter( 'acf/load_field/name=from_user', 'wps_populate_from_user_field' );

/**
 * Populate to user field.
 *
 * @param array $field The original array with fields.
 * @return array $field The updated array with fields.
 */
function wps_populate_to_user_field( $field ) {

	if ( ! empty( $_REQUEST['uid'] ) && is_numeric( $_REQUEST['uid'] ) ) {
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

	if ( ! empty( $_REQUEST['amount'] ) && is_numeric( $_REQUEST['amount'] ) ) {
		$field['default_value'] = (int) $_REQUEST['amount'];
	}

	return $field;

}
add_filter( 'acf/load_field/name=amount', 'wps_populate_amount_field' );

/**
 * WP Seeds settings page.
 *
 * @since 1.0.0
 * @return void
 */
function wps_settings_page() {
	$vars = array();

	if ( isset( $_REQUEST['do_create'] ) && isset( $_REQUEST['amount'] ) && isset( $_REQUEST['user_id'] ) ) {
		$user     = get_user_by( 'id', (int) $_REQUEST['user_id'] );
		$balance  = intval( get_user_meta( $user->ID, 'wps_balance', true ) );
		$balance += intval( $_REQUEST['amount'] );
		update_user_meta( $user->ID, 'wps_balance', $balance );
		$vars ['notice_success'] = __( 'The seeds have been created.', 'wp-seeds' );
	} elseif ( isset( $_REQUEST['do_burn'] ) && isset( $_REQUEST['amount'] ) && isset( $_REQUEST['user_id'] ) ) {
		$user     = get_user_by( 'id', (int) $_REQUEST['user_id'] );
		$balance  = intval( get_user_meta( $user->ID, 'wps_balance', true ) );
		$balance -= intval( $_REQUEST['amount'] );
		update_user_meta( $user->ID, 'wps_balance', $balance );
		$vars ['notice_success'] = __( 'The seeds have been burned.', 'wp-seeds' );
	}

	$vars['users'] = array();
	foreach ( get_users() as $user ) {
		$vars['users'][ $user->ID ] = wps_transaction_format_user( $user );
	}

	display_template( dirname( __FILE__ ) . '/tpl/wps-settings-page.tpl.php', $vars );
}
