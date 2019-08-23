<div class="wrap">
	<h1 class="wp-heading-inline">Transaction Details</h1>

	<div class="card">
		<table class="form-table">
			<tr>
				<th scope="row">
					<label>Transaction ID</label>
				</th>
				<td>
					<?php echo htmlspecialchars($transaction->transaction_id); ?>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<label>From</label>
				</th>
				<td>
					<?php echo htmlspecialchars($transaction->getFromUserFormatted()); ?>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<label>To</label>
				</th>
				<td>
					<?php echo htmlspecialchars($transaction->getToUserFormatted()); ?>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<label>Amount</label>
				</th>
				<td>
					<?php echo htmlspecialchars($transaction->amount); ?>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<label>Notice</label>
				</th>
				<td>
					<?php echo htmlspecialchars($transaction->notice); ?>
				</td>
			</tr>
		</table>
	</div>
</div>