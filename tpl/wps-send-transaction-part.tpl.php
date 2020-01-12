<?php
/**
 * WP Seeds 🌱
 *
 * @package   wp-seeds/tpl
 * @link      https://github.com/onionco/wp-seeds
 * @author    Mikael Lindqvist, Niels Lange & Derek Smith
 * @copyright 2020 Mikael Lindqvist, Niels Lange & Derek Smith
 * @license   GPL v2 or later
 */

defined( 'ABSPATH' ) || exit;

$curr_user_id = get_current_user_id();
$curr_user = wp_get_current_user();
$curr_display_name = $curr_user->display_name;
$curr_email = $curr_user->user_email;

$users = array();
foreach ( get_users() as $wpuser ) {
	if ( $wpuser->ID == $curr_user_id ) {
		continue;
	}
	$other_users_by_id[ $wpuser->ID ] = $wpuser->data->user_nicename . ' (' . $wpuser->data->user_email . ')';
}

$req_user = get_query_var( 'to_user' );
$req_amount = get_query_var( 'amount' );
$req_check = false;
if ( $req_user && $req_amount ) {
	$req_check = true;
	$req_user = get_user_by( 'id', $req_user );
	$req_display_name = $req_user->data->display_name;
	$req_email = $req_user->user_email;
}
?>

<div class="wps-front-form">
	<h2><?php esc_html_e( 'Send Seeds', 'wp-seeds' ); ?></h2>
	<?php
		echo wp_kses(
			$send_form_result,
			array(
				'div' => array(
					'class' => array(),
				),
				'p' => array(),
				'style' => array(),
			)
		);
		?>
	<form method="post" class='wps-send-seeds-form'>
		<div class='wps-send-form'>
			<div class='row'>
				<label for="sender">Sender</label>
				<div class='field sender'>
					<input type='text' value='<?php echo esc_attr( $curr_display_name . ' (' . $curr_email . ')' ); ?>' disabled>
				</div>
			</div>
			<div class='row'>
				<label for="receiver">Receiver</label>
				<div class='field receiver'>
					<?php
					if ( true == $req_check ) {
						?>
						<input type='text' value='<?php echo esc_attr( $req_display_name . ' (' . $req_email . ')' ); ?>' disabled>
						<?php
					} else {
						?>
						<select name="receiver">
							<option value=''><?php esc_html_e( 'Please select', 'wp-seeds' ); ?></option>
							<?php display_select_options( $other_users_by_id, get_req_var( 'receiver', 0 ) ); ?>
						</select>
						<?php
					}
					?>
					<span class="description">
						<?php esc_html_e( 'Who is the receiver?', 'wp-seeds' ); ?>
					</span>
				</div>
			</div>
			<div class='row'>
				<label for="amount"><?php esc_html_e( 'Seeds', 'wp-seeds' ); ?></label>
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
		<input type="submit" 
			value="<?php esc_attr_e( 'Send Seeds' ); ?>"
			name="submit-send-seeds"
			class='button button-primary'/>
	</form>
</div>

<?php
