<?php
/**
 * WP Seeds 🌱
 *
 * @package   wp-seeds
 * @link      https://github.com/onionco/wp-seeds
 * @author    Mikael Lindqvist & Niels Lange
 * @copyright 2019 Mikael Lindqvist & Niels Lange
 * @license   GPL v2 or later
 */

?>
<div class="wrap">
	<h1>WP Seeds 🌱 Settings</h1>

	<?php if ( isset( $notice_success ) ) { ?>
		<div class="notice notice-success">
			<p><?php echo esc_html( $notice_success ); ?></p>
		</div>
	<?php } ?>

	<h2>Create Seeds</h2>
	<p>
		Create brand new seeds.
	</p>

	<p>
		This is a very powerful thing to do! It is the equivalent of printing new money and putting them
		into cicrulation. Before you do this, make sure this is actually what you want. Maybe what you actually
		want to do, is to make a transaction from one account to another? 
	</p>

	<p>
		If you have decided that you actually want to create new seeds, the power is yours!
	</p>

	<form method="post" class="form-table" action="<?php echo esc_attr( $action ); ?>">
		<table>
			<tr>
				<th scope="row">
					<label>Amount</label>
				</th>
				<td>
					<input type="text" class="regular-text" name="amount"
						value="<?php echo esc_attr( $amount ); ?>"/>
					<p class="description">
						How many seeds do you want to create?
					</p>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<label>Account</label>
				</th>
				<td>
					<select name="user_id">
						<option value="">Select User Account</option>
						<?php display_select_options( $users, $user_id ); ?>
					</select>
					<p class="description">
						Where should these new seeds be deposited?
					</p>
				</td>
			</tr>
		</table>
		<p class="submit">
			<input name="do_create" type="submit" class="button button-primary" value="Create New Seeds"/>
		</p>
	</form>

	<h2>Burn Seeds</h2>
	<p>
		Burn seeds and take them out of circulation.
	</p>

	<p>
		This is a very powerful thing to do! It is the equivalent of removing money from the
		cicrulation. Before you do this, make sure this is actually what you want. Maybe what you actually
		want to do, is to make a transaction from one account to another? 
	</p>

	<p>
		If you have decided that you actually want to burn existing seeds, the power is yours!
	</p>

	<form method="post" class="form-table" action="<?php echo esc_attr( $action ); ?>">
		<table>
			<tr>
				<th scope="row">
					<label>Amount</label>
				</th>
				<td>
					<input type="text" class="regular-text" name="amount"
						value="<?php echo esc_attr( $amount ); ?>"/>
					<p class="description">
						How many seeds do you want to burn?
					</p>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<label>Account</label>
				</th>
				<td>
					<select name="user_id">
						<option value="">Select User Account</option>
						<?php display_select_options( $users, $user_id ); ?>
					</select>
					<p class="description">
						Where should these seeds be taken from?
					</p>
				</td>
			</tr>
		</table>
		<p class="submit">
			<input name="do_burn" type="submit" class="button button-primary" value="Burn Seeds"/>
		</p>
	</form>
</div>
