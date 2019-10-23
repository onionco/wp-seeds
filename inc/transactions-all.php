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

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Add transaction column titles.
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
 * Add transaction column content.
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
			if ( $user ) {
				echo '<a href="' . esc_html( get_edit_user_link( $user->ID ) ) . '">' . esc_attr( $user->display_name ) . '</a>';
			} else {
				esc_html_e( 'SYSTEM', 'wp-seeds' );
			}
			break;
		case 'to_user':
			$user_id = get_post_meta( $post_id, 'to_user', true );
			$user    = get_userdata( $user_id );
			if ( $user ) {
				echo '<a href="' . esc_html( get_edit_user_link( $user->ID ) ) . '">' . esc_attr( $user->display_name ) . '</a>';
			} else {
				esc_html_e( 'SYSTEM', 'wp-seeds' );
			}
			break;
		case 'amount':
			echo esc_html( get_post_meta( $post_id, 'amount', true ) );
			break;
	}
}
add_action( 'manage_transaction_posts_custom_column', 'wps_transaction_posts_custom_column', 10, 2 );

/**
 * Make transaction columns sortable
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
 * Query transaction columns.
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
 * Add transaction filter
 *
 * @since 1.0.0
 * @return void
 */
function wps_restrict_manage_posts() {
	global $typenow, $wp_query;
	$users = get_users();

	if ( 'transaction' === $typenow ) {
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
add_action( 'restrict_manage_posts', 'wps_restrict_manage_posts' );

/**
 * Add transaction query
 *
 * @param array $query The WP_Query object.
 * @return void
 */
function wps_parse_query( $query ) {
	global $pagenow;
	$post_type = isset( $_GET['post_type'] ) ? sanitize_text_field( wp_unslash( $_GET['post_type'] ) ) : '';

	if ( is_admin() && 'edit.php' === $pagenow && 'transaction' === $post_type && isset( $_GET['uid'] ) && 'all' !== $_GET['uid'] ) {
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
add_filter( 'parse_query', 'wps_parse_query' );
