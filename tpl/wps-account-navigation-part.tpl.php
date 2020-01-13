<?php
/**
 * WP Seeds 🌱
 *
 * Custom functionality for transactions overview page.
 *
 * @package   wp-seeds/tpl
 * @link      https://github.com/onionco/wp-seeds
 * @author    Mikael Lindqvist, Niels Lange & Derek Smith
 * @copyright 2020 Mikael Lindqvist, Niels Lange & Derek Smith
 * @license   GPL v2 or later
 */

defined( 'ABSPATH' ) || exit;


$seeds_account = intval( get_query_var( 'wpsaccount' ) );
$send_seeds = intval( get_query_var( 'wpssend' ) );
$request_seeds = intval( get_query_var( 'wpsrequest' ) );

switch ( 1 ) {
	case $seeds_account:
		$active = 1;
		break;
	case $send_seeds:
		$active = 2;
		break;
	case $request_seeds:
		$active = 3;
		break;
	default:
		$active = 0;
}

$account = 'seeds-account';
$send = 'send';
$request = 'request';
?>

<nav class="seeds-account-navigation">
	<ul>
		<li class="seeds-account-link 
		<?php
		if ( 1 == $active ) {
			echo 'active';}
		?>
		">
			<a href="<?php echo esc_attr( get_site_url() . '/' . $account . '/' ); ?>" title="Seeds Account">Seeds <span>Account</span></a>
		</li>
		<li class="seeds-account-link 
		<?php
		if ( 2 == $active ) {
			echo 'active';}
		?>
		">
			<a href="<?php echo esc_attr( get_site_url() . '/' . $account . '/' . $send . '/' ); ?>" title="Send Seeds">Send <span>Seeds</span></a>
		</li>
		<li class="seeds-account-link 
		<?php
		if ( 3 == $active ) {
			echo 'active';}
		?>
		">
			<a href="<?php echo esc_attr( get_site_url() . '/' . $account . '/' . $request . '/' ); ?>" title="Send Seeds">Request <span>Seeds</span></a>
		</li>
	</ul>
</nav>
