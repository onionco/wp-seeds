<div class="wrap">
	<h1 class="wp-heading-inline">New Seeds Transaction</h1>

	<?php if ($errorMessage) { ?>
		<div class="settings-error error">
			<p><?php echo htmlspecialchars($errorMessage); ?></p>
		</div>
	<?php } ?>

	<p>
		Transfer seeds from one account to another.
	</p>

	<form method="post" class="form-table" action="<?php echo $action; ?>">
		<table>
			<tr>
				<th scope="row">
					<label>From Account</label>
				</th>
				<td>
					<select name="fromUserId">
						<option value="">Select User Account</option>
						<option></option>
						<?php display_select_options($userAccounts,$fromUserId); ?>
					</select>
					<p class="description">
						From account.
					</p>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<label>To Account</label>
				</th>
				<td>
					<select name="toUserId">
						<option value="">Select User Account</option>
						<option></option>
						<?php display_select_options($userAccounts,$toUserId); ?>
					</select>
					<p class="description">
						To account.
					</p>
				</td>
			</tr>

			<tr>
				<th scope="row">
					<label>Amount</label>
				</th>
				<td>
					<input type="text" class="regular-text" name="amount"
						value="<?php echo htmlspecialchars($amount); ?>"/>
					<p class="description">
						How many seeds is the transaction for?
					</p>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<label>Notice</label>
				</th>
				<td>
					<input type="text" class="regular-text" name="notice"
						value="<?php echo htmlspecialchars($notice); ?>"/>
					<p class="description">
						What is the transaction for?
					</p>
				</td>
			</tr>
		</table>
		<p class="submit">
			<input type="submit" class="button button-primary" value="Transact Seeds"/>
		</p>
	</form>
</div>