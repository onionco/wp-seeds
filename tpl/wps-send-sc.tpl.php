<?php
/**
 * WP Seeds 🌱
 *
 * Template for the user facing send seeds from.
 *
 * @package   wp-seeds/tpl
 * @link      https://github.com/limikael/wp-seeds
 * @author    Mikael Lindqvist & Niels Lange
 * @copyright 2019 Mikael Lindqvist & Niels Lange
 * @license   GPL v2 or later
 */

?>
<?php $v->echo_messages(); ?>

<?php if ( $show_form ) : ?>
	<form class="seeds-send-form" method="post" action="<?php echo esc_attr( $action_url ); ?>">
		<input type="hidden" name="seedsDoSend" value="1"/>

		<p class="seeds-send-form-field-container">
			<label>To Account</label>
			<div class="seeds-send-form-field">
				<select name="to_user">
					<option><?php esc_html_e( 'Select user to receive the seeds', 'wp-seeds' ); ?></option>
					<?php display_select_options( $users, $v->get_unchecked( 'wps_receiver' ) ); ?>
				</select>
			</div>
		</p>

		<p class="seeds-send-form-field-container">
			<label>Amount</label>
			<div class="seeds-send-form-field">
				<input type="text" name="amount" 
						value="<?php $v->echo_esc_attr_unchecked( 'wps_amount' ); ?>">
			</div>
		</p>

		<p class="seeds-send-form-submit-container">
			<input type="submit" value="Send Seeds"/>
		</p>
	</form>

<?php endif; ?>
