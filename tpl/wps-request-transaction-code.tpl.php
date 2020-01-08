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
<div class="qr-wrap qr-success wps-front-form">
	<h2><?php esc_html_e( 'Please Scan', 'wp-seeds' ); ?></h2>

	<?php if ( isset( $notice_success_1 ) ) : ?>
		<div class="notice notice-success is-dismissible">
			<p><?php echo esc_html( $notice_success_1 ); ?></p>
		</div>
	<?php endif; ?>

	<div id="qrcode"></div>
	<script type="text/javascript">
		new QRCode(document.getElementById("qrcode"),<?php echo wp_json_encode( $qr_code_url ); ?>); 
	</script>

	<?php if ( isset( $notice_success_2 ) ) : ?>
		<div class="notice notice-end is-dismissible">
			<p><?php echo esc_html( $notice_success_2 ); ?></p>
		</div>
	<?php endif; ?>

</div>

<?php if ( isset( $reader_prompt ) && isset( $reader_link ) ) : ?>
	<div class="notice qr-reader is-dismissible">
		<p><?php echo esc_html( $reader_prompt ); ?>
			<a href="<?php echo esc_html( $reader_link ); ?>" title="Download QR Reader"><?php echo __('Download Here.'); ?></a>
		</p>
	</div>
<?php endif; ?>