<?php
/**
 * WP Seeds 🌱
 *
 * @package   wp-seeds
 * @link      https://github.com/onionco/wp-seeds
 * @author    Mikael Lindqvist, Niels Lange & Derek Smith
 * @copyright 2020 Mikael Lindqvist, Niels Lange & Derek Smith
 * @license   GPL v2 or later

 * Plugin Name:       WP Seeds
 * Plugin URI:        https://github.com/limikael/wp-seeds
 * GitHub Plugin URI: https://github.com/onionco/wp-seeds
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

defined( 'ABSPATH' ) || exit;

/**
 * Include required classes and files.
 *
 * @since 1.0.0
 */
require_once plugin_dir_path( __FILE__ ) . '/inc/class-wps-transaction.php';
require_once plugin_dir_path( __FILE__ ) . '/inc/class-custom-list-table.php';
require_once plugin_dir_path( __FILE__ ) . '/inc/lib.php';
require_once plugin_dir_path( __FILE__ ) . '/inc/class-wps-form-exception.php';
require_once plugin_dir_path( __FILE__ ) . '/inc/wps-admin.php';
require_once plugin_dir_path( __FILE__ ) . '/inc/wps-public.php';


/**
 * Handle plugin activation.
 *
 * @return void
 */
function wps_activate() {
	WPS_Transaction::install();
}
register_activation_hook( __FILE__, 'wps_activate' );

/**
 * Handle plugin uninstall.
 *
 * @return void
 */
function wps_uninstall() {
	WPS_Transaction::uninstall();
}
register_uninstall_hook( __FILE__, 'wps_uninstall' );

/**
 * Load styles.
 *
 * @since 1.0.0
 * @return void
 */
function wps_enqueue_styles() {
	wp_enqueue_style( 'admin-styles', plugin_dir_url( __FILE__ ) . '/inc/css/admin.css', null, '1.5', 'screen' );
}
add_action( 'admin_enqueue_scripts', 'wps_enqueue_styles' );


if ( is_admin() ) {
	require_once plugin_dir_path( __FILE__ ) . '/inc/wps-admin.php';
}
if ( ! is_admin() ) {
	require_once plugin_dir_path( __FILE__ ) . '/inc/wps-public.php';
}


/**
 * Create gardener role on activation.
 */
function wps_add_roles_on_activation() {
	add_role(
		'wps_gardener',
		'Gardener',
		array(
			'read' => true,
			'edit_posts' => false,
			'delete_posts' => false,
		)
	);
}
register_activation_hook( __FILE__, 'wps_add_roles_on_activation' );

/**
 * Create custom role capabilities on activation.
 */
function wps_custom_role_caps() {

	$gardener = get_role( 'wps_gardener' );
	$gardener->add_cap( 'wps_transfer_seeds_universally' );
	$gardener->add_cap( 'wps_view_all_transactions' );

	$admin = get_role( 'administrator' );
	$admin->add_cap( 'wps_create_burn_seeds' );
	$admin->add_cap( 'wps_transfer_seeds_universally' );
	$admin->add_cap( 'wps_view_all_transactions' );

}
register_activation_hook( __FILE__, 'wps_custom_role_caps' );


/**
 * Remove custom role capabilities on deactivation.
 */
function wps_remove_custom_role() {
	if ( get_role( 'wps_gardener' ) ) {
		remove_role( 'wps_gardener' );
	}

	$admin = get_role( 'administrator' );

	$caps = array(
		'wps_create_burn_seeds',
		'wps_transfer_seeds_universally',
		'wps_view_all_transactions',
	);

	foreach ( $caps as $cap ) {
		$admin->remove_cap( $cap );
	}

}
register_deactivation_hook( __FILE__, 'wps_remove_custom_role' );


/**
 * Create account pages on activation
 */
define( 'WPSEEDS_PLUGIN_FILE', __FILE__ );
register_activation_hook( WPSEEDS_PLUGIN_FILE, 'create_account_page' );

/**
 * Create pages that the plugin relies on, storing page IDs in variables.
 */
function create_account_page() {

	$pages = array(
		'wpsaccount' => array(
			'name'    => _x( 'seeds-account', 'Page slug', 'wpseeds' ),
			'title'   => _x( 'Seeds Account', 'Page title', 'wpseeds' ),
			'content' => '[seeds-account]',
		),
	);

	foreach ( $pages as $key => $page ) {
		wps_create_page( esc_sql( $page['name'] ), 'wpseeds_' . $key . '_page_id', $page['title'], $page['content'], ! empty( $page['parent'] ) ? $page['parent'] : '' );
	}
}

/**
 * Create seeds pages on installation.
 *
 * @param string $slug The slug for the page.
 * @param string $option The option.
 * @param string $page_title The title for the page.
 * @param string $page_content The content for the page.
 * @param string $post_parent The parent.
 *
 * @return int The page id.
 */
