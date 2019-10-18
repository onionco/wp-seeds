<?php
/**
 * WP Seeds 🌱
 *
 * @package   wp-seeds
 * @link      https://github.com/onionco/wp-seeds
 * @author    Mikael Lindqvist & Niels Lange
 * @copyright 2019 Mikael Lindqvist & Niels Lange
 * @license   GPL v2 or later
 */

?>
<div class="wrap">

	<h1>Request Transaction</h1>

	<?php if ( isset( $notice_success ) ) : ?>
		<div class="notice notice-success is-dismissible">
			<p><?php echo esc_html( $notice_success ); ?></p>
		</div>
	<?php endif; ?>

	<?php printf( '<p><img src="https://chart.googleapis.com/chart?cht=qr&chs=300x300&chl=%s"></p>', esc_html( $qr_code_url ) ); ?>

</div>
