<div class="seeds-history-container">
	<table class="seeds-history-table">
		<tr>
			<th>Transaction ID</th>
			<th>Account</th>
			<th>Amount</th>
		</tr>

		<?php foreach ($transactions as $transaction) { ?>
			<tr>
				<td>
					<?php echo htmlspecialchars($transaction["id"]); ?>
				</td>
				<td>
					<?php echo htmlspecialchars($transaction["accountLabel"]); ?>
				</td>
				<td>
					<?php echo htmlspecialchars($transaction["amount"]); ?>
				</td>
			</tr>
		<?php } ?>
	</table>
</div>