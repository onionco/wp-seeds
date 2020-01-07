<?php
/**
 * WP Seeds 🌱
 *
 * Custom functionality for transactions overview page.
 *
 * @package   wp-seeds/tpl
 * @link      https://github.com/limikael/wp-seeds
 * @author    Mikael Lindqvist & Niels Lange
 * @copyright 2019 Mikael Lindqvist & Niels Lange
 * @license   GPL v2 or later
 */

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
			<a href="<?php echo esc_attr( get_site_url() . '/' . $account . '/' ); ?>"
					title="Seeds Account">
				Seeds Account
			</a>
		</li>
		<li class="seeds-account-link 
		<?php
		if ( 2 == $active ) {
			echo 'active';}
		?>
		">
			<a href="<?php echo esc_attr( get_site_url() . '/' . $account . '/' . $send . '/' ); ?>"
					title="Send Seeds">
				Send Seeds
			</a>
		</li>
		<li class="seeds-account-link 
		<?php
		if ( 3 == $active ) {
			echo 'active';}
		?>
		">
			<a href="<?php echo esc_attr( get_site_url() . '/' . $account . '/' . $request . '/' ); ?>"
					title="Send Seeds">
				Request Seeds
			</a>
		</li>
	</ul>
</nav>
