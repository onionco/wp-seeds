<?php
/**
 * WP Seeds 🌱
 *
 * @package   wp-seeds/tpl
 * @link      https://github.com/limikael/wp-seeds
 * @author    Mikael Lindqvist & Niels Lange
 * @copyright 2019 Mikael Lindqvist & Niels Lange
 * @license   GPL v2 or later
 */

defined( 'ABSPATH' ) || exit;

$current_user_id = get_current_user_id();
$current_user = wp_get_current_user();
$user_display_name = $current_user->data->display_name;
$user_email = $current_user->user_email;

$users = array();
foreach ( get_users() as $wpuser ) {
	if( $wpuser->ID == $current_user_id ) {
		continue;
	}
	$other_users_by_id[ $wpuser->ID ] = $wpuser->data->user_nicename . ' (' . $wpuser->data->user_email . ')';
}


?>

<div class="wrap wps-front-form">
	<h2><?php esc_html_e( 'Send Seeds', 'wp-seeds' ); ?></h2>
	<form method="post">
		<div class='wps-send-form'>
			<div class='row'>
				<label for="sender">Sender</label>
				<div class='field sender'>
					<input type='text' value='<?php echo $user_display_name . ' (' . $user_email . ')'; ?>' disabled>
				</div>
			</div>
			<div class='row'>
				<label for="receiver">Receiver</label>
				<div class='field receiver'>
					<select name="receiver">
						<option value=''><?php esc_html_e( 'Please select', 'wp-seeds' ); ?></option>
						<?php display_select_options( $other_users_by_id, get_req_var( 'receiver', 0 ) ); ?>
					</select>
					<span class="description">
						<?php esc_html_e( 'Who is the receiver?', 'wp-seeds' ); ?>
					</span>
				</div>
			</div>
			<div class='row'>
				<label for="amount">Amount</label>
				<div class='field amount'>
					<input type="text"
							name="amount"
							value="<?php echo esc_attr( get_req_var( 'amount', '' ) ); ?>"
							class='small-text'
							autocomplete='off'/>
					<span class="description">
						<?php esc_html_e( 'What is the amount?', 'wp-seeds' ); ?>
					</span>
				</div>
			</div>
		</div>
		<input type="submit" value="<?php esc_attr_e( 'Send Seeds' ); ?>" name="submit" class='button button-primary'/>
	</form>
</div>

<?php
