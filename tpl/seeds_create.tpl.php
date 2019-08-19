<div class="wrap">
	<h1 class="wp-heading-inline">Create New Seeds</h1>

	<?php if ($errorMessage) { ?>
		<div class="settings-error error">
			<p><?php echo htmlspecialchars($errorMessage); ?></p>
		</div>
	<?php } ?>

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

	<form method="post" class="form-table" action="<?php echo $action; ?>">
		<table>
			<tr>
				<th scope="row">
					<label>Amount</label>
				</th>
				<td>
					<input type="text" class="regular-text" name="amount"
						value="<?php echo htmlspecialchars($amount); ?>"/>
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
					<select name="userId">
						<option>Select User Account</option>
						<option></option>
						<?php foreach ($users as $user) { ?>
							<option value="<?php echo $user["id"]; ?>"
								<?php if ($userId==$user["id"]) echo "selected"; ?>
							>
								<?php echo htmlspecialchars($user["label"]); ?>
							</option>
						<?php } ?>
					</select>
					<p class="description">
						Where should these new seeds be deposited?
					</p>
				</td>
			</tr>
		</table>
		<p class="submit">
			<input type="submit" class="button button-primary" value="Create New Seeds"/>
		</p>
	</form>
</div>