<?php
/**
 * WP Seeds 🌱
 *
 * Custom functionality for transactions overview page.
 *
 * @package   wp-seeds/inc
 * @link      https://github.com/onionco/wp-seeds
 * @author    Mikael Lindqvist, Niels Lange & Derek Smith
 * @copyright 2020 Mikael Lindqvist, Niels Lange & Derek Smith
 * @license   GPL v2 or later
 */

defined( 'ABSPATH' ) || exit;


/**
 * Rewrite rules
 */
add_filter(
	'generate_rewrite_rules',
	function ( $wp_rewrite ) {
		$wp_rewrite->rules = array_merge(
			[ 'seeds-account/send/?$' => 'index.php?wpssend=1' ],
			[ 'seeds-account/request/?$' => 'index.php?wpsrequest=1' ],
			$wp_rewrite->rules
		);
	}
);


/**
 * Register request seeds query vars.
 *
 * @param array $vars The query vars.
 * @return $query_vars.
 */
add_filter(
	'query_vars',
	function( $query_vars ) {
		$query_vars[] = 'wpsaccount';
		$query_vars[] = 'wpssend';
		$query_vars[] = 'wpsrequest';

		$vars[] = 'to_user';
		$vars[] = 'amount';

		return $query_vars;
	}
);


/**
 * Add /virtual/ endpoint permalink
 * Must be appended to an existing URL structure
 * You would replace this with your rewrite rule code.
 */
add_action(
	'init',
	function () {
		add_rewrite_endpoint( 'wpssend', EP_PERMALINK | EP_PAGES );
		add_rewrite_endpoint( 'wpsrequest', EP_PERMALINK | EP_PAGES );
	}
);


/**
 * Create virtual page, prevent 404 and customize page title, etc.
 */
add_filter(
	'the_posts',
	function ( array $posts, \WP_Query $query ) {

		if ( ! isset( $query->query_vars['wpssend'] ) && ! isset( $query->query_vars['wpsrequest'] ) ) {
			return $posts;
		}

		$seeds_page_object = get_page_by_path( 'seeds-account', OBJECT, 'page' );
		$seeds_page_id = $seeds_page_object->ID;
		$default = array(
			'account_page' => $seeds_page_id,
		);
		$wps_options = get_option( 'wps_settings', $default );
		$account_pid = $wps_options['account_page'];
		$account_content = get_the_content( $account_pid );

		if ( isset( $query->query_vars['wpssend'] ) ) {
			$title = 'Send Seeds';
			$shortcode = '[seeds-send]';
		}

		if ( isset( $query->query_vars['wpsrequest'] ) ) {
			$title = 'Request Seeds';
			$shortcode = '[seeds-request]';
		}

		$post = [
			'ID'             => $account_pid,
			'post_title'     => $title,
			'post_name'      => sanitize_title( $title ),
			'post_content'   => $account_content . $shortcode,
			'post_excerpt'   => '',
			'post_parent'    => 0,
			'menu_order'     => 0,
			'post_type'      => 'page',
			'is-singular'    => true,
			'post_status'    => 'publish',
			'comment_status' => 'closed',
			'ping_status'    => 'closed',
			'comment_count'  => 0,
			'post_password'  => '',
			'to_ping'        => '',
			'pinged'         => '',
			'guid'           => home_url( $query->getUrl() ),
			'post_date'      => current_time( 'mysql' ),
			'post_date_gmt'  => current_time( 'mysql', 1 ),
			'post_author'    => 0,
			'is_virtual'     => true,
			'filter'         => 'raw',
		];

		return [
			new \WP_Post( (object) $post ),
		];

	},
	10,
	2
);


/**
 * Set virtual page variables.
 *
 * @param array $wp The post variables.
 * @return void.
 */
function wps_account_pages_query( $wp ) {

	if ( is_admin() || ! $wp->is_main_query() ) {
		return;
	}

	if ( $wp->is_main_query() ) {
		if ( get_query_var( 'wpssend' ) || get_query_var( 'wpsrequest' ) ) {
			/* Set page variables. */
			$wp->query_vars['post_type'] = 'page';
			$wp->query_vars['is_single'] = false;
			$wp->query_vars['is_singular'] = true;
			$wp->query_vars['is_archive'] = false;
			$wp->query_vars['posts_per_page'] = 1;

			/* forces the page template. */
			$wp->is_single = false;
			$wp->is_singular = true;
			$wp->is_archive = false;
			$wp->is_post_type_archive = false;
		}
	}
}
add_action( 'pre_get_posts', 'wps_account_pages_query', 0, 2 );


/**
 * Template redirects
 */
function wps_template_redirects() {
	global $wp_query;

	if ( get_query_var( 'wpssend' ) || get_query_var( 'wpsrequest' ) ) {

		add_filter(
			'template_include',
			function() {
				$seeds_page_object = get_page_by_path( 'seeds-account', OBJECT, 'page' );
				$seeds_page_id = $seeds_page_object->ID;
				$default = array(
					'account_page' => $seeds_page_id,
				);
				$wps_options = get_option( 'wps_settings', $default );
				$account_pid = $wps_options['account_page'];
				$account_template = get_post_meta( $account_pid, '_wp_page_template', true );

				$template = get_template_directory() . '/' . $account_template;

				if ( ! file_exists( $template ) ) {
					$template = get_template_directory() . '/page.php';
				}
				if ( ! file_exists( $template ) ) {
					$template = get_template_directory() . '/singular.php';
				}
				if ( ! file_exists( $template ) ) {
					$template = get_template_directory() . '/index.php';
				}

				return $template;
			}
		);
	}
}
add_action( 'template_redirect', 'wps_template_redirects' );


/**
 * Show transaction history for the current user.
 *
 * @param array $args The shortcode args.
 * @return void.
 */
function wps_history_sc( $args ) {
	$user = wp_get_current_user();
	if ( ! $user || ! $user->ID ) {
		esc_html_e( 'You need to be logged in to view this page', 'wp-seeds' );
		return;
	}

	$user_display_by_id = wps_user_display_by_id();
	$vars = array();
	$vars['transactions'] = array();
	$transactions = WPS_Transaction::findAllByQuery(
		'SELECT * ' .
		'FROM   :table ' .
		'WHERE  sender=%s ' .
		'OR     receiver=%s',
		$user->ID,
		$user->ID
	);
	foreach ( $transactions as $transaction ) {
		$view = array();
		$view['timestamp'] = date( 'Y-m-d H:m:s', $transaction->timestamp );
		$view['id'] = $transaction->transaction_id;

		if ( $user->ID == $transaction->sender ) {
			// If we are the sender, show amount as negative, and the
			// receiver in the to/from field...
			$view['amount'] = -$transaction->amount;
			$view['user'] = __( 'To: ', 'wp-seeds' ) . $user_display_by_id[ $transaction->receiver ];
		} else {
			// ...otherwise, we are the receiver, so show the amount as
			// positive and the sender in the to/from field.
			$view['amount'] = $transaction->amount;
			$view['user'] = __( 'From: ', 'wp-seeds' ) . $user_display_by_id[ $transaction->sender ];
		}

		$vars['transactions'][] = $view;
	}

	display_template( __DIR__ . '/../tpl/wps-history.tpl.php', $vars );
}
add_shortcode( 'seeds-history', 'wps_history_sc' );
