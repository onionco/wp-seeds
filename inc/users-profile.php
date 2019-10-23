<?php
/**
 * WP Seeds 🌱
 *
 * Custom functionality for profile page.
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
 * Show user balance.
 *
 * @param object $user The user object.
 * @return void
 */
function wps_show_balance( $user ) {
	$balance = get_user_meta( $user->ID, 'wps_balance', true );
	?>
	<h3><?php esc_html_e( 'WP Seeds 🌱', 'wp-seeds' ); ?></h3>
	<table class="form-table">
		<tr>
			<th><?php esc_html_e( 'Balance', 'wp-seeds' ); ?></th>
			<?php if ( $balance > 0 ) : ?>
			<td><?php echo sprintf( '<a href="%s/wp-admin/edit.php?s&post_status=all&post_type=transaction&action=-1&m=0&uid=%d&filter_action=Filter&paged=1&action2=-1">%s</a>', esc_html( get_admin_url() ), (int) $user->ID, (int) $balance ); ?></td>
			<?php else : ?>
			<td><?php echo 0; ?></td>
			<?php endif; ?>
		</tr>
	</table>
	<?php
}
add_action( 'show_user_profile', 'wps_show_balance' );
add_action( 'edit_user_profile', 'wps_show_balance' );
