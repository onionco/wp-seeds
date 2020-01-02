<?php
/**
 * WP Seeds 🌱
 *
 * Template for the transaction detail page.
 *
 * @package   wp-seeds/tpl
 * @link      https://github.com/limikael/wp-seeds
 * @author    Mikael Lindqvist & Niels Lange
 * @copyright 2019 Mikael Lindqvist & Niels Lange
 * @license   GPL v2 or later
 */

?>
<div class='wrap'>
	<h2><?php esc_html_e( 'Transaction', 'wp-seeds' ); ?></h2>
	<table class='form-table'>
		<tr>
			<th><?php esc_html_e( 'Time', 'wp-seeds' ); ?></th>
			<td><?php echo esc_html( date( 'Y-m-d H:m:s', $transaction->timestamp ) ); ?></td>
		</tr>
		<tr>
			<th><?php esc_html_e( 'Transaction ID', 'wp-seeds' ); ?></th>
			<td><?php echo esc_html( $transaction->transaction_id ); ?></td>
		</tr>
		<tr>
			<th><?php esc_html_e( 'From Account', 'wp-seeds' ); ?></th>
			<td><?php echo esc_html( $user_display_by_id[ $transaction->sender ] ); ?></td>
		</tr>
		<tr>
			<th><?php esc_html_e( 'To Account', 'wp-seeds' ); ?></th>
			<td><?php echo esc_html( $user_display_by_id[ $transaction->receiver ] ); ?></td>
		</tr>
		<tr>
			<th><?php esc_html_e( 'Amount', 'wp-seeds' ); ?></th>
			<td><?php echo esc_html( $transaction->amount ); ?></td>
		</tr>
	</table>
</div>
