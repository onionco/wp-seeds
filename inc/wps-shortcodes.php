<?php
/**
 * Implement shortcodes fro WP Seeds.
 *
 * @package WordPress
 * @subpackage WP Seeds
 * @since 1.0.0
 */

function wps_send_sc( $args ) {
	$current_user = wp_get_current_user();
	$vars = array();
	$vars['showForm'] = true;
	$vars['message']  = '';

	if ($_REQUEST["seedsDoSend"]) {
		$post_id=wp_insert_post(array(
			"post_type" => "transaction"
		));

		update_post_meta($post_id, 'from_user', $current_user->ID);
		update_post_meta($post_id, 'to_user', $_REQUEST["seedsSendToAccount"]);
		update_post_meta($post_id, 'amount', $_REQUEST["seedsSendAmount"]);

		wp_publish_post($postId);
		do_action( 'acf/save_post', $post_id);
	}

	$vars['actionUrl'] = get_permalink();
	$vars['users'] = array();
	$users         = get_users();
	foreach ( $users as $user ) {
		if ( $user->ID != $current_user->ID ) {
			$vars['users'][ $user->ID ] = wps_transaction_format_user( $user );
		}
	}
	return render_template( __DIR__ . '/../tpl/wps-send-sc.tpl.php', $vars );
}
add_shortcode( 'seeds-send', 'wps_send_sc' );