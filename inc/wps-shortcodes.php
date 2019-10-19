<?php
/**
 * Implement shortcodes fro WP Seeds.
 *
 * @package WordPress
 * @subpackage WP Seeds
 * @since 1.0.0
 */

/**
 * Shortcode that creates a form to send seeds.
 *
 * @param array $args The scortcode args.
 * @return string
 */
function wps_send_sc( $args ) {
	$current_user     = wp_get_current_user();
	$vars             = array();
	$vars['show_form'] = true;
	$vars['message']  = '';

	if ( isset( $_REQUEST['seedsDoSend'] )
		&& isset( $_REQUEST['seedsSendToAccount'] )
		&& isset( $_REQUEST['seedsSendAmount'] ) ) {
		$post_id = wp_insert_post(
			array(
				'post_type' => 'transaction',
			)
		);

		update_post_meta( $post_id, 'from_user', $current_user->ID );
		update_post_meta( $post_id, 'to_user', wp_unslash( (int) $_REQUEST['seedsSendToAccount'] ) );
		update_post_meta( $post_id, 'amount', wp_unslash( (int) $_REQUEST['seedsSendAmount'] ) );

		wp_publish_post( $post_id );
		do_action( 'acf/save_post', $post_id ); // phpcs:ignore
	}

	$vars['action_url'] = get_permalink();
	$vars['users']     = array();
	$users             = get_users();
	foreach ( $users as $user ) {
		if ( $user->ID !== $current_user->ID ) {
			$vars['users'][ $user->ID ] = wps_transaction_format_user( $user );
		}
	}
	return render_template( __DIR__ . '/../tpl/wps-send-sc.tpl.php', $vars );
}
add_shortcode( 'seeds-send', 'wps_send_sc' );
