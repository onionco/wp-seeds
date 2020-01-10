<?php
/**
 * WP Seeds 🌱
 *
 * @package   wp-seeds/tpl
 * @link      https://github.com/onionco/wp-seeds
 * @author    Mikael Lindqvist, Niels Lange & Derek Smith
 * @copyright 2020 Mikael Lindqvist, Niels Lange & Derek Smith
 * @license   GPL v2 or later
 */

defined( 'ABSPATH' ) || exit;

?>
<div class="qr-wrap qr-success wps-front-form">
	<h2><?php esc_html_e( 'Please Scan', 'wp-seeds' ); ?></h2>

	<?php if ( isset( $notice_success_1 ) ) : ?>
		<div class="notice notice-success is-dismissible">
			<p><?php echo esc_html( $notice_success_1 ); ?></p>
		</div>
	<?php endif; ?>

	<!-- <a href="<?php echo esc_attr( $qr_code_url ); ?>"> -->
		<div id="qrcode"></div>
	<!-- </a> -->
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
			<a href="<?php echo esc_html( $reader_link ); ?>" title="Download QR Reader"><?php echo esc_html_e( 'Download Here.', 'wp-seeds' ); ?></a>
		</p>
	</div>
<?php endif; ?>
