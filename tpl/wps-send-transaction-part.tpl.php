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

defined( 'ABSPATH' ) || exit;

?>

<div class="wrap wps-send-form">
	<h3>Send Seeds</h3>
	<?php
	$output = '';
	$cmb = 'wps_new_transaction';
	$object_id  = 'fake_object';

	$output .= cmb2_get_metabox_form( $cmb, $object_id, array( 'save_button' => __( 'Send Seeds', 'wp-seeds' ) ) );
	echo $output;
	?>
</div>

<?php
