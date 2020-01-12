<?php
/**
 * WP Seeds 🌱
 *
 * Custom functionality for transactions overview page.
 *
 * @package   wp-seeds/tpl
 * @link      https://github.com/onionco/wp-seeds
 * @author    Mikael Lindqvist, Niels Lange & Derek Smith
 * @copyright 2020 Mikael Lindqvist, Niels Lange & Derek Smith
 * @license   GPL v2 or later
 */

defined( 'ABSPATH' ) || exit;

?>
<div class="wps-history-container">
	<table class="wps-history-table">
		<thead>
			<tr>
				<th><?php echo esc_html_e( 'Time of Transaction', 'wp-seeds' ); ?></th>
				<th><?php echo esc_html_e( 'Transaction ID', 'wp-seeds' ); ?></th>
				<th><?php echo esc_html_e( 'To / From', 'wp-seeds' ); ?></th>
				<th class="amount"><?php echo esc_html_e( 'Seeds', 'wp-seeds' ); ?></th>
			</tr>
		</thead>

		<tbody>
			<?php foreach ( $transactions as $transaction ) { ?>
				<tr>
					<td>
						<?php echo esc_html( $transaction['timestamp'] ); ?>
					</td>
					<td>
						<?php echo esc_html( $transaction['id'] ); ?>
					</td>
					<td>
						<?php echo esc_html( $transaction['user'] ); ?>
					</td>
					<td class="amount">
						<?php echo esc_html( $transaction['amount'] ); ?>
					</td>
				</tr>
			<?php } ?>
		</tbody>
	</table>
</div>
