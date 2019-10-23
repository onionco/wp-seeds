<?php
/**
 * WP Seeds 🌱
 *
 * Create CPT transaction.
 *
 * @package   wp-seeds/inc
 * @link      https://github.com/limikael/wp-seeds
 * @author    Mikael Lindqvist & Niels Lange
 * @copyright 2019 Mikael Lindqvist & Niels Lange
 * @license   GPL v2 or later
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
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
			'delete_post'            => 'delete_transaction',
			'delete_posts'           => 'delete_transactions',
			'delete_private_posts'   => 'delete_private_transactions',
			'delete_published_posts' => 'delete_published_transactions',
			'delete_others_posts'    => 'delete_others_transactions',
			'edit_post'              => 'edit_transaction',
			'edit_posts'             => 'edit_transactions',
			'edit_others_posts'      => 'edit_other_transactions',
			'edit_private_posts'     => 'edit_private_transactions',
			'edit_published_posts'   => 'edit_published_transactions',
			'publish_posts'          => 'publish_transactions',
			'read_post'              => 'read_transaction',
			'read_private_posts'     => 'read_private_transactions',
		),
		'map_meta_cap'        => true,
	);
	register_post_type( 'transaction', $args );
}
add_action( 'init', 'wps_register_cpt', 0 );
