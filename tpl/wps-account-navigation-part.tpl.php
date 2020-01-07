<?php

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
		if ( $active == 1 ) {
			echo 'active';}
		?>
		">
			<a href="<?php echo get_site_url() . '/' . $account . '/'; ?>" title="Seeds Account">Seeds Account</a>
		</li>
		<li class="seeds-account-link 
		<?php
		if ( $active == 2 ) {
			echo 'active';}
		?>
		">
			<a href="<?php echo get_site_url() . '/' . $account . '/' . $send . '/'; ?>" title="Send Seeds">Send Seeds</a>
		</li>
		<li class="seeds-account-link 
		<?php
		if ( $active == 3 ) {
			echo 'active';}
		?>
		">
			<a href="<?php echo get_site_url() . '/' . $account . '/' . $request . '/'; ?>" title="Send Seeds">Request Seeds</a>
		</li>
	</ul>
</nav>
