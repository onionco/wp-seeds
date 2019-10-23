<?php
/**
 * WP Seeds 🌱
 *
 * Handle roles and caps.
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
 * Add custom user role
 *
 * @since 1.0.0
 * @return void
 */
function wps_custom_roles() {
	add_role( 'gardener', 'Gardener' );
}
add_action( 'init', 'wps_custom_roles' );

/**
 * Add custom user capabilities
 *
 * @since 1.0.0
 * @return void
 */
function wps_custom_caps() {
	$roles_caps = array(
		'administrator' => array(
			'can'    => array( 'delete_transaction', 'delete_transactions', 'delete_published_transactions', 'delete_private_transactions', 'delete_others_transactions', 'spread_seeds', 'edit_transaction', 'edit_transactions', 'edit_other_transactions', 'edit_private_transactions', 'edit_published_transactions', 'publish_transactions', 'read_transaction', 'read_private_transactions' ),
			'cannot' => array(),
		),
		'gardener'      => array(
			'can'    => array( 'spread_seeds', 'edit_transaction', 'edit_transactions', 'edit_other_transactions', 'edit_private_transactions', 'edit_published_transactions', 'publish_transactions', 'read_transaction', 'read_private_transactions' ),
			'cannot' => array( 'delete_transaction', 'delete_transactions', 'delete_published_transactions', 'delete_private_transactions', 'delete_others_transactions' ),
		),
		'editor'        => array(
			'can'    => array( 'edit_transaction', 'edit_transactions', 'edit_other_transactions', 'edit_private_transactions', 'edit_published_transactions', 'publish_transactions', 'read_transaction', 'read_private_transactions' ),
			'cannot' => array( 'delete_transaction', 'delete_transactions', 'delete_published_transactions', 'delete_private_transactions', 'delete_others_transactions' ),
		),
		'author'        => array(
			'can'    => array( 'edit_transaction', 'edit_transactions', 'edit_other_transactions', 'edit_private_transactions', 'edit_published_transactions', 'publish_transactions', 'read_transaction', 'read_private_transactions' ),
			'cannot' => array( 'delete_transaction', 'delete_transactions', 'delete_published_transactions', 'delete_private_transactions', 'delete_others_transactions' ),
		),
		'contributor'   => array(
			'can'    => array( 'edit_transaction', 'edit_transactions', 'edit_other_transactions', 'edit_private_transactions', 'edit_published_transactions', 'publish_transactions', 'read_transaction', 'read_private_transactions' ),
			'cannot' => array( 'delete_transaction', 'delete_transactions', 'delete_published_transactions', 'delete_private_transactions', 'delete_others_transactions' ),
		),
		'subscriber'    => array(
			'can'    => array( 'edit_transaction', 'edit_transactions', 'edit_other_transactions', 'edit_private_transactions', 'edit_published_transactions', 'publish_transactions', 'read_transaction', 'read_private_transactions' ),
			'cannot' => array( 'delete_transaction', 'delete_transactions', 'delete_published_transactions', 'delete_private_transactions', 'delete_others_transactions' ),
		),
	);

	foreach ( $roles_caps as $k => $v ) {
		$role = get_role( $k );
		foreach ( $v['can'] as $cap ) {
			$role->add_cap( $cap );
		}
		foreach ( $v['cannot'] as $cap ) {
			$role->remove_cap( $cap );
		}
	}

}
add_action( 'init', 'wps_custom_caps' );

/**
 * Show only own transactions to regular users.
 *
 * @since 1.0.0
 * @param object $wp_query The WP_Query object to alter.
 * @return void
 */
function wps_show_only_own_transactions( $wp_query ) {
	if ( current_user_can( 'administrator' ) || current_user_can( 'gardener' ) ) {
		return;
	}

	global $pagenow;
	$post_type = isset( $_GET['post_type'] ) ? sanitize_text_field( wp_unslash( $_GET['post_type'] ) ) : '';
	if ( 'edit.php' === $pagenow && 'transaction' === $post_type ) {
		$query->query_vars['meta_query'] = array(
			'relation' => 'OR',
			array(
				'key'     => 'wps_receiver',
				'value'   => get_current_user_id(),
				'compare' => '=',
			),
			array(
				'key'     => 'wps_sender',
				'value'   => get_current_user_id(),
				'compare' => '=',
			),
		);
	}
}
add_filter( 'parse_query', 'wps_show_only_own_transactions' );

