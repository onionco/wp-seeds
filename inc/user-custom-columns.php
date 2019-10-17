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
 * @param array $column The column to add data to.
 * @param int   $post_id The user id.
 * @return void
 */
function wps_user_posts_custom_column( $output, $column, $post_id ) {
	global $post;

	switch ( $column ) {

		case 'balance':
			return get_user_meta( $post_id, 'wps_balance', true );
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
	$columns['from_user'] = 'from_user';
	$columns['to_user']   = 'to_user';
	$columns['amount']    = 'amount';

	return $columns;
}
add_filter( 'manage_edit-user_sortable_columns', 'wps_user_sortable_columns' );

/**
 * Query user columns.
 *
 * @since 1.0.0
 * @param object $query The WP_Query object.
 * @return void
 */
function wps_user_pre_get_posts( $query ) {
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
add_action( 'pre_get_posts', 'wps_user_pre_get_posts' );

/**
 * Add user filter
 *
 * @since 1.0.0
 * @return void
 */
function wps_user_restrict_manage_posts() {
	global $typenow, $wp_query;
	$users = get_users();

	if ( 'user' === $typenow ) {
		if ( isset( $_GET['uid'] ) ) {
			$user_id = (int) $_GET['uid'];
		} ?>
		<select name="uid" id="filter-by-user-id">
			<option value="all" <?php selected( 'all', $user_id ); ?>><?php esc_html_e( 'All users', 'wp-seeds' ); ?></option>
				<?php foreach ( $users as $user ) { ?>
					<option value="<?php echo esc_attr( $user->ID ); ?>" <?php selected( $user->ID, $user_id ); ?>><?php echo esc_attr( $user->display_name ); ?></option>
				<?php } ?>
			</select>
		<?php
	}
}
add_action( 'restrict_manage_posts', 'wps_user_restrict_manage_posts' );

/**
 * Add user query
 *
 * @param array $query The WP_Query object.
 * @return void
 */
function wps_user_parse_query( $query ) {
	global $pagenow;
	$post_type = isset( $_GET['post_type'] ) ? sanitize_text_field( wp_unslash( $_GET['post_type'] ) ) : '';

	if ( is_admin() && 'edit.php' === $pagenow && 'user' === $post_type && isset( $_GET['uid'] ) && 'all' !== $_GET['uid'] ) {
		$query->query_vars['meta_query'] = array(
			'relation' => 'OR',
			array(
				'key'     => 'to_user',
				'value'   => sanitize_text_field( wp_unslash( $_GET['uid'] ) ),
				'compare' => '=',
			),
			array(
				'key'     => 'from_user',
				'value'   => sanitize_text_field( wp_unslash( $_GET['uid'] ) ),
				'compare' => '=',
			),
		);
	}
}
add_filter( 'parse_query', 'wps_user_parse_query' );
