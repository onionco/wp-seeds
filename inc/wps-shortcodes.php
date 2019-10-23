<?php
/**
 * Implement shortcodes fro WP Seeds.
 *
 * @package WordPress
 * @subpackage WP Seeds
 * @since 1.0.0
 */

/**
 * Required files.
 */
require_once __DIR__ . '/../classes/class-wps-form-validator.php';

/**
 * Shortcode that creates a form to send seeds.
 *
 * @param array $args The scortcode args.
 * @return string
 */
function wps_send_sc( $args ) {
	$current_user       = wp_get_current_user();
	$vars               = array();
	$vars['show_form']  = true;
	$vars['users']      = array();
	$vars['action_url'] = get_permalink();
	$users              = get_users();
	foreach ( $users as $user ) {
		if ( $user->ID !== $current_user->ID ) {
			$vars['users'][ $user->ID ] = wps_transaction_format_user( $user );
		}
	}

	$v         = new WPS_Form_Validator();
	$vars['v'] = $v;
	$v->check_wp_user_id( 'wps_receiver' );
	$v->check_positive_number( 'wps_amount' );

	if ( $v->is_valid_submission() ) {
		$post_id = wp_insert_post( array( 'post_type' => 'transaction' ) );
		update_post_meta( $post_id, 'wps_sender', $current_user->ID );
		update_post_meta( $post_id, 'wps_receiver', $v->get_checked( 'wps_receiver' ) );
		update_post_meta( $post_id, 'wps_amount', $v->get_checked( 'wps_amount' ) );
		try {
			wps_process_transaction( $post_id );
			$v->done( __( 'The seeds have been sent.', 'wp-seeds' ) );
			$vars['show_form'] = false;
		} catch ( Exception $e ) {
			$v->trigger( $e->getMessage() );
			wp_delete_post( $post_id, true );
		}
	}

	return render_template( __DIR__ . '/../tpl/wps-send-sc.tpl.php', $vars );
}
add_shortcode( 'seeds-send', 'wps_send_sc' );