/**
 * Adjust post counts
 *
 * @since 1.0.0
 * @param array $views The original view array.
 * @return array $views The updated view array.
 */
function wps_adjust_post_counts( $views ) {

	global $current_user, $wp_query;
	unset( $views['mine'] );

	$types = array(
		array( 'status' => null ),
		array( 'status' => 'publish' ),
		array( 'status' => 'draft' ),
		array( 'status' => 'pending' ),
		array( 'status' => 'trash' ),
	);

	foreach ( $types as $type ) {
		$query = array(
			'author'      => $current_user->ID,
			'post_type'   => 'post',
			'post_status' => $type['status'],
		);

		$result = new WP_Query( $query );

		if ( null === $type['status'] ) :

			$class = ( null === $wp_query->query_vars['post_status'] ) ? ' class="current"' : '';

			$views['all'] = sprintf(
				/* Translators: The count of all posts */
				__( '<a href="/%1$s" %2$s>All <span class="count">(%3$d)</span></a>', 'all' ),
				admin_url( 'edit.php?post_type=post' ),
				$class,
				$result->found_posts
			);

	elseif ( 'publish' === $type['status'] ) :

		$class = ( 'publish' === $wp_query->query_vars['post_status'] ) ? ' class="current"' : '';

		$views['publish'] = sprintf(
			/* Translators: The count of published posts */
			__( '<a href="/%1$s" %2$s>Published <span class="count">(%3$d)</span></a>', 'publish' ),
			admin_url( 'edit.php?post_status=publish&post_type=post' ),
			$class,
			$result->found_posts
		);

	elseif ( 'draft' === $type['status'] ) :

		$class = ( 'draft' === $wp_query->query_vars['post_status'] ) ? ' class="current"' : '';

		$title = ( count( $result->posts ) > 1 ) ? 'Drafts' : 'Draft';

		$views['draft'] = sprintf(
			/* Translators: The count of drafted posts */
			__( '<a href="/%1$s" %2$s>%4$s <span class="count">(%3$d)</span></a>', 'draft' ),
			admin_url( 'edit.php?post_status=draft&post_type=post' ),
			$class,
			$result->found_posts,
			$title
		);

	elseif ( 'pending' === $type['status'] ) :

		$class = ( 'pending' === $wp_query->query_vars['post_status'] ) ? ' class="current"' : '';

		$views['pending'] = sprintf(
			/* Translators: The count of pending posts */
			__( '<a href="/%1$s" %2$s>Pending <span class="count">(%3$d)</span></a>', 'pending' ),
			admin_url( 'edit.php?post_status=pending&post_type=post' ),
			$class,
			$result->found_posts
		);

	elseif ( 'trash' === $type['status'] ) :

		$class = ( 'trash' === $wp_query->query_vars['post_status'] ) ? ' class="current"' : '';

		$views['trash'] = sprintf(
			/* Translators: The count of trashed posts */
			__( '<a href="/%1$s" %2$s>Trash <span class="count">(%3$d)</span></a>', 'trash' ),
			admin_url( 'edit.php?post_status=trash&post_type=post' ),
			$class,
			$result->found_posts
		);

	endif;

	}

	return $views;

}

/**
 * Custom row actions
 *
 * @since 1.0.0
 * @param array $actions The original array with actions.
 * @return array $actions The updated array with actions.
 */
function wps_transaction_post_row_actions( $actions ) {
	if ( ! current_user_can( 'spread_seeds' ) && get_post_type() === 'transaction' ) {
		unset( $actions['edit'] );
		unset( $actions['view'] );
		unset( $actions['trash'] );
		unset( $actions['inline hide-if-no-js'] );
	}
	return $actions;
}
add_filter( 'post_row_actions', 'wps_transaction_post_row_actions', 10, 1 );