function wps_create_page( $slug, $option = '', $page_title = '', $page_content = '', $post_parent = 0 ) {

	if ( ! current_user_can( 'activate_plugins' ) ) {
		return;
	}

	global $wpdb;

	$option_value = get_option( $option );

	if ( $option_value > 0 ) {
		$page_object = get_post( $option_value );

		if ( $page_object && 'page' === $page_object->post_type && ! in_array( $page_object->post_status, array( 'pending', 'trash', 'future', 'auto-draft' ), true ) ) {
			// Valid page is already in place.
			return $page_object->ID;
		}
	}

	if ( strlen( $page_content ) > 0 ) {
		// Search for an existing page with the specified page content (typically a shortcode).
		$shortcode = str_replace( array( '<!-- wp:shortcode -->', '<!-- /wp:shortcode -->' ), '', $page_content );
		$valid_page_found = $wpdb->get_var( $wpdb->prepare( "SELECT ID FROM $wpdb->posts WHERE post_type='page' AND post_status NOT IN ( 'pending', 'trash', 'future', 'auto-draft' ) AND post_content LIKE %s LIMIT 1;", "%{$shortcode}%" ) );
	} else {
		// Search for an existing page with the specified page slug.
		$valid_page_found = $wpdb->get_var( $wpdb->prepare( "SELECT ID FROM $wpdb->posts WHERE post_type='page' AND post_status NOT IN ( 'pending', 'trash', 'future', 'auto-draft' )  AND post_name = %s LIMIT 1;", $slug ) );
	}

	if ( $valid_page_found ) {
		if ( $option ) {
			update_option( $option, $valid_page_found );
		}
		return $valid_page_found;
	}

	// Search for a matching valid trashed page.
	if ( strlen( $page_content ) > 0 ) {
		// Search for an existing page with the specified page content (typically a shortcode).
		$trashed_page_found = $wpdb->get_var( $wpdb->prepare( "SELECT ID FROM $wpdb->posts WHERE post_type='page' AND post_status = 'trash' AND post_content LIKE %s LIMIT 1;", "%{$page_content}%" ) );
	} else {
		// Search for an existing page with the specified page slug.
		$trashed_page_found = $wpdb->get_var( $wpdb->prepare( "SELECT ID FROM $wpdb->posts WHERE post_type='page' AND post_status = 'trash' AND post_name = %s LIMIT 1;", $slug ) );
	}

	if ( $trashed_page_found ) {
		$page_id   = $trashed_page_found;
		$page_data = array(
			'ID'          => $page_id,
			'post_status' => 'publish',
		);
		wp_update_post( $page_data );
	} else {
		$page_data = array(
			'post_status'    => 'publish',
			'post_type'      => 'page',
			'post_author'    => 1,
			'post_name'      => $slug,
			'post_title'     => $page_title,
			'post_content'   => $page_content,
			'post_parent'    => $post_parent,
			'comment_status' => 'closed',
		);
		$page_id   = wp_insert_post( $page_data );
	}

	if ( $option ) {
		update_option( $option, $page_id );
	}

	return $page_id;
}


/**
 * Flush permalinks
 */
register_deactivation_hook( __FILE__, 'flush_rewrite_rules' );
register_activation_hook( __FILE__, 'flush_rewrite_rules' );


if ( ! function_exists( 'is_wpsaccount_page' ) ) {

	/**
	 * Is_account_page - Returns true when viewing an account page.
	 *
	 * @return bool
	 */
	function is_wpseeds_page() {
		global $post;
		if ( is_a( $post, 'WP_Post' ) && has_shortcode( $post->post_content, 'seeds-account' ) ) {
			$seeds_account = true;
			set_query_var( 'wpsaccount', true );
		} else {
			$seeds_account = '';
		}
		// $seeds_account = intval( get_query_var( 'wpsaccount' ) );
		$send_seeds = intval( get_query_var( 'wpssend' ) );
		$request_seeds = intval( get_query_var( 'wpsrequest' ) );

		if ( $seeds_account || $send_seeds || $request_seeds ) {
			return true;
		} else {
			return false;
		}
	}
}

/**
 * Add body class by listening to the body_class filter.
 *
 * @param array $classes The current classes.
 * @return array The resulting classes.
 */
function wpseeds_body_class( $classes ) {

	global $post;

	if ( is_wpseeds_page() ) {
		$classes[] = 'wpseeds';
	}
	return $classes;
}
add_filter( 'body_class', 'wpseeds_body_class' );


/**
 * Enqueue styles.
 *
 * @return void
 */
function wps_enqueue_style() {
	global $post;
	if ( is_wpseeds_page() ) {
		wp_enqueue_style( 'front-styles', plugin_dir_url( __FILE__ ) . 'inc/css/front-styles.css', null, '1.0', 'screen' );
		wp_enqueue_style( 'list-tables' );
	}
	if ( is_wpseeds_page() ) {
		wp_enqueue_script( 'qr-generator', plugin_dir_url( __FILE__ ) . 'ext/qrcodejs/qrcode.js', 'jquery', '0.0.1', false );
	}
}
add_action( 'wp_enqueue_scripts', 'wps_enqueue_style' );

