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
 * Include the TGM_Plugin_Activation class.
 *
 * @since 1.0.0
 * @return void
 */
require_once dirname( __FILE__ ) . '/class-tgm-plugin-activation.php';

/**
 * Include custom library.
 *
 * @since 1.0
 * @return void
 */
require_once dirname( __FILE__ ) . '/inc/lib.php';

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
		'add_new_item'       => __( 'Add New Transaction', 'wp-seeds' ),
		'add_new'            => __( 'Add New', 'wp-seeds' ),
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

	if ( get_post_type() === 'transaction' ) {
		$temp[] = date( 'Y.m.d' );
		$temp[] = get_field( 'from_user' );
		$temp[] = get_field( 'to_user' );
		$temp[] = get_field( 'amount' );
		$temp[] = time();

		$post->post_title = crypt( implode( '', $temp ) );
	}

	wp_update_post( $post );
}
add_action( 'acf/save_post', 'wps_save_post', 20 );

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
 * Add custom column titles.
 *
 * @since 1.0.0
 * @param array $columns The original array with columns.
 * @return array $columns The updated array with columns.
 */
function wps_transaction_columns( $columns ) {

	$columns = array(
		'cb'        => $columns['cb'],
		'title'     => __( 'ID' ),
		'from_user' => __( 'From' ),
		'to_user'   => __( 'To' ),
		'amount'    => __( 'Amount' ),
		'date'      => __( 'Date' ),
	);

	return $columns;
}
add_filter( 'manage_edit-transaction_columns', 'wps_transaction_columns' );

/**
 * Add custom column content.
 *
 * @since 1.0.0
 * @param array $column The column to add data to.
 * @param int   $post_id The user id.
 * @return void
 */
function wps_transaction_posts_custom_column( $column, $post_id ) {
	global $post;

	switch ( $column ) {

		case 'from_user':
			$user_id = get_post_meta( $post_id, 'from_user', true );
			$user    = get_userdata( $user_id );
			echo '<a href="' . esc_html( get_edit_user_link( $user->ID ) ) . '">' . esc_attr( $user->display_name ) . '</a>';
			break;
		case 'to_user':
			$user_id = get_post_meta( $post_id, 'to_user', true );
			$user    = get_userdata( $user_id );
			echo '<a href="' . esc_html( get_edit_user_link( $user->ID ) ) . '">' . esc_attr( $user->display_name ) . '</a>';
			break;
		case 'amount':
			echo esc_html( get_post_meta( $post_id, 'amount', true ) );
			break;
	}
}
add_action( 'manage_transaction_posts_custom_column', 'wps_transaction_posts_custom_column', 10, 2 );

/**
 * Make custom columns sortable
 *
 * @since 1.0.0
 * @param array $columns The original array with columns.
 * @return array $columns The updated array with columns.
 */
function wps_transaction_sortable_columns( $columns ) {
	$columns['from_user'] = 'from_user';
	$columns['to_user']   = 'to_user';
	$columns['amount']    = 'amount';

	return $columns;
}
add_filter( 'manage_edit-transaction_sortable_columns', 'wps_transaction_sortable_columns' );

/**
 * Query custom column.
 *
 * @since 1.0.0
 * @param object $query The WP_Query object.
 * @return void
 */
function wps_pre_get_posts( $query ) {
	if ( ! is_admin() ) {
		return;
	}

	$orderby = $query->get( 'orderby' );

	if ( 'from_user' === $orderby ) {
		$query->set( 'meta_key', 'from_user' );
		$query->set( 'orderby', 'meta_value_num' );
	}

	if ( 'to_user' === $orderby ) {
		$query->set( 'meta_key', 'to_user' );
		$query->set( 'orderby', 'meta_value_num' );
	}

	if ( 'amount' === $orderby ) {
		$query->set( 'meta_key', 'amount' );
		$query->set( 'orderby', 'meta_value_num' );
	}
}
add_action( 'pre_get_posts', 'wps_pre_get_posts' );

/**
 * WP Seeds settings page.
 *
 * @return void
 */
function wps_settings_page() {
	$vars = array();

	display_template( dirname( __FILE__ ) . '/tpl/wps_settings_page.tpl.php', $vars );
}

/**
 * Admin menu hook, add options page.
 *
 * @return void
 */
function wps_admin_menu() {
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
