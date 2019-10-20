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
	$v->check_wp_user_id( 'to_user' );
	$v->check_positive_number( 'amount' );

	if ( $v->is_valid_submission() ) {
		$post_id = wp_insert_post(
			array(
				'post_type' => 'transaction',
			)
		);

		update_post_meta( $post_id, 'from_user', $current_user->ID );
		update_post_meta( $post_id, 'to_user', $v->get_checked( 'to_user' ) );
		update_post_meta( $post_id, 'amount', $v->get_checked( 'amount' ) );

		wp_publish_post( $post_id );
		do_action( 'acf/save_post', $post_id ); // phpcs:ignore

		$v->done( __( 'The seeds have been sent!', 'wp-seeds' ) );
		$vars['show_form'] = false;
	}

	return render_template( __DIR__ . '/../tpl/wps-send-sc.tpl.php', $vars );
}
add_shortcode( 'seeds-send', 'wps_send_sc' );
