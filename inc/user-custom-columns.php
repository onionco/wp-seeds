<?php
/**
 * User custom columns
 *
 * @package WordPress
 * @subpackage WP Seeds
 * @since 1.0.0
 */

/**
 * Add user column titles.
 *
 * @since 1.0.0
 * @param array $columns The original array with columns.
 * @return array $columns The updated array with columns.
 */
function wps_user_columns( $columns ) {

	// @todo make it better! ¯\_(ツ)_/¯
	$columns = array(
		'cb'       => $columns['cb'],
		'username' => __( 'Username' ),
		'name'     => __( 'Name' ),
		'email'    => __( 'Email' ),
		'balance'  => __( 'Balance' ),
		'role'     => __( 'Role' ),
		'posts'    => __( 'Posts' ),
	);

	return $columns;
}
add_filter( 'manage_users_columns', 'wps_user_columns' );

/**
 * Add user column content.
 *
 * @since 1.0.0
 * @param array $output The array with user data.
 * @param array $column The column to add data to.
 * @param int   $post_id The user id.
 * @return mixed The single user data or array with user data.
 */
function wps_user_posts_custom_column( $output, $column, $post_id ) {
	global $post;

	switch ( $column ) {

		case 'balance':
			$balance = get_user_meta( $post_id, 'wps_balance', true );
			if ( ! empty( $balance ) ) {
				return sprintf( '<a href="/wp-admin/edit.php?s&post_status=all&post_type=transaction&action=-1&m=0&uid=%d&filter_action=Filter&paged=1&action2=-1">%s</a>', $post_id, $balance );
			}
			break;
	}
	return $output;
}
add_filter( 'manage_users_custom_column', 'wps_user_posts_custom_column', 10, 3 );

/**
 * Make user columns sortable
 *
 * @since 1.0.0
 * @param array $columns The original array with columns.
 * @return array $columns The updated array with columns.
 */
function wps_user_sortable_columns( $columns ) {
	$columns['balance'] = 'balance';

	return $columns;
}
add_filter( 'manage_users_sortable_columns', 'wps_user_sortable_columns' );

/**
 * Query user columns.
 *
 * @since 1.0.0
 * @param object $query The WP_Query object.
 * @return void
 */
function wps_user_pre_get_users( $query ) {
	if ( ! is_admin() ) {
		return;
	}

	$orderby = $query->get( 'orderby' );

	if ( 'balance' === $orderby ) {
		$query->set( 'meta_key', 'wps_balance' );
		$query->set( 'orderby', 'meta_value_num' );
	}
}
add_action( 'pre_get_users', 'wps_user_pre_get_users' );
