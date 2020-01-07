<?php
/**
 * The template for displaying all single posts
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/#single-post
 *
 * @package WordPress
 * @subpackage Twenty_Nineteen
 * @since 1.0.0
 */

get_header();
?>

	<div id="primary" class="content-area">
		<main id="main" class="site-main">
			<div class="entry-content">

				<header class="entry-header">
					<h1 class="entry-title">Seeds Account</h1>
				</header>

				<?php
				switch (1) {
					case $seeds_account:
						do_shortcode( '[seeds-account]' );
						break;
					case $send_seeds:
						do_shortcode( '[seeds-send]' );
						break;
					case $request_seeds:
						do_shortcode( '[seeds-request]' );
						break;
					default:
						do_shortcode( '[seeds-account]' );
				}
				?>

			</div>
		</main><!-- #main -->
	</div><!-- #primary -->

<?php
get_footer();
