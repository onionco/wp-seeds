<?php
/**
 * WP Seeds 🌱
 *
 * @package   wp-seeds/tpl
 * @link      https://github.com/limikael/wp-seeds
 * @author    Mikael Lindqvist & Niels Lange & Derek Smith
 * @copyright 2019 Mikael Lindqvist & Niels Lange & Derek Smith
 * @license   GPL v2 or later
 */

?>

<div class="wrap wps-request-form">

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

	<div class="wrap wps-front-form">
		<h2><?php esc_html_e( 'Request Seeds', 'wp-seeds' ); ?></h2>
		<form method="post">
			<div class='wps-request-form'>
				<div class='row'>
					<label for="sender">Amount</label>
					<div class='field amount'>
						<input type="number"
								name="amount"
								value="<?php echo esc_attr( $amount ); ?>"
								class='small-text'
								autocomplete='off'/>
						<span class="description">
							<?php esc_html_e( 'How many seeds do you want to request?', 'wp-seeds' ); ?>
						</span>
					</div>
				</div>
			</div>
		<input name="do_request" type="submit" class="button button-primary" value="Request transaction"/>
		</form>
	</div>

</div>
