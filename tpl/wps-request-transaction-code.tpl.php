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
<div class="qr-wrap">

	<h3>Request Transaction</h3>

	<?php if ( isset( $notice_success ) ) : ?>
		<div class="notice notice-success is-dismissible">
			<p><?php echo esc_html( $notice_success ); ?></p>
		</div>
	<?php endif; ?>

	<div id="qrcode"></div>
	<script type="text/javascript">
		new QRCode(document.getElementById("qrcode"), "<?php echo $qr_code_url; ?>"); 
	</script>

</div>
