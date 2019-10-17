<?php
/**
 * Users » Your Profile
 *
 * Custom functionality for profile page.
 *
 * @package WordPress
 * @subpackage WP Seeds
 * @since 1.0.0
 */

/**
 * Undocumented function
 *
 * @param object $user The user object.
 * @return void
 */
function wps_show_user_profile( $user ) {
	$balance = get_user_meta( $user->ID, 'wps_balance', true );
	?>
	<h3><?php esc_html_e( 'WP Seeds 🌱', 'wp-seeds' ); ?></h3>

	<table class="form-table">
		<tr>
			<th><?php esc_html_e( 'Balance', 'wp-seeds' ); ?></th>
			<td><?php echo sprintf( '<a href="%s/wp-admin/edit.php?s&post_status=all&post_type=transaction&action=-1&m=0&uid=%d&filter_action=Filter&paged=1&action2=-1">%s</a>', esc_html( get_admin_url() ), (int) $user->ID, (int) $balance ); ?></td>
		</tr>
	</table>
	<?php
}
add_action( 'show_user_profile', 'wps_show_user_profile' );
add_action( 'edit_user_profile', 'wps_show_user_profile' );
