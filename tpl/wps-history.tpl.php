<?php
/**
 * WP Seeds 🌱
 *
 * Custom functionality for transactions overview page.
 *
 * @package   wp-seeds/tpl
 * @link      https://github.com/limikael/wp-seeds
 * @author    Mikael Lindqvist & Niels Lange
 * @copyright 2019 Mikael Lindqvist & Niels Lange
 * @license   GPL v2 or later
 */

?>
<div class="wps-history-container">
	<table class="wps-history-table">
		<tr>
			<th>Transaction ID</th>
			<th>To / From</th>
			<th>Amount</th>
		</tr>

		<?php foreach ( $transactions as $transaction ) { ?>
			<tr>
				<td>
					<?php echo esc_html( $transaction['id'] ); ?>
				</td>
				<td>
					<?php echo esc_html( $transaction['user'] ); ?>
				</td>
				<td>
					<?php echo esc_html( $transaction['amount'] ); ?>
				</td>
			</tr>
		<?php } ?>
	</table>
</div>
