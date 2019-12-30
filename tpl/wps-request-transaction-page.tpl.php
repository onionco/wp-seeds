<?php
/**
 * WP Seeds 🌱
 *
 * @package   wp-seeds/tpl
 * @link      https://github.com/limikael/wp-seeds
 * @author    Mikael Lindqvist & Niels Lange
 * @copyright 2019 Mikael Lindqvist & Niels Lange
 * @license   GPL v2 or later
 */

?>
<div class="wrap">

	<h3>Request Transaction</h3>

	<?php if ( isset( $notice_success ) ) : ?>
		<div class="notice notice-success is-dismissible">
			<p><?php echo esc_html( $notice_success ); ?></p>
		</div>
	<?php endif; ?>

	<?php if ( isset( $notice_error ) ) : ?>
		<div class="notice notice-error is-dismissible">
			<p><?php echo esc_html( $notice_error ); ?></p>
		</div>
	<?php endif; ?>

	<form method="post" class="form-table" action="<?php echo esc_attr( $action ); ?>">
		<table>
			<tr>
				<th scope="row">
					<label>Amount</label>
				</th>
				<td>
					<input type="number" class="regular-text" name="amount" value="<?php echo esc_attr( $amount ); ?>"/>
					<p class="description">How many seeds do you want to request?</p>
				</td>
			</tr>
		</table>
		<p class="submit">
			<input name="do_request" type="submit" class="button button-primary" value="Request transaction"/>
		</p>
	</form>

</div>
