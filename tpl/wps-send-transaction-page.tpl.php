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
<h3>Send Seeds</h3>
<?php
	$output = '';
	$cmb = 'wps_new_transaction';
	$object_id  = 'wps_new_transaction';

	$output .= cmb2_get_metabox_form( $cmb, $object_id, array( 'save_button' => __( 'Send Seeds', 'wp-seeds' ) ) );
	echo $output;
?>
</div>
