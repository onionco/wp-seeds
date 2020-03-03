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

?>

<?php
if ( ! current_user_can( 'manage_options' ) ) {
	wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'wp-seeds' ) );
}
?>

<div class="wrap">
	<h1><?php esc_html_e( 'Seeds Settings', 'wp-seeds' ); ?></h1>

	<h2 class="nav-tab-wrapper">
		<a href="<?php echo esc_attr( $url . '&tab=about' ); ?>"
				class="nav-tab 
				<?php
				if ( 'about' == $tab ) {
					echo 'nav-tab-active';}
				?>
				">
			About
		</a>
		<a href="<?php echo esc_attr( $url . '&tab=create' ); ?>"
				class="nav-tab 
				<?php
				if ( 'create' == $tab ) {
					echo 'nav-tab-active';}
				?>
				">
			Create Seeds
		</a>
		<a href="<?php echo esc_attr( $url . '&tab=burn' ); ?>"
				class="nav-tab 
				<?php
				if ( 'burn' == $tab ) {
					echo 'nav-tab-active';}
				?>
				">
			Burn Seeds
		</a>
	</h2>

	<?php if ( 'about' == $tab ) { ?>
		<p>
			<?php esc_html_e( 'These pages are for creating and burning seeds.', 'wp-seeds' ); ?>
		</p>
		<p>
			<?php esc_html_e( 'This is the equivalent of printing new money, please act responsibly!', 'wp-seeds' ); ?>
		</p>

		<form method="post" action="options.php">
			<?php settings_fields( 'wps_settings_group' ); ?>
			<?php do_settings_sections( 'wps_settings_section' ); ?>
			<?php submit_button(); ?>
		</form>

	<?php } ?>

	<?php if ( 'create' == $tab ) { ?>
		<form method="post">
			<div class='wps-admin-form'>
				<div class='row'>
					<label for="receiver">Receiver</label>
					<div class='field'>
						<select name="receiver">
							<option value=''><?php esc_html_e( 'Please select', 'wp-seeds' ); ?></option>
							<?php display_select_options( $user_display_by_id, get_req_var( 'receiver', 0 ) ); ?>
						</select>
						<span class="description">
							<?php esc_html_e( 'Who should receive the newly created seeds?', 'wp-seeds' ); ?>
						</span>
					</div>
				</div>
				<div class='row'>
					<label for="receiver">Amount</label>
					<div class='field'>
						<input type="text"
								name="amount"
								value="<?php echo esc_attr( get_req_var( 'amount', '' ) ); ?>"
								class='small-text'
								autocomplete='off'/>
						<span class="description">
							<?php esc_html_e( 'How many seeds should be created?', 'wp-seeds' ); ?>
						</span>
					</div>
				</div>
			</div>
			<input type="submit"
					value="<?php esc_attr_e( 'Create Seeds' ); ?>" 
					name="submit-create"
					class='button button-primary'/>
		</form>
	<?php } ?>

	<?php if ( 'burn' == $tab ) { ?>
		<form method="post">
			<div class='wps-admin-form'>
				<div class='row'>
					<label for="sender">Sender</label>
					<div class='field'>
						<select name="sender">
							<option value=''><?php esc_html_e( 'Please select', 'wp-seeds' ); ?></option>
							<?php display_select_options( $user_display_by_id, get_req_var( 'sender', 0 ) ); ?>
						</select>
						<span class="description">
							<?php esc_html_e( 'Where should the seeds be taken from?', 'wp-seeds' ); ?>
						</span>
					</div>
				</div>
				<div class='row'>
					<label for="amount">Amount</label>
					<div class='field'>
						<input type="text"
								name="amount"
								value="<?php echo esc_attr( get_req_var( 'amount', '' ) ); ?>"
								class='small-text'
								autocomplete='off'/>
						<span class="description">
							<?php esc_html_e( 'How many seeds should be burned?', 'wp-seeds' ); ?>
						</span>
					</div>
				</div>
			</div>
			<input type="submit"
					value="<?php esc_attr_e( 'Burn Seeds' ); ?>" 
					name="submit-burn"
					class='button button-primary'/>
		</form>
	<?php } ?>
</div>
