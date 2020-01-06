<?php
/**
 * WP Seeds 🌱
 *
 * @package   wp-seeds
 * @link      https://github.com/limikael/wp-seeds
 * @author    Mikael Lindqvist, Niels Lange & Derek Smith
 * @copyright 2019 Mikael Lindqvist, Niels Lange & Derek Smith
 * @license   GPL v2 or later

 * Plugin Name:       WP Seeds
 * Plugin URI:        https://github.com/limikael/wp-seeds
 * Description:       Allows users to hold, send and receive tokens named seeds.
 * Version:           3.0
 * Requires at least: 5.2
 * Requires PHP:      7.2
 * Author:            Mikael Lindqvist, Niels Lange & Derek Smith
 * Author URI:        https://www.theonionco.com
 * Text Domain:       wp-seeds
 * License:           GPL v2 or later
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 */

/**
 * Include required classes and files.
 *
 * @since 1.0.0
 */
require_once plugin_dir_path( __FILE__ ) . '/inc/class-transaction.php';
require_once plugin_dir_path( __FILE__ ) . '/inc/class-custom-list-table.php';
require_once plugin_dir_path( __FILE__ ) . '/inc/lib.php';
require_once plugin_dir_path( __FILE__ ) . '/inc/class-wps-form-exception.php';
require_once plugin_dir_path( __FILE__ ) . '/inc/wps-admin.php';
require_once plugin_dir_path( __FILE__ ) . '/inc/wps-frontend.php';

/**
 * Enqueue styles.
 *
 * @return void
 */
function wps_enqueue_style() {
	global $post;
	if ( ( isset( $post->post_content ) && has_shortcode( $post->post_content, 'seeds_send' ) ) || ( isset( $post->post_content ) && has_shortcode( $post->post_content, 'seeds_receive' ) ) ) {
		wp_enqueue_style( 'front-styles', plugin_dir_url( __FILE__ ) . '/front-styles.css', null, '1.0', 'screen' );
	}
	if ( ( isset( $post->post_content ) && has_shortcode( $post->post_content, 'seeds_send' ) ) ) {
		wp_enqueue_script( 'qr-generator', plugin_dir_url( __FILE__ ) . 'ext/qrcodejs/qrcode.js', 'jquery', '0.0.1', false );
	}
}
add_action( 'wp_enqueue_scripts', 'wps_enqueue_style' );

/**
 * Handle plugin activation.
 *
 * @return void
 */
function wps_activate() {
	Transaction::install();
}
register_activation_hook( __FILE__, 'wps_activate' );

/**
 * Handle plugin deactivation.
 *
 * @return void
 */
function wps_deactivate() {
	Transaction::uninstall();
}
register_deactivation_hook( __FILE__, 'wps_deactivate' );

/**
 * Get the url where to find cmb2 resources. We hook into this function
 * because the original implementation of cmb2 fails if the plugin is inside
 * a sym-linked directory.
 *
 * @param string $url The auto generated url.
 * @since 1.0.0
 * @return string
 */
function wps_cmb2_meta_box_url( $url ) {
	$new_url = trailingslashit( plugin_dir_url( __FILE__ ) . 'ext/cmb2' );

	return $new_url;
}
add_filter( 'cmb2_meta_box_url', 'wps_cmb2_meta_box_url' );


/**
 * Send Seeds Shortcode.
 *
 * @param array $atts The shortcode attributes.
 */
function request_seeds_form_shortcode( $atts = array() ) {
	global $post;

	/**
	 * Depending on your setup, check if the user has permissions to edit_posts
	 */
	if ( ! is_user_logged_in() ) {
		return __( 'You do not have permissions to be here.', 'lang_domain' );
	}

	$user_id = get_current_user_id();
	$user_meta = get_user_meta( $user_id );

	$user_info = get_userdata( $user_id );
	$user_email = $user_info->user_email;

	$user_first = $user_meta['first_name'][0];
	$user_last = $user_meta['last_name'][0];
	$user_balance = $user_meta['seeds_balance'][0];
	?>
	<div class="seeds">

		<h2>Welcome <?php echo $user_first; ?> <?php echo $user_last; ?></h2>

		<div class="seeds-balance">
			<p>Your Current Balance is:</p>
			<p class="CurrSeeds"><?php echo "{$user_balance} Seed" . ( $user_balance == 1 ? '' : 's' ); ?></p>
		</div>

		<?php
		$vars    = array();
		$show_qr = false;

		if ( isset( $_REQUEST['do_request'] ) ) {
			if ( ! empty( $_REQUEST['amount'] ) ) {
				$to_user                = (int) get_current_user_id();
				$amount                 = (int) $_REQUEST['amount'];
				$home                   = get_site_url();
				$vars['notice_success'] = __( 'Your QR has been created successfully. Please ask the sender to scan this QR code to transfer seeds to you.', 'wp-seeds' );
				$vars['qr_code_url']    = sprintf( '%3$s/transactions?to_user=2&amount=1', $to_user, $amount, $home );
				$show_qr                = true;

			} else {
				$vars['notice_error'] = __( 'Please provide an amount to request.', 'wp-seeds' );
			}
		}

		if ( $show_qr ) {
			display_template( dirname( __FILE__ ) . '/tpl/wps-request-transaction-code.tpl.php', $vars );
		} else {
			display_template( dirname( __FILE__ ) . '/tpl/wps-request-transaction-page.tpl.php', $vars );
		}
		?>


	</div>

	<?php

}
add_shortcode( 'seeds_receive', 'request_seeds_form_shortcode' );


/**
 * Receive Seeds Shortcode.
 *
 * @param array $atts The shortcode attrs.
 * @return void
 */
function send_seeds_form_shortcode( $atts = array() ) {
	global $post;

	/**
	 * Depending on your setup, check if the user has permissions to edit_posts
	 */
	if ( ! is_user_logged_in() ) {
		return __( 'You do not have permissions to be here.', 'lang_domain' );
	}

	$user_id = get_current_user_id();
	$user_meta = get_user_meta( $user_id );

	$user_info = get_userdata( $user_id );
	$user_email = $user_info->user_email;

	$user_first = $user_meta['first_name'][0];
	$user_last = $user_meta['last_name'][0];
	$user_balance = $user_meta['seeds_balance'][0];

	// var_dump($user_meta);
	// var_dump($user_info);

	?>

	<div class="seeds">

		<h2>Welcome <?php echo $user_first; ?> <?php echo $user_last; ?></h2>

		<div class="seeds-balance">
			<p>Your Current Balance is:</p>
			<p class="CurrSeeds"><?php echo "{$user_balance} Seed" . ( $user_balance == 1 ? '' : 's' ); ?></p>
		</div>

	</div>
	
	<?php

}
add_shortcode( 'seeds_send', 'send_seeds_form_shortcode' );

/**
 * Load admin styles.
 *
 * @since 1.0.0
 * @return void
 */
function wps_admin_style() {
	wp_enqueue_style( 'admin-styles', plugin_dir_url( __FILE__ ) . '/admin.css', null, '1.3', 'screen' );
	wp_enqueue_style( 'cmb2-styles-css', plugin_dir_url( __FILE__ ) . '/ext/cmb2/css/cmb2.min.css', null, '5.3.2', 'screen' );
}
add_action( 'admin_enqueue_scripts', 'wps_admin_style' );
