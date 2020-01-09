<?php
/**
 * WP Seeds 🌱
 *
 * Custom functionality for send seeds page.
 *
 * @package   wp-seeds/tpl
 * @link      https://github.com/limikael/wp-seeds
 * @author    Mikael Lindqvist & Niels Lange
 * @copyright 2019 Mikael Lindqvist & Niels Lange
 * @license   GPL v2 or later
 */

?>
<div class="wrap">
	<h1><?php esc_html_e( 'New Transaction', 'wp-seeds' ); ?></h1>
	<form method="post">
		<div class='wps-admin-form'>
			<div class='row'>
				<label for="receiver">Sender</label>
				<div class='field'>
					<select name="sender">
						<option value=''><?php esc_html_e( 'Please select', 'wp-seeds' ); ?></option>
						<?php display_select_options( $user_display_by_id, get_req_var( 'sender', 0 ) ); ?>
					</select>
					<span class="description">
						<?php esc_html_e( 'Who should send the seeds?', 'wp-seeds' ); ?>
					</span>
				</div>
			</div>
			<div class='row'>
				<label for="receiver">Receiver</label>
				<div class='field'>
					<select name="receiver">
						<option value=''><?php esc_html_e( 'Please select', 'wp-seeds' ); ?></option>
						<?php display_select_options( $user_display_by_id, get_req_var( 'receiver', 0 ) ); ?>
					</select>
					<span class="description">
						<?php esc_html_e( 'Who is the receiver?', 'wp-seeds' ); ?>
					</span>
				</div>
			</div>
			<div class='row'>
				<label for="receiver">Amount</label>
				<div class='field'>
					<input type="text"
							name="amount"
							value="<?php echo esc_attr( get_req_var( 'amount', '' ) ); ?>"
							class='small-text'
							autocomplete='off'/>
					<span class="description">
						<?php esc_html_e( 'What is the amount?', 'wp-seeds' ); ?>
					</span>
				</div>
			</div>
		</div>
		<input type="submit" value="<?php esc_attr_e( 'Create Transaction' ); ?>" name="submit" class='button button-primary'/>
	</form>
</div>