/**
 *
 * Seeds Account Shortcode.
 *
 * @param array $atts The shortcode attrs.
 * @return string The shortcode content.
 */
function seeds_account_shortcode( $atts = array() ) {

	if ( ! is_admin() ) {
		global $post;

		/**
		 * Depending on your setup, check if the user has permissions to edit_posts
		 */
		if ( ! is_user_logged_in() ) {
			return __( 'You do not have permissions to be here.', 'lang_domain' );
		}
		?>

		<div class="wpseeds-account wps-account">
			<?php display_template( dirname( __FILE__ ) . '/tpl/wps-account-navigation-part.tpl.php' ); ?>

			<div class="wpseeds-account-content">
				
				<?php display_template( dirname( __FILE__ ) . '/tpl/wps-account-balance-part.tpl.php' ); ?>
				
				<?php wps_history_sc( array() ); ?>
			</div>
			
		</div>

		<?php
	}
}
add_shortcode( 'seeds-account', 'seeds_account_shortcode' );


/**
 * Request Seeds Shortcode.
 *
 * @param array $atts The shortcode attributes.
 */
function request_seeds_form_shortcode( $atts = array() ) {
	if ( ! is_admin() ) {
		global $post;

		/**
		 * Depending on your setup, check if the user has permissions to edit_posts
		 */
		if ( ! is_user_logged_in() ) {
			return __( 'You do not have permissions to be here.', 'lang_domain' );
		}

		?>
		<div class="wpseeds-account wps-request">

			<?php display_template( dirname( __FILE__ ) . '/tpl/wps-account-navigation-part.tpl.php' ); ?>

			<div class="wpseeds-account-content">

				<?php
				display_template( dirname( __FILE__ ) . '/tpl/wps-account-balance-part.tpl.php' );

				$vars    = array();
				$show_qr = false;

				if ( isset( $_REQUEST['do_request'] ) ) {
					if ( ! empty( $_REQUEST['amount'] ) ) {
						$to_user                    = (int) get_current_user_id();
						$amount                     = (int) $_REQUEST['amount'];
						$home                       = get_site_url();
						$vars['notice_success_1']   = __( 'Your QR Code has been created successfully.', 'wp-seeds' );
						$vars['notice_success_2']   = __( 'Please ask the sender to scan this QR code to transfer seeds to you.', 'wp-seeds' );
						$vars['reader_prompt']      = __( 'Android users if you do not have a QR reader you may', 'wp-seeds' );
						$vars['reader_link']        = 'https://play.google.com/store/apps/details?id=com.apple.qrcode.reader&hl=en';
						$vars['qr_code_url']        = sprintf(
							'%s/seeds-account/send?receiver=%s&amount=%s',
							$home,
							$to_user,
							$amount
						);
						$show_qr                    = true;

					} else {
						$vars['notice_error'] = __( 'Please provide an amount to request.', 'wp-seeds' );
					}
				}

				if ( $show_qr ) {
					display_template( dirname( __FILE__ ) . '/tpl/wps-request-transaction-code.tpl.php', $vars );
				} else {
					display_template( dirname( __FILE__ ) . '/tpl/wps-request-transaction-part.tpl.php', $vars );
				}

				?>
			</div>
		</div>
		<?php
	}
}
add_shortcode( 'seeds-request', 'request_seeds_form_shortcode' );

/**
 * Handle the sending of seeds in the frontend.
 *
 * @return void
 */
function wps_send_seeds_form_process() {
	$t = new WPS_Transaction();
	$t->sender    = get_current_user_id();
	$t->receiver  = get_req_var( 'receiver' );
	$t->amount    = get_req_var( 'amount' );
	$t->perform();
}

/**
 * Send Seeds Shortcode
 *
 * @param array $atts The shortcode attrs.
 * @return string
 */
function send_seeds_form_shortcode( $atts = array() ) {
	if ( ! is_admin() ) {
		/**
		 * Depending on your setup, check if the user has permissions to edit_posts
		 */
		if ( ! is_user_logged_in() ) {
			return __( 'You do not have permissions to be here.', 'lang_domain' );
		}

		$send_form_result = wps_process_form(
			array(
				'submit_var' => 'submit-send-seeds',
				'form_class' => 'wps-send-seeds-form',
				'process_cb' => 'wps_send_seeds_form_process',
				'success_message' => __( 'The seeds have been sent.', 'wp-seeds' ),
				'return_output' => true,
			)
		);

		?>
		<div class="wpseeds-account wps-send">

			<?php display_template( dirname( __FILE__ ) . '/tpl/wps-account-navigation-part.tpl.php' ); ?>

			<div class="wpseeds-account-content">
				<?php display_template( dirname( __FILE__ ) . '/tpl/wps-account-balance-part.tpl.php' ); ?>
				<?php
					$vars = array(
						'send_form_result' => $send_form_result,
					);
					display_template(
						dirname( __FILE__ ) . '/tpl/wps-send-transaction-part.tpl.php',
						$vars
					);
				?>
			</div>

		</div>
		<?php
	}

}
add_shortcode( 'seeds-send', 'send_seeds_form_shortcode' );
