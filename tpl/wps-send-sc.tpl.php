<?php
/**
 * WP Seeds 🌱
 *
 * @package   wp-seeds
 * @link      https://github.com/limikael/wp-seeds
 * @author    Mikael Lindqvist & Niels Lange
 * @copyright 2019 Mikael Lindqvist & Niels Lange
 * @license   GPL v2 or later
 */

?>
<?php if ( isset( $message ) ) : ?>
	<i><?php echo esc_html( $message ); ?></i>
<?php endif; ?>

<?php if ( $showForm ) : ?>
	<form class="seeds-send-form" method="post" action="<?php echo esc_html( $actionUrl ); ?>">
		<input type="hidden" name="seedsDoSend" value="1"/>

		<p class="seeds-send-form-field-container">
			<label>To Account</label>
			<div class="seeds-send-form-field">
				<select name="seedsSendToAccount">
					<?php display_select_options( $users ); ?>
				</select>
			</div>
		</p>

		<p class="seeds-send-form-field-container">
			<label>Amount</label>
			<div class="seeds-send-form-field">
				<input type="text" name="seedsSendAmount"/>
			</div>
		</p>

		<p class="seeds-send-form-submit-container">
			<input type="submit" value="Send Seeds"/>
		</p>
	</form>
<?php endif; ?>
